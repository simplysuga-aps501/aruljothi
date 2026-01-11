<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transport\TruckType;

class QuoteTruck extends Model
{
    protected $fillable = [
        'quote_version_id', 'truck_type_id', 'body_type',
        'truck_cost', 'distance_km', 'rate_per_km','fixed_rate','multiplier','unloading_charges','total_weight',
    ];

    public function version()
    {
        return $this->belongsTo(QuoteVersion::class, 'quote_version_id');
    }

    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }

    public function products()
    {
        return $this->hasMany(QuoteTruckProduct::class, 'quote_truck_id');
    }
}

