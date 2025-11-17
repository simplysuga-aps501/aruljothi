<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksModifiedBy;

class TpOffice extends Model
{
    use HasFactory, SoftDeletes, TracksModifiedBy;

    protected $table = 'tp_offices';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'gst_number',
        'location_id',
        'preferred_states',
        'preferred_districts',
    ];

    protected $casts = [
        'preferred_states' => 'array',
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
     * The user who last modified this record
     */
    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }

    public function getPreferredStatesListAttribute(): string
    {
        return $this->preferred_states
            ? implode(', ', $this->preferred_states)
            : '';
    }

    public function getPreferredDistrictsListAttribute(): string
    {
        return $this->preferred_districts
            ? implode(', ', $this->preferred_districts)
            : '';
    }

    public function isPreferredForDistrict(string $district): bool
    {
        return in_array($district, $this->preferred_districts ?? []);
    }

    public function isPreferredForState(string $state): bool
    {
        return in_array($state, $this->preferred_states ?? []);
    }

    public function districtRates()
    {
        return $this->hasMany(TpDistrictRate::class, 'office_id');
    }
}
