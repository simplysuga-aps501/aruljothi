<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ParameterUnit extends Model
{
    // Explicitly set the table
    protected $table = 'prod_parameter_units';

    // Mass assignable fields
    protected $fillable = [
        'parameter_id',
        'unit',
        'description',
    ];

    /**
     * The parameter this unit belongs to
     */
    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id');
    }

    /**
     * Optional: configs that use this unit
     * (If ParameterConfig now references units)
     */
    public function parameterConfigs()
    {
        return $this->hasMany(ParameterConfig::class, 'unit_id');
    }
}
