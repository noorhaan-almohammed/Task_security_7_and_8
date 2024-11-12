<?php

namespace App\Http\Requests\TaskRequest;

use App\Rules\NotAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Handles the request for storing a new task.
 */
class StoreTaskRequest extends FormRequest
{
    // Stop validation on the first failure
    protected $stopOnFirstFailure = true;

    /**
     * Authorization check (allows any user to create a task).
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for storing a task.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:30|unique:tasks,title',
            'description' => 'nullable|string|max:225',
            'type' => 'required|in:Bug,Feature,Improvement',
            'status' => 'required|in:Open,Blocked',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'required|date|after:today',
            'assign_to' => ['required', 'exists:users,id', new NotAdmin()],
            'depends_on' => 'required_if:status,Blocked|array',
            'depends_on.*' => 'exists:tasks,id',
        ];
    }

    /**
     * Handles failed validation response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    public function failedValidation($validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 'error',
                'message' => "Validation error",
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}
