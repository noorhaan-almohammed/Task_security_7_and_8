<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'User']);
    }

    public function test_store_user_successfully()
    {
        $adminUser = User::factory()->create([
            'role_id' => 1,
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $response = $this->actingAs($adminUser)->postJson('/api/users', $data);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'User Created Successfully',
        ]);
    }

    public function test_store_user_with_duplicate_email()
    {

        $adminUser = User::factory()->create([
            'role_id' => 1,
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $response = $this->actingAs($adminUser)->postJson('/api/users', $data);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'User Created Successfully'
        ]);

        $data = [
            'name' => 'Jane Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $response = $this->actingAs($adminUser)->postJson('/api/users', $data);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'error validation',
            'errors' => [
                'email' => [
                    'email should be unique, this email is already exists!'
                ]
            ]
        ]);
    }
    }
