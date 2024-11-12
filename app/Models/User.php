<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * Define relation with Task model where a user has many tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assign_to');
    }

    /**
     * Define relation with Task model for tasks created by user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get user along with assigned tasks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersWithTasks()
    {
        $usersWithTasks = User::with('tasks')->get();
        return response()->json($usersWithTasks);
    }

    /**
     * Get a user with their tasks by user ID.
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getUserWithTasks($id)
    {
        return  User::with('tasks')->findOrFail($id);
    }

    /**
     * Define relation with Comment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Define relation with TaskStatusUpdate model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskUpdate()
    {
        return $this->hasMany(TaskStatusUpdate::class);
    }

    /**
     * Define relation with Role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Hidden attributes for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'boolean'
    ];

    /**
     * Get the JWT identifier for the user.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get custom JWT claims for the user.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
