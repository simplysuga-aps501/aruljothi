<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistancePincode extends Model
{
    use HasFactory;
    protected $fillable = [
        'pincode',
        'latitude',
        'longitude',
        'place',
        'district',
        'state'
    ];

    public static function getPincodeDetails($pincode)
    {
        return self::where('pincode', $pincode)->first();
    }

    public function getFullLocationAttribute()
    {
        $parts = array_filter([$this->place, $this->district, $this->state]);
        return implode(', ', $parts);
    }
}
