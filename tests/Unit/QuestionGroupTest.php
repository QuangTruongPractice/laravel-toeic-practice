<?php

namespace Tests\Unit;

use App\Models\QuestionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests cho QuestionGroup model.
 *
 * Thay đổi cần kiểm thử:
 * - audio_path và image_path giờ lưu Cloudinary URL trực tiếp (không còn relative path).
 * - Accessor audio_url / image_url trả về thẳng giá trị audio_path / image_path.
 * - Accessor được khai báo trong $appends nên luôn xuất hiện khi serialize.
 */
class QuestionGroupTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // audio_url accessor
    // -------------------------------------------------------------------------

    public function test_audio_url_returns_cloudinary_url_when_audio_path_is_set(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/demo/video/upload/toeic/test_1/audios/q1.mp3';

        $group = QuestionGroup::factory()->withAudio($cloudinaryUrl)->create();

        $this->assertSame($cloudinaryUrl, $group->audio_url);
    }

    public function test_audio_url_returns_null_when_audio_path_is_null(): void
    {
        $group = QuestionGroup::factory()->create(['audio_path' => null]);

        $this->assertNull($group->audio_url);
    }

    public function test_audio_url_equals_audio_path(): void
    {
        $url = 'https://res.cloudinary.com/demo/video/upload/sample.mp3';
        $group = QuestionGroup::factory()->withAudio($url)->create();

        // Accessor chỉ pass-through audio_path, không transform
        $this->assertSame($group->audio_path, $group->audio_url);
    }

    // -------------------------------------------------------------------------
    // image_url accessor
    // -------------------------------------------------------------------------

    public function test_image_url_returns_cloudinary_url_when_image_path_is_set(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/demo/image/upload/toeic/test_1/images/p1.jpg';

        $group = QuestionGroup::factory()->withImage($cloudinaryUrl)->create();

        $this->assertSame($cloudinaryUrl, $group->image_url);
    }

    public function test_image_url_returns_null_when_image_path_is_null(): void
    {
        $group = QuestionGroup::factory()->create(['image_path' => null]);

        $this->assertNull($group->image_url);
    }

    public function test_image_url_equals_image_path(): void
    {
        $url = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';
        $group = QuestionGroup::factory()->withImage($url)->create();

        $this->assertSame($group->image_path, $group->image_url);
    }

    // -------------------------------------------------------------------------
    // $appends: audio_url + image_url phải xuất hiện khi serialize
    // -------------------------------------------------------------------------

    public function test_audio_url_and_image_url_are_appended_to_array(): void
    {
        $group = QuestionGroup::factory()
            ->withAudio('https://res.cloudinary.com/demo/video/upload/a.mp3')
            ->withImage('https://res.cloudinary.com/demo/image/upload/b.jpg')
            ->create();

        $array = $group->toArray();

        $this->assertArrayHasKey('audio_url', $array);
        $this->assertArrayHasKey('image_url', $array);
    }

    public function test_appended_urls_are_null_when_paths_are_empty(): void
    {
        $group = QuestionGroup::factory()->create([
            'audio_path' => null,
            'image_path' => null,
        ]);

        $array = $group->toArray();

        $this->assertNull($array['audio_url']);
        $this->assertNull($array['image_url']);
    }

    // -------------------------------------------------------------------------
    // hasAudio() / hasImage() helper methods
    // -------------------------------------------------------------------------

    public function test_has_audio_returns_true_when_audio_path_is_set(): void
    {
        $group = QuestionGroup::factory()->withAudio()->create();

        $this->assertTrue($group->hasAudio());
    }

    public function test_has_audio_returns_false_when_audio_path_is_null(): void
    {
        $group = QuestionGroup::factory()->create(['audio_path' => null]);

        $this->assertFalse($group->hasAudio());
    }

    public function test_has_image_returns_true_when_image_path_is_set(): void
    {
        $group = QuestionGroup::factory()->withImage()->create();

        $this->assertTrue($group->hasImage());
    }

    public function test_has_image_returns_false_when_image_path_is_null(): void
    {
        $group = QuestionGroup::factory()->create(['image_path' => null]);

        $this->assertFalse($group->hasImage());
    }
}
