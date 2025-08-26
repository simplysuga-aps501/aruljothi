<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterUnit extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_units';

    protected $fillable = ['name','modified_by'];

    public function parameters()
    {
        return $this->belongsToMany(
            Parameter::class,
            'prod_parameter_unit_config',
            'prod_parameter_unit_id',
            'prod_parameter_id'
        );
    }

    public function unitConfigs()
    {
        return $this->hasMany(ParameterUnitConfig::class, 'prod_parameter_unit_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}

