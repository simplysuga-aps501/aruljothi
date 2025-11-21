<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;

class QuoteTruck extends Model
{
    protected $fillable = [
        'quote_version_id', 'truck_type_id', 'body_type', 'truck_count',
        'truck_cost', 'distance_km', 'multiplier'
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
        return $this->hasMany(QuoteProduct::class, 'quote_truck_id');
    }
}

