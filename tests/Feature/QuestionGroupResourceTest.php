<?php

namespace Tests\Feature;

use App\Http\Resources\QuestionGroupResource;
use App\Models\QuestionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Tests cho QuestionGroupResource.
 *
 * Thay đổi cần kiểm thử:
 * - Trước đây resource build URL qua Storage::url() + url().
 * - Sau khi cập nhật, resource đọc thẳng accessor audio_url / image_url từ model
 *   (vốn chỉ pass-through audio_path / image_path là Cloudinary URL).
 * - Không còn phụ thuộc vào Storage facade.
 */
class QuestionGroupResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequest(): Request
    {
        return Request::create('/');
    }

    // -------------------------------------------------------------------------
    // audio_url trong JSON resource
    // -------------------------------------------------------------------------

    public function test_resource_returns_cloudinary_audio_url(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/mycloudname/video/upload/toeic/test_1/audios/q1.mp3';

        $group = QuestionGroup::factory()->withAudio($cloudinaryUrl)->create();

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        $this->assertSame($cloudinaryUrl, $resource['audio_url']);
    }

    public function test_resource_returns_null_audio_url_when_no_audio(): void
    {
        $group = QuestionGroup::factory()->create(['audio_path' => null]);

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        $this->assertNull($resource['audio_url']);
    }

    // -------------------------------------------------------------------------
    // image_url trong JSON resource
    // -------------------------------------------------------------------------

    public function test_resource_returns_cloudinary_image_url(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/mycloudname/image/upload/toeic/test_1/images/p1.jpg';

        $group = QuestionGroup::factory()->withImage($cloudinaryUrl)->create();

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        $this->assertSame($cloudinaryUrl, $resource['image_url']);
    }

    public function test_resource_returns_null_image_url_when_no_image(): void
    {
        $group = QuestionGroup::factory()->create(['image_path' => null]);

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        $this->assertNull($resource['image_url']);
    }

    // -------------------------------------------------------------------------
    // Resource không còn dùng Storage::url() (kiểm tra URL không bị wrap thêm)
    // -------------------------------------------------------------------------

    public function test_resource_audio_url_is_not_double_wrapped(): void
    {
        // Nếu URL bị wrap thêm sẽ có dạng "http://localhost/storage/https://..."
        $cloudinaryUrl = 'https://res.cloudinary.com/demo/video/upload/sample.mp3';

        $group = QuestionGroup::factory()->withAudio($cloudinaryUrl)->create();

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        // Phải là Cloudinary URL gốc, không bị tiền tố storage
        $this->assertStringStartsWith('https://res.cloudinary.com', $resource['audio_url']);
        $this->assertStringNotContainsString('/storage/', $resource['audio_url']);
    }

    public function test_resource_image_url_is_not_double_wrapped(): void
    {
        $cloudinaryUrl = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';

        $group = QuestionGroup::factory()->withImage($cloudinaryUrl)->create();

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        $this->assertStringStartsWith('https://res.cloudinary.com', $resource['image_url']);
        $this->assertStringNotContainsString('/storage/', $resource['image_url']);
    }

    // -------------------------------------------------------------------------
    // Kiểm tra cấu trúc resource đầy đủ
    // -------------------------------------------------------------------------

    public function test_resource_contains_required_keys(): void
    {
        $group = QuestionGroup::factory()->create();

        $resource = (new QuestionGroupResource($group))->toArray($this->makeRequest());

        foreach (['id', 'part_id', 'part_number', 'part_name', 'passage', 'audio_url', 'image_url'] as $key) {
            $this->assertArrayHasKey($key, $resource, "Key '{$key}' missing from resource.");
        }
    }
}
