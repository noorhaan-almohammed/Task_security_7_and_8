<?php

namespace App\Http\Requests\TaskRequest;

use App\Rules\NotAdmin;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles the request for assigning a task to a user.
 */
class AssignTaskRequest extends FormRequest
{
    /**
     * Determines if the user is authorized to make this request.
     * The task can only be reassigned by the user who created it.
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
     * Validation rules for assigning a task.
     * Requires the 'assign_to' field to exist in the users table
     * and uses a custom rule to prevent assigning the task to an admin.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assign_to' => ['required', 'exists:users,id', new NotAdmin()],
        ];
    }

    /**
     * Custom validation messages for assign task request.
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
