<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReassignTaskTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser1;
    protected $regularUser2;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary roles
        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'User']);

        // Create users
        $this->adminUser = User::factory()->create(['role_id' => 1]);
        $this->regularUser1 = User::factory()->create(['role_id' => 2]);
        $this->regularUser2 = User::factory()->create(['role_id' => 2]);

        // Log in as admin by default
        Auth::login($this->adminUser);

        // Create a task assigned to regularUser1
        $this->task = Task::factory()->create([
            'title' => 'Test Task',
            'created_by' => $this->adminUser->id,
            'description' => 'This is a test task description.',
            'type' => 'Bug',
            'status' => 'Open',
            'priority' => 'High',
            'due_date' => now()->addDays(2)->toDateString(),
            'assign_to' => $this->regularUser1->id,
        ]);
    }

    /**
     * Test successful task reassignment.
     *
     * @return void
     */
    public function testReassignTaskSuccessfully()
    {
        $requestData = [
            'assign_to' => $this->regularUser2->id, // Reassign to another user
        ];

        // Send the PUT request to reassign the task
        $response = $this->putJson(route('tasks.reassign', $this->task->id), $requestData);

        // Assert successful reassignment response
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Task Reassigned Successfully',
            'Task' => [
                'id' => $this->task->id,
                'assign_to' => $this->regularUser2->id,
            ]
        ]);

        // Verify in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assign_to' => $this->regularUser2->id,
        ]);
    }

    /**
     * Test reassignment fails if assigning to the same user.
     *
     * @return void
     */
    public function testReassignTaskFailsWhenAssignedToSameUser()
    {
        $requestData = [
            'assign_to' => $this->regularUser1->id, // Attempt to reassign to the same user
        ];

        $response = $this->putJson(route('tasks.reassign', $this->task->id), $requestData);

        // Assert validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('assign_to');
    }

    /**
     * Test reassignment fails when assigned to an admin user.
     *
     * @return void
     */
    public function testReassignTaskFailsWhenAssignedToAdmin()
    {
        $requestData = [
            'assign_to' => $this->adminUser->id, // Attempt to reassign to admin user
        ];

        $response = $this->putJson(route('tasks.reassign', $this->task->id), $requestData);

        // Assert validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('assign_to');
    }
}
