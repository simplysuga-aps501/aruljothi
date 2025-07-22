<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductParameterUnitConfig extends Model
{
    use HasFactory;
    protected $table = 'product_parameter_unit_config';

        protected $fillable = [
            'product_parameter_id',
            'product_parameter_unit_id',
            'modified_by',
        ];

        public function parameter()
        {
            return $this->belongsTo(ProductParameter::class, 'product_parameter_id');
        }

        public function unit()
        {
            return $this->belongsTo(ProductParameterUnit::class, 'product_parameter_unit_id');
        }

        public function modifiedBy()
        {
            return $this->belongsTo(\App\Models\User::class, 'modified_by');
        }

}
