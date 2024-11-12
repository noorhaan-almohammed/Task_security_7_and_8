<?php

namespace App\Rules;

use Closure;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSameUser implements ValidationRule
{
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($this->task){
            $user = User::find($value);
            if(!$user){
                $fail('User not found.');
            }
            if($this->task->created_by == $value){
                $fail('You cannot assign a task to yourself.');
            }
            if($this->task->assign_to == $value){
                $fail('Task is already assigned to this user.');
            }
        }
    }
}
