<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    // Stops validation on the first failure
    protected $stopOnFirstFailure = true;

    /**
     * Check if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules for updating an existing user.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|max:55',
            'email' => 'nullable|string|email|max:30',
            'password' => 'nullable|string|min:8|max:30',
        ];
    }

    /**
     * Handle failed validation and return error message.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    public function failedValidation($validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 'error',
                'message' => "error validation",
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}
