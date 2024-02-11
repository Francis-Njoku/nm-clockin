<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = ['partnerName', 'partnerDetails', 'slug', 'banner', 'logo', 'createdBy'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('partnerName')->saveSlugsTo('slug');
    }
}
