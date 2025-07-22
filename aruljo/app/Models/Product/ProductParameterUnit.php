<?php

namespace App\Models\Product;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProductParameterUnit extends Model
{
    use HasFactory;
    protected $fillable = [
            'name',
            'modified_by',
        ];
    public function parameters()
    {
        return $this->belongsToMany(
            ProductParameter::class,
            'product_parameter_unit_config',
            'product_parameter_unit_id',
            'product_parameter_id'
        );
    }

    public function unitConfigs()
    {
        return $this->hasMany(ProductParameterUnitConfig::class, 'product_parameter_unit_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
