<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotePriceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_version_id',
        'product_id',
        'total_qty',
        'unit_price',
        'transport_unit',
        'total_unit_price',
        'total_price',
    ];

    public function version()
    {
        return $this->belongsTo(QuoteVersion::class, 'quote_version_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Product::class);
    }
}

