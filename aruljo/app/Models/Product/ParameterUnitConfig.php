<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterUnitConfig extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_unit_configs';

    protected $fillable = [
        'prod_parameter_id',
        'prod_parameter_unit_id',
        'modified_by',
    ];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParameterUnit::class, 'prod_parameter_unit_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
