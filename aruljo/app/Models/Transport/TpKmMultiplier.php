<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;

class TpKmMultiplier extends Model
{
    protected $table = 'tp_min_km_multipliers';
    protected $fillable = ['min_km', 'max_km', 'multiplier'];
    public $timestamps = true;
}

