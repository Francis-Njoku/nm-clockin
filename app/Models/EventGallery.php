<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\EventGallery;


class EventGallery extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'isFeatured', 'event_id'];

    public function images()
    {
        return $this->hasMany(EventGallery::class);
    }
}
