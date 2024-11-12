<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'status', 'changed_by'];

    // relation with task each task has many status update
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // relation with user how changed the status of task
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
