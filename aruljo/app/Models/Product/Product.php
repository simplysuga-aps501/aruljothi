<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Product extends Model
{
    use HasFactory;
    protected $fillable = [
            'sku',
            'name',
            'description',
            'product_template_id',
            'unit_id',
            'hsncode_id',
            'stock_count',
            'modified_by',
        ];

        public function unit(): BelongsTo
        {
            return $this->belongsTo(Unit::class);
        }

        public function hsncode(): BelongsTo
        {
            return $this->belongsTo(Hsncode::class);
        }

        public function productTemplate(): BelongsTo
        {
            return $this->belongsTo(ProductTemplate::class);
        }
        public function parameterValues()
        {
            return $this->hasMany(ProductParameterValue::class);
        }
        public function modifiedBy()
        {
            return $this->belongsTo(\App\Models\User::class, 'modified_by');
        }

}
