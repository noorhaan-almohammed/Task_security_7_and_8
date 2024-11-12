<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\Attachment;
use App\Models\TaskStatusUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;

class Task extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['title','description','type','status','priority','due_date','assign_to','created_by'];

    protected $hidden =['created_at' , 'updated_at','deleted_at'];
    // relation task with user ... task belong to one user how assigned
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }
    // relation task with user ... task belong to one user how created
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    // relation task with task ..task may depends on another task or more
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', foreignPivotKey: 'task_id', relatedPivotKey: 'depends_on_id')
                    ->withTimestamps();
    }
    // related with comments
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // related with attachments
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // realated with updates status
    public function statusUpdates()
    {
        return $this->hasMany(TaskStatusUpdate::class);
    }
    /**
     * filter tasks using query
     * @param mixed $query
     * @param mixed $status
     * @param mixed $priority
     * @param mixed $type
     * @param mixed $depends_on
     * @param mixed $assign_to
     * @param mixed $due_date
     * @return mixed
     */
    public function scopeFilterTasks( $query,$assign_to = null ,$due_date = null, $status = null, $priority = null, $type = null, $depends_on = null)
    {
        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($priority)) {
            $query->where('priority', $priority);
        }
        if (!empty($type)) {
            $query->where('type', $type);
        }
        if (!empty($due_date)) {
            $query->where('due_date', $due_date);
        }
        if (!empty($assign_to)) {
            $query->where('assign_to', $assign_to);
        }
        if (!empty($depends_on) && $depends_on !== "null") {
            $query->whereHas('dependencies', function ($taskQuery) use ($depends_on) {
                $taskQuery->where('depends_on_id', $depends_on);
            });
        } else{
            $query->whereDoesntHave('dependencies');
        }
        return $query->where('created_by', auth()->id());
    }
    public function scopeDailyReport($query, $userId){
        return $query->select('title', 'type', 'status', 'priority')
            ->whereDate('due_date', today())
            ->where('created_by', $userId);
    }
    public function scopeBlockedTasks($query){
        return $query->select('title', 'type', 'priority','due_date')
        ->where('status' , 'Blocked')
        ->where('created_by', auth()->id());
    }
}
