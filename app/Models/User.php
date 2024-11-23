<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'firstName',
        'lastName',
        'isStaff',
        'identity',
        'phone',
        'password',
        'hasManager', 
        'joined',
        'manager_id',
        'status',
        'gmt',
        'department_id'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function Manager()
    {
        return $this->belongsTo(User::class);
    }

    public function Department()
    {
        return $this->belongsTo(Department::class);
    }

    // Many-to-many relationship for users (LeaveUser pivot)
    public function leaves()
    {
        return $this->belongsToMany(Leave::class, 'leave_users', 'user_id', 'leave_id');
    }
}
