<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignTaskTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'User']);

        // Create users
        $this->adminUser = User::factory()->create(['role_id' => 1]); // Admin user
        $this->regularUser = User::factory()->create(['role_id' => 2]); // Regular user

        // Create a task without an assignee
        $this->task = Task::factory()->create( ['title' => 'Test Task',
                                                            'description' => 'This is a test task description.',
                                                            'type' => 'Bug',
                                                            'status' => 'Open',
                                                            'priority' => 'High',
                                                            'due_date' => now()->addDays(2)->toDateString(),
                                                            'assign_to' => null,
                                                            'created_by' => $this->adminUser->id
    ]);

        // Log in as the task creator
        $this->actingAs($this->adminUser);
    }

    /**
     * Test assigning task successfully to a regular user.
     */
    public function testAssignTaskSuccessfully()
    {
        $response = $this->putJson(route('tasks.assign', ['task' => $this->task->id]), [
            'assign_to' => $this->regularUser->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'Task' => [
                'assign_to' => $this->regularUser->id,
            ],
            'message' => 'Task Assigned Successfully'
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assign_to' => $this->regularUser->id,
        ]);
    }

    /**
     * Test assigning task fails when user does not exist.
     */
    public function testAssignTaskFailsWithNonExistentUser()
    {
        $response = $this->putJson(route('tasks.assign', ['task' => $this->task->id]), [
            'assign_to' => 999, // Non-existent user ID
        ]);

        $response->assertStatus(422); // The status should be 422 for validation failure
        $response->assertJsonValidationErrors('assign_to'); // The validation error field is 'assign_to'
        $response->assertJson([
            'message' => 'User not found',
            'errors' => [
                'assign_to' => [
                    'User not found',
                ],
            ],
        ]);
      }

    /**
     * Test assigning task fails when assigning to an admin.
     */
    public function testAssignTaskFailsWithAdminUser()
    {
        $response = $this->putJson(route('tasks.assign', ['task' => $this->task->id]), [
            'assign_to' => $this->adminUser->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('assign_to');
    }

    /**
     * Test assigning task fails if task is already assigned.
     */
    public function testAssignTaskFailsWhenAlreadyAssigned()
    {
        // First, assign the task
        $this->task->assign_to = $this->regularUser->id;
        $this->task->save();

        $response = $this->putJson(route('tasks.assign', ['task' => $this->task->id]), [
            'assign_to' => $this->regularUser->id,
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'message' => 'Task already assigned to user',
        ]);
    }
}
