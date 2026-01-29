<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    protected $table = 'prod_parameters';

    protected $fillable = [
        'name',
        'description',
        'input_type',
        'prod_template_id', // if parameters are template-specific
        'modified_by',
        // ⚠️ 'unit' column removed (deprecated)
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * All options for this parameter
     */
    public function options()
    {
        return $this->hasMany(ParameterOptionConfig::class, 'prod_parameter_id');
    }

    /**
     * Options filtered by a specific template
     */
    public function optionsForTemplate($templateId)
    {
        return $this->hasMany(ParameterOptionConfig::class, 'prod_parameter_id')
                    ->where('prod_template_id', $templateId)
                    ->where('is_active', true);
    }

    /**
     * Template this parameter belongs to (optional if parameters are template-specific)
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    /**
     * All unit configurations (across all templates)
     */
    public function unitConfigs()
    {
        return $this->hasMany(ParameterUnitConfig::class, 'prod_parameter_id', 'id')
                    ->with('unit');
    }

    /**
     * Unit configurations specific to a given template
     */
    public function unitConfigsForTemplate($templateId)
    {
        return $this->hasMany(ParameterUnitConfig::class, 'prod_parameter_id', 'id')
                    ->where('prod_template_id', $templateId)
                    ->with('unit');
    }

    /**
     * Parameters that depend on this parameter via option dependencies
     */
    public function dependentParameters()
    {
        return $this->hasManyThrough(
            Parameter::class,
            ParameterOptionDependency::class,
            'req_param_id', // Foreign key on dependency table pointing to this parameter
            'id',           // Foreign key on Parameter table
            'id',           // Local key on this Parameter model
            'option_id'     // Local key on dependency table pointing to option
        )->with('unitConfigs.unit');
    }

    /**
     * Quick accessor to get all unit names as array
     */
    public function units()
    {
        return $this->unitConfigs->pluck('unit.unit')->unique()->values()->all();
    }

    /**
     * User who last modified this parameter
     */
    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
