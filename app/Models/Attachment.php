<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'attachable_type',
        'attachable_id',
        'user_id',
    ];
    // polymorphic relation with task
    public function attachable()
    {
        return $this->morphTo();
    }
}
