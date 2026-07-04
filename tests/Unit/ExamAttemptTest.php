<?php

namespace Tests\Unit;

use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_accuracy_percent_calculation()
    {
        $attempt = ExamAttempt::factory()->create([
            'total_correct' => 80,
            'total_questions' => 100,
        ]);

        $this->assertSame(80.0, $attempt->accuracy_percent);
    }

    public function test_is_in_progress_flag_returns_true()
    {
        $attempt = ExamAttempt::factory()->inProgress()->create();

        $this->assertTrue($attempt->isInProgress());
        $this->assertFalse($attempt->isCompleted());
    }

    public function test_remaining_time_is_calculated_from_started_at()
    {
        $attempt = ExamAttempt::factory()->inProgress()->create([
            'started_at' => now()->subMinutes(10),
        ]);

        $this->assertSame(7200 - 600, $attempt->getRemainingTimeSeconds(7200));
    }
}
