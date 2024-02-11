<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerRole extends Model
{
    use HasFactory;

    protected $fillable = ['partnerName', 'partnerDetails', 'slug', 'banner', 'logo', 'createdBy'];
}
