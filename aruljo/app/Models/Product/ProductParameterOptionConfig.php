<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductParameterOptionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_parameter_id',
        'parameter_option',
        'dependencies', // added this
        'modified_by',
    ];

    // Cast dependencies JSON to array automatically
    protected $casts = [
        'dependencies' => 'array',
    ];

    public function parameter()
    {
        return $this->belongsTo(ProductParameter::class, 'product_parameter_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
