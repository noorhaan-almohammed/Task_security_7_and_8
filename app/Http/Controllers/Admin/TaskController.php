<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Task;
use App\Models\User;
use App\Jobs\dailyReport;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Catch_;
use App\Http\Services\TaskService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use function PHPUnit\Framework\isEmpty;
use App\Notifications\DailyReportNotification;
use App\Http\Requests\TaskRequest\StoreTaskRequest;
use App\Http\Requests\TaskRequest\AssignTaskRequest;
use App\Http\Requests\TaskRequest\ReassignTaskRequest;
use App\Http\Requests\CommentRequest\AddCommentRequest;
use App\Http\Requests\AttachmentRequest\AddAttachmentRequest;

class TaskController extends Controller
{
    protected $taskService;

    /**
     * Constructor to inject TaskService
     */
    public function __construct(TaskService $taskService){
       $this->taskService = $taskService;  // inject taskService to the controller
    }

    /**
     * Create and store a new task.
     * @param \App\Http\Requests\TaskRequest\StoreTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {
        $taskData = $request->validated();
        $task = $this->taskService->createTask($taskData);
        return parent::successResponse('Task', new TaskResource($task), 'Task Created Successfully', 200);
    }

    /**
     * Reassign an existing task to a new user.
     *
     * @param ReassignTaskRequest $request Validated data for reassigning task.
     * @param Task $task The task to reassign.
    * @return \Illuminate\Http\JsonResponse
     */
    public function reassignTask(ReassignTaskRequest $request, Task $task)
    {
        $taskData = $request->validated();
        $task = $this->taskService->reassignTask($taskData, $task);
        return parent::successResponse('Task', $task, 'Task Reassigned Successfully', 201);
    }

    /**
     * Assign a task to a user if not already assigned.
     *
     * @param AssignTaskRequest $request Validated data for task assignment.
     * @param Task $task The task to be assigned.
    * @return \Illuminate\Http\JsonResponse
     */
    public function assignTask(AssignTaskRequest $request, Task $task)
    {
        if($task->assign_to){
            return response()->json(['message' => 'Task already assigned to user'], 409);
        }
        $taskData = $request->validated();
        $task = $this->taskService->assignTask($taskData, $task);
        return parent::successResponse('Task', $task, 'Task Assigned Successfully', 201);
    }

    /**
     * Add a comment to a specified task.
     *
     * @param AddCommentRequest $request Validated data for comment.
     * @param int $task_id The ID of the task to add the comment to.
    * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(AddCommentRequest $request, $task_id)
    {
        $comment = $request->validated();
        $task = $this->taskService->addComment($comment, $task_id);
        return parent::successResponse('Task', new TaskResource($task), 'Comment added successfully', 201);
    }

    /**
     * Retrieve a specific task by ID.
     *
     * @param Task $task The task to retrieve.
     * * @return \Illuminate\Http\JsonResponse
     */
    public function showTask(Task $task)
    {
        return parent::successResponse('Task', new TaskResource($task), 'Task retrieved Successfully', 200);
    }

    /**
     * Filter tasks based on various criteria and cache results.
     ** @return \Illuminate\Http\JsonResponse
     */
    public function showFilterTask()
    {
        $status = request()->query('status');
        $priority = request()->query('priority');
        $type = request()->query('type');
        $depends_on = request()->query('depends_on');
        $assign_to = request()->query('assign_to');
        $due_date = request()->query('due_date');

        $cacheKey = 'tasks_' . 'status: '.($status ?? 'all_status') . ' & ' .
                               'priority: '.($priority ?? 'all_priority') . ' & ' .
                               'type: '.($type ?? 'all_type') . ' & ' .
                               'depends_on: '.($depends_on ?? 'all_depends_on') . ' & ' .
                               'assign_to: '.($assign_to ?? 'all_assign_to') . ' & ' .
                               'due_date: '.($due_date ?? 'all_due_date');
        Log::info(Cache::get($cacheKey));

        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($assign_to, $due_date, $status, $priority, $type, $depends_on) {
            return Task::filterTasks($assign_to, $due_date, $status, $priority, $type, $depends_on)->simplePaginate();
        });

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No Task matched!'], 404);
        }

        return parent::successResponse(
            'Tasks',
            TaskResource::collection($tasks)->response()->getData(true),
            'Tasks retrieved successfully',
            200
        );
    }

    /**
     * Trigger daily report generation for the authenticated user.
     ** @return \Illuminate\Http\JsonResponse
     */
    public function showReportTask()
    {
        $userId = auth()->id();
        dispatch(new DailyReport($userId));

        return response()->json([
            'message' => 'Report generation started. You will receive a notification once it is ready.',
        ], 200);
    }

    /**
     * Display a list of blocked tasks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBlockedTasks()
    {
        $tasks = Task::blockedTasks()->simplePaginate(10);
        if($tasks->isEmpty()){
            return response()->json(['message'=>'No Task matched!'],404);
        }
        return parent::successResponse('Tasks', $tasks, 'Tasks retrieved Successfully', 200);
    }

    /**
     * Delete a task if the user is the creator.
     *
     * @param Task $Task The task to be deleted.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $Task)
    {
        if (!$Task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }
        if(auth()->id() !== $Task->created_by){
            return response()->json(['message'=>'You cannot delete this task, you\'re not the creator!'],403);
        }
        $oldTask = $Task;
        $this->taskService->deleteTask($Task);
        return parent::successResponse('task', $oldTask, "Task Deleted Successfully", 200);
    }

    /**
     * Retrieve and paginate trashed (soft deleted) tasks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed()
    {
        $trashedTasks = $this->taskService->trashedListTask();
        if($trashedTasks->isEmpty()){
            return response()->json(['message'=>'No trashed Tasks found!'],404);
        }
        return parent::successResponse('Tasks', $trashedTasks, 'Trashed Tasks retrieved Successfully', 200);
    }

    /**
     * Restore a trashed (soft deleted) task by ID.
     *
     * @param int $id ID of the trashed task to restore.
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $Task = $this->taskService->restoreTask($id);
        if(!$Task){
            return response()->json(['message'=>"Task not found"],404);
        }
        return parent::successResponse("Task", $Task, "Task restored Successfully", 200);
    }

    /**
     * Permanently delete a trashed task by ID.
     *
     * @param int $id ID of the trashed task to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $this->taskService->forceDeleteTask($id);
        return parent::successResponse("Task", null, "Task deleted Permanently", 200);
    }
}
