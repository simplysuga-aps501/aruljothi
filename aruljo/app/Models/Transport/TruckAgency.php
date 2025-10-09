<?php

namespace App\Models\Transport; // because it's in app/Models/Transport/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckAgency extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tp_truck_agencies'; // still points to your table

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'gst_number',
        'default_per_km_rate',
    ];
    public function agencyRates()
    {
        return $this->hasMany(TruckAgencyRate::class, 'truck_agency_id');
    }
}
