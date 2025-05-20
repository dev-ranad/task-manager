<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }
    public function test_can_list_tasks()
    {
        Sanctum::actingAs($this->user);

        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks?isKanban=false&perPage=10&page=1');

        $response->assertOk()
            ->assertJsonPath('data.meta.per_page', 10);
    }
    public function test_can_kanban_tasks()
    {
        Sanctum::actingAs($this->user);

        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks?isKanban=true');

        $response->assertOk()
        ->assertJsonStructure([
                'message',
                'data' => ['results' => ['task']],
            ]);
    }

    public function test_can_store_a_task_with_attachments()
    {
        Sanctum::actingAs($this->user);

        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'High',
            'status' => 'pending',
            'categories' => [$category->id],
            'users' => [$user->id],
            'image' => UploadedFile::fake()->image('task.jpg'),
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Test Task');
    }

    public function test_can_show_a_single_task()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_can_update_a_task()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create();

        $updatedData = [
            'title' => 'Updated Task Title',
            'status' => 'completed',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Task Title')
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_can_delete_a_task()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
