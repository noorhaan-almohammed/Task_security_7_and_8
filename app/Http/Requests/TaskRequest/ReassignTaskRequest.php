<?php

namespace App\Http\Requests\TaskRequest;

use App\Rules\NotAdmin;
use App\Rules\NotSameUser;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles the request for reassigning a task to a different user.
 */
class ReassignTaskRequest extends FormRequest
{
    /**
     * Checks if the user is authorized to reassign the task.
     * Only the task creator can reassign it.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        if (auth()->user()->id != $task->created_by) {
            abort(403, 'You are not authorized to reassign this task.');
        }
        return true;
    }

    /**
     * Validation rules for reassigning a task.
     * Uses custom rules to prevent assigning to an admin or to the same user.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $task = $this->route('task');
        return [
            'assign_to' => ['required', 'exists:users,id', new NotAdmin(), new NotSameUser($task)],
        ];
    }

    /**
     * Custom validation messages for reassign task request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'assign_to.exists' => 'User not found',
        ];
    }
}
