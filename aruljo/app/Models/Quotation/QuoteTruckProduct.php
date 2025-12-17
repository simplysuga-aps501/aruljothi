<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteTruckProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_truck_id',
        'product_id',
        'allocated_qty',
        'weight_per_unit',
        'max_allowed_qty',
    ];

    public function truck()
    {
        return $this->belongsTo(QuoteTruck::class, 'quote_truck_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Product::class);
    }
}
