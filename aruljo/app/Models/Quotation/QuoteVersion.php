<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;

class QuoteVersion extends Model
{
    protected $fillable = [
        'quotation_id', 'version_number', 'subtotal', 'gst_rate', 'total_amount',
        'net_total', 'cost_per_kg', 'distance_km', 'delivery_location_id',
        'is_active', 'remarks', 'created_by'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function trucks()
    {
        return $this->hasMany(QuoteTruck::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

