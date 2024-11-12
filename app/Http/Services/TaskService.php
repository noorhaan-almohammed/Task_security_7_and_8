<?php

namespace App\Http\Services;

use Exception;
use App\Models\Task;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskService
{
    /**
     * Create a new task.
     *
     * @param array $fieldInputs The input data for creating the task.
     * @return Task The created task instance.
     * @throws HttpResponseException If there is an error during task creation.
     */
    public function createTask(array $fieldInputs)
    {
        DB::beginTransaction();
        try {
            // Create the new task in the database
            $task = Task::create([
                'title' => $fieldInputs['title'],
                'description' => $fieldInputs['description'],
                'type' => $fieldInputs['type'],
                'status' => $fieldInputs['status'],
                'priority' => $fieldInputs['priority'],
                'due_date' => $fieldInputs['due_date'],
                'assign_to' => $fieldInputs['assign_to'],
                'created_by' => auth()->user()->id,
            ]);

            // Log the status update for the task
            TaskStatusUpdate::create([
                'task_id' => $task->id,
                'status' => $task->status,
                'changed_by' => auth()->user()->id
            ]);

            // If task status is blocked, add dependencies
            if (isset($fieldInputs['depends_on']) && $task->status === 'Blocked') {
                foreach ($fieldInputs['depends_on'] as $dependentTaskId) {
                    DB::table('task_dependencies')->insert([
                        'task_id' => $task->id,
                        'depends_on_id' => $dependentTaskId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Load the related models like assignee, dependencies, comments, etc.
            $task->load(['assignee', 'createdBy', 'dependencies', 'comments', 'attachments']);

            DB::commit();
            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Reassign a task to a new user.
     *
     * @param array $data The data containing the new assignee's ID.
     * @param Task $task The task to be reassigned.
     * @return Task The reassigned task.
     * @throws HttpResponseException If there is an error during reassignment.
     */
    public function reassignTask($data, $task)
    {
        try {
            // Reassign the task to the new user
            $task->assign_to = $data['assign_to'];
            $task->save();
            return $task;
        } catch (Exception $e) {
            Log::error('Error reassigning Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Assign a task to a user.
     *
     * @param array $data The data containing the assignee's ID.
     * @param Task $task The task to be assigned.
     * @return Task The assigned task.
     * @throws HttpResponseException If there is an error during assignment.
     */
    public function assignTask($data, $task)
    {
        try {
            // Assign the task to the user
            $task->assign_to = $data['assign_to'];
            $task->save();
            return $task;
        } catch (Exception $e) {
            Log::error('Error assigning task to user: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Add a comment to a task.
     *
     * @param array $data The data containing the comment content.
     * @param int $task_id The ID of the task to add the comment to.
     * @return Task The task with the newly added comment.
     * @throws HttpResponseException If there is an error adding the comment.
     */
    public function addComment($data, $task_id)
    {
        try {
            // Find the task by its ID
            $task = Task::findOrFail($task_id);

            // Add the comment to the task
            $task->comments()->create([
                'content' => $data['content'],
                'user_id' => auth()->id(),
            ]);

            // Reload the task with its comments
            $task = $task->load('comments');
            return $task;
        } catch (Exception $e) {
            Log::error('Error adding comment to Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Update the status of a task.
     *
     * @param array $data The data containing the new status.
     * @param Task $task The task whose status is to be updated.
     * @return Task The task with the updated status.
     * @throws HttpResponseException If there is an error updating the status.
     */
    public function updateStatus($data, $task)
    {
        DB::beginTransaction();
        try {
            // Update the task's status
            $task->status = $data['status'];
            $task->save();

            // Log the status update
            TaskStatusUpdate::create([
                'task_id' => $task->id,
                'status' => $task->status,
                'changed_by' => auth()->user()->id
            ]);

            // Reload the task with its related models
            $task->load(['assignee', 'createdBy', 'dependencies', 'comments', 'attachments']);
            DB::commit();
            return $task;
        } catch (ModelNotFoundException $e) {
            Log::error('Error finding Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('Task not found', 404));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating Task status: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Add an attachment to a task.
     *
     * @param object $data The uploaded file data.
     * @param int $id The ID of the task to add the attachment to.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success.
     * @throws HttpResponseException If there is an error adding the attachment.
     */
    public function addAttachment($data, $id)
    {
        $task = Task::findOrFail($id);

        // Store the uploaded attachment
        $path = $data->file('attachment')->store('attachments');

        // Create a record for the attachment in the database
        $attachment = $task->attachments()->create([
            'file_path' => $path,
        ]);

        return response()->json(['message' => 'Attachment added successfully', 'attachment' => $attachment], 201);
    }

    /**
     * Delete a task.
     *
     * @param Task $Task The task to be deleted.
     * @return void
     * @throws HttpResponseException If there is an error during deletion.
     */
    public function deleteTask($Task)
    {
        try {
            $Task->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('Error finding Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('Task not found', 404));
        } catch (Exception $e) {
            Log::error('Error deleting Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     *  Display a paginated listing of the trashed (soft-deleted) tasks.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function trashedListTask()
    {
        try {
            return Task::onlyTrashed()->simplePaginate(10);
        } catch (Exception $e) {
            Log::error('Error fetching trashed tasks: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Restore a trashed (soft-deleted) task by its ID.
     *
     * @param int $id The ID of the trashed task to restore.
     * @return Task The restored task.
     * @throws HttpResponseException If there is an error restoring the task.
     */
    public function restoreTask($id)
    {
        try {
            $Task = Task::onlyTrashed()->findOrFail($id);
            $Task->restore();
            return $Task;
        } catch (ModelNotFoundException $e) {
            Log::error('Error finding Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('Task not found', 404));
        } catch (Exception $e) {
            Log::error('Error restoring Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }

    /**
     * Permanently delete a trashed (soft-deleted) task by its ID.
     *
     * @param int $id The ID of the trashed task to permanently delete.
     * @return void
     * @throws HttpResponseException If there is an error during the force delete.
     */
    public function forceDeleteTask($id)
    {
        try {
            $trashedTask = Task::onlyTrashed()->findOrFail($id);

            // Check if the user is the creator of the task
            if (auth()->id() !== $trashedTask->created_by) {
                throw new HttpResponseException(response()->json(['message' => 'You cannot delete this task, You\'re not the creator!'], 403));
            }

            $trashedTask->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error('Error finding Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json(['message' => 'Task not found'], 404));
        } catch (Exception $e) {
            Log::error('Error deleting Task: ' . $e->getMessage());
            throw new HttpResponseException(response()->json('There is something wrong in server', 500));
        }
    }
}
