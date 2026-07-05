<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exam_id' => Exam::factory(),
            'mode' => 'full_test',
            'total_score' => 550,
            'listening_score' => 275,
            'reading_score' => 275,
            'total_correct' => 100,
            'total_questions' => 200,
            'time_spent_seconds' => 7200,
            'parts_attempted' => [],
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }
}
