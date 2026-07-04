<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_exam_index_is_visible()
    {
        $user = User::factory()->create();
        Exam::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user)->get(route('exams.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Thi thử TOEIC Full Test');
    }

    public function test_search_finds_published_exam_by_keyword()
    {
        $user = User::factory()->create();
        $exam = Exam::factory()->create([
            'status' => 'published',
            'title' => 'TOEIC Practice 2026',
            'description' => 'Đề luyện nghe và đọc',
        ]);

        $response = $this->actingAs($user)->get(route('exams.index', ['q' => 'practice']));

        $response->assertStatus(200);
        $response->assertSee($exam->title);
    }

    public function test_guest_is_redirected_to_login_when_accessing_draft_exam()
    {
        $exam = Exam::factory()->draft()->create();

        $response = $this->get(route('exams.show', $exam));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_draft_exam()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->draft()->create();

        $response = $this->actingAs($admin)->get(route('exams.show', $exam));

        $response->assertStatus(200);
        $response->assertSeeText($exam->title);
    }
}
