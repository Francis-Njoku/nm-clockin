<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttendance extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'attendance_id','ipAddress','clock','comment','status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
