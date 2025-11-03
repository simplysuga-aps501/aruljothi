<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterConfig extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_configs';

    protected $fillable = [
        'product_template_id',
        'prod_parameter_id',
        'modified_by',
    ];

    public function template()
    {
        return $this->belongsTo(template::class, 'product_template_id');
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
