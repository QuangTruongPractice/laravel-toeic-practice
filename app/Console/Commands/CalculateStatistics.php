<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CalculateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and cache daily user statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Starting daily statistics calculation...');

            // Calculate total completed attempts
            $totalAttempts = ExamAttempt::where('status', 'completed')->count();
            Cache::put('stats:total_attempts', $totalAttempts, now()->addDay());

            // Calculate average scores by section
            $avgListening = ExamAttempt::where('status', 'completed')
                ->avg('listening_score');
            Cache::put('stats:avg_listening', (int) $avgListening, now()->addDay());

            $avgReading = ExamAttempt::where('status', 'completed')
                ->avg('reading_score');
            Cache::put('stats:avg_reading', (int) $avgReading, now()->addDay());

            // Calculate total active users (with at least one completed attempt)
            $activeUsers = User::whereHas('examAttempts', function ($query) {
                $query->where('status', 'completed');
            })->count();
            Cache::put('stats:active_users', $activeUsers, now()->addDay());

            // Calculate top 10 users by score
            $topUsers = User::withCount(['examAttempts' => function ($query) {
                $query->where('status', 'completed');
            }])
                ->withSum(['examAttempts' => function ($query) {
                    $query->where('status', 'completed');
                }], 'total_score')
                ->orderByDesc('exam_attempts_sum_total_score')
                ->limit(10)
                ->get(['id', 'name', 'email', 'exam_attempts_count', 'exam_attempts_sum_total_score'])
                ->toArray();

            Cache::put('stats:top_users', $topUsers, now()->addDay());

            $this->info('✓ Statistics calculated successfully');
            $this->info("  - Total Attempts: {$totalAttempts}");
            $this->info("  - Average Listening Score: {$avgListening}");
            $this->info("  - Average Reading Score: {$avgReading}");
            $this->info("  - Active Users: {$activeUsers}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to calculate statistics: '.$e->getMessage());
            logger()->error('stats:calculate command failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
