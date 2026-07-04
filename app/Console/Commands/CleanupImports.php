<?php

namespace App\Console\Commands;

use App\Models\Import;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:imports {--days=30 : Number of days to keep failed/pending imports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old temporary import files and failed import records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $days = $this->option('days');
            $cutoffDate = now()->subDays($days);

            $this->info("Cleaning up imports older than {$days} days...");

            // Find and delete old failed/pending imports
            $oldImports = Import::where('status', '!=', 'completed')
                ->where('created_at', '<', $cutoffDate)
                ->get();

            $deletedFiles = 0;
            foreach ($oldImports as $import) {
                // Delete associated files
                if ($import->pdf_path && Storage::exists($import->pdf_path)) {
                    Storage::delete($import->pdf_path);
                    $deletedFiles++;
                }

                if ($import->audio_path && Storage::exists($import->audio_path)) {
                    Storage::delete($import->audio_path);
                    $deletedFiles++;
                }

                $import->delete();
            }

            $this->info("✓ Deleted {$oldImports->count()} old import records and {$deletedFiles} associated files");

            // Also cleanup leftover temp files in storage
            $tempPath = 'temp/imports';
            if (Storage::exists($tempPath)) {
                $tempFiles = Storage::files($tempPath);
                foreach ($tempFiles as $file) {
                    $fileModified = Storage::lastModified($file);
                    if ($fileModified < $cutoffDate->timestamp) {
                        Storage::delete($file);
                        $deletedFiles++;
                    }
                }
            }

            $this->info("✓ Import cleanup completed successfully ({$deletedFiles} files total)");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup imports: '.$e->getMessage());
            logger()->error('cleanup:imports command failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
