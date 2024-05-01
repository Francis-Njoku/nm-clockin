<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerRole extends Model
{
    use HasFactory;

    protected $fillable = ['partner_id', 'event_id', 'role', 'createdBy'];
}
