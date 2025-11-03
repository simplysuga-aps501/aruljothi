<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistanceCache extends Model
{
    use HasFactory;

    protected $table = 'distance_cache';

    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'distance_km',
        'duration_minutes',
        'last_updated',
    ];

    protected $dates = ['last_updated'];
}
