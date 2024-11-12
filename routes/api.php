<?php

use App\Http\Controllers\Admin\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\User\TaskController as UserTask;
use App\Http\Controllers\Admin\TaskController as AdminTask;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Routes are grouped based on middleware, controllers, and functionalities.
|
*/

Route::controller(AuthController::class)->group(function () {
    /**
     * Auth Routes: Handles login, logout, token refresh, and profile retrieval
     */
    Route::post('login', 'login'); // POST request to login, returns a JWT token on success
    Route::post('logout', 'logout'); // POST request to logout, invalidates the JWT token
    Route::post('refresh', 'refresh'); // POST request to refresh the JWT token
    Route::post('profile', 'profile'); // POST request to get the current user's profile information
});

Route::middleware(['throttle:60,1', 'security', 'admin'])->apiResource('users', UserController::class);
/**
 * User Routes for admin: Only accessible to users with 'admin' role
 * - Registers CRUD routes for managing users with UserController.
 * - Throttled to 60 requests per minute, requires 'security' and 'admin' middleware.
 */

Route::middleware(['auth:api' , 'throttle:60,1', 'security'])->group(function () {
    /**
     * User Task Routes: Accessible to authenticated users with specific permissions
     */
    Route::put('tasks/{task}/status', [UserTask::class, 'updateStatus']);
    // PUT request to update the status of a task
    // Takes the task ID and updated status, returns updated task details on success

    Route::get('listTasks', [UserTask::class, 'index']);
    // GET request to list all tasks for the authenticated user
    // Returns a collection of tasks related to the user

    Route::post('tasks/{id}/comments', [AdminTask::class, 'addComment']);
    // POST request to add a comment to a task
    // Takes the task ID and comment details, returns the updated task or comment

    Route::post('tasks/{id}/attachment', [AttachmentController::class, 'upload']);
    // POST request to upload an attachment to a task
    // Takes the task ID and attachment file, returns details of the uploaded attachment
});

Route::middleware(['admin'])->group(function () {
    /**
     * Admin Task Routes: Accessible only to users with admin permissions
     */
    Route::get('tasks/blockedTasks', [AdminTask::class, 'showBlockedTasks']);
    // GET request to list all blocked tasks
    // Returns a list of tasks that are blocked or require admin intervention

    Route::post('tasks', [AdminTask::class, 'store'])->name('tasks.store');
    // POST request to create a new task
    // Takes task details in the request body, returns the created task

    Route::put('tasks/{task}/reassign', [AdminTask::class, 'reassignTask'])->name('tasks.reassign');
    // PUT request to reassign a task to another user
    // Takes task ID and the new assignee details, returns the updated task

    Route::put('tasks/{task}/assign', [AdminTask::class, 'assignTask'])->name('tasks.assign');
    // PUT request to assign a task to a user
    // Takes task ID and user ID for assignment, returns the updated task

    Route::get('tasks/{task}', [AdminTask::class, 'showTask']);
    // GET request to show details of a specific task by task ID
    // Returns the task details

    Route::get('tasks', [AdminTask::class, 'showFilterTask']);
    // GET request to list tasks with optional filters
    // Returns a list of tasks filtered by specific criteria

    Route::get('reports/daily-tasks', [AdminTask::class, 'showReportTask']);
    // GET request to retrieve a report on daily tasks
    // Returns a report of tasks for the day

    Route::delete('tasks/{task}/delete', [AdminTask::class, 'destroy']);
    // DELETE request to delete a specific task by task ID
    // Returns a success response after deleting the task

    Route::get('tasks/trashed', [AdminTask::class, 'trashed']);
    // GET request to retrieve tasks that are in the trash (soft deleted)
    // Returns a list of trashed tasks

    Route::put('tasks/{id}/restore', [AdminTask::class, 'restore']);
    // PUT request to restore a trashed task
    // Takes task ID, restores the task and returns the restored task

    Route::delete('tasks/{id}/forceDelete', [AdminTask::class, 'forceDelete']);
    // DELETE request to permanently delete a trashed task
    // Takes task ID, permanently deletes the task, and returns a success response
});
