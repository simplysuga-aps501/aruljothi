<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductParameterValue extends Model
{
    use HasFactory;
    protected $fillable = [
            'product_id',
            'product_parameter_id',
            'value',
            'unit_id',
            'modified_by',
        ];

        public function product()
        {
            return $this->belongsTo(Product::class);
        }

        public function parameter()
        {
            return $this->belongsTo(ProductParameter::class, 'product_parameter_id');
        }

        public function unit()
        {
            return $this->belongsTo(Unit::class);
        }

        public function modifier()
        {
            return $this->belongsTo(\App\Models\User::class, 'modified_by');
        }
}
