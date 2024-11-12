<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary roles
        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'User']);

        // Create users for testing
        $this->adminUser = User::factory()->create(['role_id' => 1]);
        $this->regularUser = User::factory()->create(['role_id' => 2]);

        // Log in as admin by default
        Auth::login($this->adminUser);
    }

    /**
     * Returns sample task data for testing purposes.
     *
     * @param array $overrides Override default task data.
     * @return array
     */
    protected function getTaskData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Task',
            'description' => 'This is a test task description.',
            'type' => 'Bug',
            'status' => 'Open',
            'priority' => 'High',
            'due_date' => now()->addDays(2)->toDateString(),
            'assign_to' => $this->regularUser->id,
            'depends_on' => [],
        ], $overrides);
    }

    /**
     * Test successful task creation.
     *
     * @return void
     */
    public function testStoreTaskSuccessfully()
    {
        $taskData = $this->getTaskData();

        $response = $this->postJson(route('tasks.store'), $taskData);

        $response->assertStatus(200);
        $response->assertJson([
            'Task' => [
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'type' => $taskData['type'],
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'due_date' => $taskData['due_date'],
                'assigned_to' => [
                    'id' => $this->regularUser->id,
                    'name' => $this->regularUser->name,
                    'email' => $this->regularUser->email,
                ],
                'created_by' => [
                    'id' => $this->adminUser->id,
                    'name' => $this->adminUser->name,
                    'email' => $this->adminUser->email,
                ],
                'dependencies' => [],
                'comments' => [],
                'attachments' => [],
            ],
            'message' => 'Task Created Successfully'
        ]);

        // Check the database for the created task
        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'assign_to' => $this->regularUser->id,
        ]);

        // Check the initial status update record for the task
        $this->assertDatabaseHas('task_status_updates', [
            'task_id' => Task::first()->id,
            'status' => 'Open',
        ]);
    }

    /**
     * Test task creation fails with validation error due to missing title.
     *
     * @return void
     */
    public function testStoreTaskValidationFails()
    {
        $taskData = $this->getTaskData(['title' => null]);

        $response = $this->postJson(route('tasks.store'), $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /**
     * Test task creation fails with invalid 'assign_to' field.
     *
     * @return void
     */
    public function testStoreTaskWithInvalidAssignTo()
    {
        $invalidAssignToData = $this->getTaskData(['assign_to' => $this->adminUser->id]);

        $response = $this->postJson(route('tasks.store'), $invalidAssignToData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('assign_to');
    }
}
