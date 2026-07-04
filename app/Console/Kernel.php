<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily: Calculate and cache user statistics
        $schedule->command('stats:calculate')
            ->daily()
            ->onSuccess(function () {
                logger()->info('Daily statistics calculated successfully');
            })
            ->onFailure(function () {
                logger()->error('Failed to calculate daily statistics');
            });

        // Weekly: Cleanup old sessions and temporary files (every Monday at 2 AM)
        $schedule->command('cleanup:sessions')
            ->weeklyOn(1, '02:00')
            ->onSuccess(function () {
                logger()->info('Old sessions cleaned up successfully');
            });

        // Weekly: Cleanup temporary import files (every Sunday at 3 AM)
        $schedule->command('cleanup:imports')
            ->weeklyOn(0, '03:00')
            ->onSuccess(function () {
                logger()->info('Temporary import files cleaned up successfully');
            });

        // Daily: Send summary emails to admins (every day at 9 AM)
        $schedule->command('email:daily-summary')
            ->dailyAt('09:00')
            ->onSuccess(function () {
                logger()->info('Daily summary emails sent successfully');
            })
            ->onFailure(function () {
                logger()->error('Failed to send daily summary emails');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
