<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpOffice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_offices';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'gst_number',
        'default_per_km_rate',
        'location_id',
        'preferred_districts', // stores district names as JSON array
    ];

    protected $casts = [
        'preferred_districts' => 'array',
    ];

    /**
     * Each transport office belongs to one main location (optional)
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\DistancePincode::class, 'location_id');
    }


    /**
     * Accessor: Get preferred districts as a comma-separated string
     */
    public function getPreferredDistrictsListAttribute()
    {
        return $this->preferred_districts
            ? implode(', ', $this->preferred_districts)
            : '';
    }

    /**
     * Helper: Check if a given district name is marked as preferred
     */
    public function isPreferredForDistrict(string $district): bool
    {
        return in_array($district, $this->preferred_districts ?? []);
    }
}
