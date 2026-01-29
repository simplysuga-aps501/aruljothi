<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ParameterUnit extends Model
{
    protected $table = 'prod_parameter_units';

    protected $fillable = [
        'parameter_id',
        'unit',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // The parameter this unit belongs to
    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id');
    }

    // Configs (template + parameter mappings) that use this unit
    public function unitConfigs()
    {
        return $this->hasMany(ParameterUnitConfig::class, 'prod_parameter_unit_id');
    }

    // Optional: all templates that use this unit via configs
    public function templates()
    {
        return $this->hasManyThrough(
            Template::class,
            ParameterUnitConfig::class,
            'prod_parameter_unit_id',
            'id',
            'id',
            'prod_template_id'
        );
    }
}
