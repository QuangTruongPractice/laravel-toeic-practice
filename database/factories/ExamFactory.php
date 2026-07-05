<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'year' => fake()->numberBetween(2018, 2025),
            'status' => 'published',
            'duration_minutes' => 120,
        ];
    }

    public function draft(): static
    {
        return $this->state([
            'status' => 'draft',
        ]);
    }
}
