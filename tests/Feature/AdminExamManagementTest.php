<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_exam()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.exams.store'), [
            'title' => 'Exam Test 1',
            'slug' => 'exam-test-1',
            'description' => 'Mô tả đề thi',
            'year' => 2025,
            'duration_minutes' => 120,
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.exams.index'));
        $this->assertDatabaseHas('exams', ['slug' => 'exam-test-1']);
    }

    public function test_non_admin_is_redirected_from_admin_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.exams.index'));

        $response->assertRedirect(route('login'));
    }
}
