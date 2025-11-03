<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LeadProductMap extends Model
{
    protected $table = 'lead_product_map';

    protected $fillable = [
        'lead_id',
        'product_id',
        'quantity',
    ];

    public function lead() {
        return $this->belongsTo(Lead::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
