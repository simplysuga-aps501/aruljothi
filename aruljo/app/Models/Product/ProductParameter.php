<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductParameter extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'input_type','modified_by' ];

    public function units()
    {
        return $this->belongsToMany(
            ProductParameterUnit::class,
            'product_parameter_unit_config',
            'product_parameter_id',
            'product_parameter_unit_id'
        );
    }

    public function options()
    {
        return $this->hasMany(ProductParameterOptionConfig::class, 'product_parameter_id');
    }

    public function productTemplate()
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }


}
