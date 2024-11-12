<?php

namespace App\Http\Requests\TaskRequest;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Handles the request for updating the status of a task.
 */
class UpdateStatusTaskRequest extends FormRequest
{
    /**
     * Checks if the user is authorized to update the task status.
     * Only the assigned user can update the status.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        if (auth()->user()->id != $task->assign_to) {
            abort(403, 'You are not authorized to update the status of this task.');
        }
        return true;
    }

    /**
     * Validation rules for updating task status.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:In_Progress,Completed',
        ];
    }

    /**
     * Custom validation messages for update status request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'status.in' => 'Status should be In_Progress or Completed',
        ];
    }
}
