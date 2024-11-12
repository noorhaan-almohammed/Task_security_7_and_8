<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['content', 'user_id'];

     // polymorphic relation with task
    public function commentable()
    {
        return $this->morphTo();
    }

    // relation with user how added the comment
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
