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
    public function agencyRates()
    {
        return $this->hasMany(\App\Models\Transport\TruckAgencyRate::class, 'location_id');
    }
    public function leads()
    {
        return $this->hasMany(\App\Models\Lead::class, 'delivery_location_id');
    }
    public function latestCache()
    {
        return $this->hasOne(\App\Models\DistanceCache::class, 'to_location_id')->latestOfMany('last_updated');
    }

}
