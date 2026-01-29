<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterOptionConfig extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_option_configs';

    protected $fillable = [
        'prod_parameter_id',
        'prod_template_id',
        'parameter_option',
        'abbreviation',
        'dependencies',
        'is_active',
        'modified_by',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'is_active'    => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }

    public function dependencies()
    {
        return $this->hasMany(ParameterOptionDependency::class, 'option_id');
    }

    public function dependentParameters()
    {
        return $this->hasManyThrough(
            Parameter::class,
            ParameterOptionDependency::class,
            'option_id',
            'id',
            'id',
            'req_param_id'
        )->with('unitConfigs.unit');
    }

    public function dependenciesWithParameters()
    {
        return $this->dependencies()->with(['parameter.unitConfigs.unit']);
    }
}
