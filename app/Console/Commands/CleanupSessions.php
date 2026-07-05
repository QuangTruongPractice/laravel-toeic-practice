<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:sessions {--days=7 : Number of days to keep sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old sessions and cache entries older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $days = $this->option('days');
            $cutoffDate = now()->subDays($days);

            $this->info("Cleaning up sessions older than {$days} days...");

            // Cleanup sessions table if using database driver
            if (config('session.driver') === 'database') {
                $deletedSessions = DB::table('sessions')
                    ->where('last_activity', '<', $cutoffDate->timestamp)
                    ->delete();

                $this->info("✓ Deleted {$deletedSessions} old session entries");
            }

            // Cleanup cache entries
            if (config('cache.default') === 'database') {
                $deletedCache = DB::table('cache')
                    ->where('expiration', '<', now()->timestamp)
                    ->delete();

                $this->info("✓ Deleted {$deletedCache} expired cache entries");
            }

            // Cleanup cache locks
            $deletedLocks = DB::table('cache_locks')
                ->where('expiration', '<', now()->timestamp)
                ->delete();

            $this->info("✓ Deleted {$deletedLocks} expired cache locks");

            $this->info('✓ Session and cache cleanup completed successfully');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup sessions: '.$e->getMessage());
            logger()->error('cleanup:sessions command failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
