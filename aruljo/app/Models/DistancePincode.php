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

    public function leads()
    {
        return $this->hasMany(\App\Models\Lead::class, 'delivery_location_id');
    }
    public function latestCache()
    {
        return $this->hasOne(\App\Models\DistanceCache::class, 'to_location_id')->latestOfMany('last_updated');
    }
    public function districtRate()
    {
        return $this->hasOne(TpDistrictRate::class, 'location_id');
    }
     /**
         * Get distinct list of states.
         */
        public static function getStates()
        {
            return self::distinct()
                ->pluck('state')
                ->sort()
                ->values();
        }

        /**
         * Get all districts for a given state.
         */
        public static function getDistrictsByState($state)
        {
            return self::where('state', $state)
                ->distinct()
                ->pluck('district')
                ->sort()
                ->values();
        }

        /**
         * Get all places for a given state + district.
         */
        public static function getPlacesByStateAndDistrict($state, $district)
        {
            return self::where('state', $state)
                ->where('district', $district)
                ->select('id', 'place')
                ->orderBy('place')
                ->get();
        }

}
