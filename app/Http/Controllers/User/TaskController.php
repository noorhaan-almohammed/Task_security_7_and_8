<?php

namespace App\Http\Controllers\User;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\TaskRequest\ReassignTaskRequest;
use App\Http\Requests\CommentRequest\AddCommentRequest;
use App\Http\Requests\SttoreTaskRequest\StoreTaskRequest;
use App\Http\Requests\TaskRequest\UpdateStatusTaskRequest;
use App\Http\Requests\AttachmentRequest\AddAttachmentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $taskService;

    /**
     * Constructor method to inject the TaskService into the controller.
     *
     * @param TaskService $taskService The task service instance.
     */
    public function __construct(TaskService $taskService){
       $this->taskService = $taskService;  // Inject TaskService to handle business logic
    }

    /**
     * List all tasks assigned to the authenticated user, retrieved from the cache.
     *
     * This method checks if the tasks for the authenticated user are already cached,
     * if not, it fetches them from the database and stores them in the cache for 1 hour (3600 seconds).
     *
     * @return mixed|\Illuminate\Http\JsonResponse The response containing the tasks.
     */
    public function index()
    {
        // Attempt to fetch tasks from cache for the current user
        $tasks = Cache::remember('tasks_'. Auth::user()->id , 3600 , function(){
            return  Auth::user()->tasks;  // Retrieve tasks assigned to the logged-in user
        });

        // Log the cached tasks (for debugging purposes)
        Log::info (Cache::get('tasks_'. Auth::user()->id));

        // Return the tasks in a success response with a 200 HTTP status code
        return parent::successResponse('Tasks',
            TaskResource::collection($tasks), // Return the tasks wrapped in a resource collection
            'Tasks retrieved successfully',     // Success message
            200);                               // HTTP status code for success
    }

    /**
     * Update the status of a specific task.
     *
     * This method updates the status of the task based on the provided request data.
     * It uses the `TaskService` to handle the actual status update logic.
     *
     * @param UpdateStatusTaskRequest $request The request containing the validated status data.
     * @param Task $task The task instance to update.
     * @return \Illuminate\Http\JsonResponse The updated task wrapped in a TaskResource.
     * @throws \App\Http\Requests\TaskRequest\UpdateStatusTaskRequest If validation fails.
     */
    public function updateStatus(UpdateStatusTaskRequest $request, Task $task)
    {
        // Validate and extract the status data from the request
        $status = $request->validated();

        // Call the taskService to update the task status
        $task = $this->taskService->updateStatus($status, $task);

        // Return a success response with the updated task
        return parent::successResponse('Task',
            new TaskResource($task), // Return the updated task wrapped in a resource
            'Task status updated successfully', // Success message
            201); // HTTP status code for created (successful update)
    }
}
