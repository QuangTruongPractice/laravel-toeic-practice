<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Part;
use App\Models\QuestionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionGroup>
 */
class QuestionGroupFactory extends Factory
{
    protected $model = QuestionGroup::class;

    public function definition(): array
    {
        // Part không có HasFactory, dùng firstOrCreate để đảm bảo có part_id hợp lệ
        $part = Part::firstOrCreate(
            ['part_number' => 1],
            [
                'name' => 'Photographs',
                'section' => 'listening',
                'directions' => 'Describe the picture.',
                'description' => 'Photographs',
                'question_count' => 6,
            ]
        );

        return [
            'exam_id' => Exam::factory(),
            'part_id' => $part->id,
            'passage' => null,
            'audio_path' => null,
            'image_path' => null,
            'order_number' => fake()->numberBetween(1, 50),
        ];
    }

    /** Nhóm có audio lưu trên Cloudinary (direct URL) */
    public function withAudio(string $url = 'https://res.cloudinary.com/demo/video/upload/sample.mp3'): static
    {
        return $this->state(['audio_path' => $url]);
    }

    /** Nhóm có ảnh lưu trên Cloudinary (direct URL) */
    public function withImage(string $url = 'https://res.cloudinary.com/demo/image/upload/sample.jpg'): static
    {
        return $this->state(['image_path' => $url]);
    }

    /** Nhóm có passage */
    public function withPassage(): static
    {
        return $this->state(['passage' => fake()->paragraph()]);
    }
}
