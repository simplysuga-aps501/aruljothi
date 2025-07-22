<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductParameterConfig extends Model
{
    use HasFactory;
    protected $fillable = [
            'product_template_id',
            'product_parameter_id',
            'modified_by',
        ];

        public function productTemplate()
        {
            return $this->belongsTo(ProductTemplate::class);
        }

        public function productParameter()
        {
            return $this->belongsTo(ProductParameter::class);
        }

        public function modifiedBy()
        {
            return $this->belongsTo(\App\Models\User::class, 'modified_by');
        }
}
