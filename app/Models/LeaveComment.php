<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveComment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'leave_id', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
