<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
       // Create 10 random user


        // Create an admin user with a valid role_id
        $adminRoleId = Role::where('name', 'admin')->first()->id;
        $userRoleId = Role::where('name', 'user')->first()->id;
        User::factory()->create([
            'name' => 'admin',
            'email' => 'gnourhhaan1994@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId // Assign the admin role
        ]);
        User::factory()->create([
            'name' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId // Assign the admin role
        ]);
        User::factory()->create([
            'name' => 'user1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $userRoleId
        ]);
        User::factory()->create([
            'name' => 'user2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $userRoleId
        ]);
         Task::factory()->create([
            'title' => 'Task1',
            'description' => 'Task1 description',
            'type' => 'Bug',
            'status' => 'Open',
            'priority' => 'Low',
            'assign_to' => $adminRoleId,
            'due_date' => today(),
            'created_by' => $adminRoleId,
        ]);
        Task::factory()->create([
            'title' => 'Task2',
            'description' => 'Task2 description',
            'type' => 'Bug',
            'status' => 'Open',
            'priority' => 'High',
            'assign_to' => $userRoleId,
            'due_date' => today(),
            'created_by' => $adminRoleId,
        ]);
    }
}
