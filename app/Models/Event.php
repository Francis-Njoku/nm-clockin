<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'eventTypeId', 'createdBy', 'slug','amount', 'location', 'excerpt', 'description', 'booked', 'amount', 'registration', 'start', 'end', 'status'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function images()
    {
        return $this->hasMany(EventGallery::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
