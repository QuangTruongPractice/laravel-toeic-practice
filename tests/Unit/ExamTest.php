<?php

namespace Tests\Unit;

use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_slug_when_created()
    {
        $exam = Exam::factory()->create([
            'title' => 'ETS 2026 Test 1',
            'slug' => 'ets-2026-test-1',
        ]);

        $this->assertSame('ets-2026-test-1', $exam->slug);
    }

    public function test_it_has_published_scope()
    {
        Exam::factory()->create(['status' => 'draft']);
        Exam::factory()->create(['status' => 'published']);

        $published = Exam::published()->get();

        $this->assertCount(1, $published);
        $this->assertSame('published', $published->first()->status);
    }
}
