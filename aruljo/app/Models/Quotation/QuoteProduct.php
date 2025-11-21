<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;

class QuoteProduct extends Model
{
    protected $fillable = [
        'quote_truck_id', 'product_id', 'quantity', 'unit_price',
        'transport_unit', 'weight_per_unit', 'total_price'
    ];

    public function truck()
    {
        return $this->belongsTo(QuoteTruck::class, 'quote_truck_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

