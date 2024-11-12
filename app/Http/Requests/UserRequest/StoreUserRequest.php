<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
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
     * Get the validation rules for creating a new user.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:55',
            'email' => 'required|string|email|max:30|unique:users,email',
            'password' => 'required|string|min:8|max:30'
        ];
    }

    /**
     * Customize error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => 'email should be unique, this email is already exists!'
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
