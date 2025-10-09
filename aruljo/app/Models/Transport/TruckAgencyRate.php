<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckAgencyRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_truck_agency_rates';

    protected $fillable = [
        'truck_agency_id',
        'truck_type_id',
        'location_id',
        'fixed_rate',
    ];

    public function agency()
    {
        return $this->belongsTo(TruckAgency::class, 'truck_agency_id');
    }

    public function truckType()
    {
        return $this->belongsTo(TpTruckType::class, 'truck_type_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\DistancePincode::class, 'location_id');
    }
}
