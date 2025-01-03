<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leave extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'approved_by', 'start', 'end', 'status', 'leave_type', 'reason', 'file_attachment'];

    /**
     * Relationship to get the user who requested the leave.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to get the user who approved the leave.
     */
    public function approved()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    /**
     * Relationship to get the user who approved the leave.
     */
    public function comments()
    {
        return $this->belongsTo(LeaveComment::class, 'leave_id');
    }

    public function manager()
    {
        return $this->hasMany(LeaveUser::class);
    }
}
