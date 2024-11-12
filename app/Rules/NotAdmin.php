<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotAdmin implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try{
            $user = User::findOrFail($value);
            if( $user->role_id == 1){
                $fail('Cannot assign task to an admin.');
            }
        }
        catch(ModelNotFoundException $e){
            // $fail('User not found.');
            Log::error('Error finding user: ' . $e->getMessage());
            // throw new HttpResponseException(response()->json('Trying to assign task to User does not found!',404), );
        }

    }
}
