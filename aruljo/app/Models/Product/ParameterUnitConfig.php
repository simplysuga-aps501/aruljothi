<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterUnitConfig extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_unit_configs';

    protected $fillable = [
        'prod_template_id',
        'prod_parameter_id',
        'prod_parameter_unit_id',
        'allow_custom_unit',
        'modified_by',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParameterUnit::class, 'prod_parameter_unit_id');
    }
    public function getUnitNameAttribute()
    {
        return $this->unit?->unit; // returns null if unit is missing
    }

}
