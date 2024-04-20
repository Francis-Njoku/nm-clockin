<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ticket', 'event_id', 'attended'];
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
