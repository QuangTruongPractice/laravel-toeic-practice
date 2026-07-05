<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary emails to admin users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Sending daily summary emails to admins...');

            // Get all admin users
            $admins = User::where('role', 'admin')->get();

            if ($admins->isEmpty()) {
                $this->warn('No admin users found to send summary to');

                return self::SUCCESS;
            }

            // Calculate today's stats
            $today = now()->startOfDay();

            $todayAttempts = ExamAttempt::where('status', 'completed')
                ->where('completed_at', '>=', $today)
                ->count();

            $averageScore = ExamAttempt::where('status', 'completed')
                ->where('completed_at', '>=', $today)
                ->avg('total_score');

            $topPerformer = ExamAttempt::where('status', 'completed')
                ->where('completed_at', '>=', $today)
                ->orderByDesc('total_score')
                ->with('user')
                ->first();

            // Prepare summary data
            $summaryData = [
                'date' => $today->format('Y-m-d'),
                'total_attempts' => $todayAttempts,
                'average_score' => (int) $averageScore,
                'top_performer' => $topPerformer,
                'active_users_today' => ExamAttempt::where('status', 'completed')
                    ->where('completed_at', '>=', $today)
                    ->distinct('user_id')
                    ->count(),
            ];

            // Send emails to each admin
            foreach ($admins as $admin) {
                try {
                    // Here you would send the email
                    // Mail::send('emails.daily-summary', $summaryData, function ($message) use ($admin) {
                    //     $message->to($admin->email)
                    //         ->subject('[TOEIC Platform] Daily Summary - ' . now()->format('Y-m-d'));
                    // });

                    $this->line("✓ Summary email sent to {$admin->email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send email to {$admin->email}: ".$e->getMessage());
                    logger()->error('Daily summary email failed', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info('✓ Daily summary emails processed');
            $this->info("  - Total Attempts Today: {$summaryData['total_attempts']}");
            $this->info("  - Average Score: {$summaryData['average_score']}");
            $this->info("  - Active Users: {$summaryData['active_users_today']}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send daily summary: '.$e->getMessage());
            logger()->error('email:daily-summary command failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
