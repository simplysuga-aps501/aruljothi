<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterConfig extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_configs';

    protected $fillable = [
        'prod_template_id',
        'prod_parameter_id',
        'modified_by',
        'sort_order',
        'is_required',
        'default_value',
        'input_type',
        'group_name',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function options()
    {
        return $this->hasMany(ParameterOptionConfig::class, 'prod_parameter_id')
                    ->with([
                        'dependencies.parameter.unitConfigs.unit', // nested parameter units
                        'dependencies.parameter.options',
                    ]);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeWithUnits($query)
    {
        return $query->with(['parameter.unitConfigs.unit']);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute()
    {
        return $this->parameter?->name ?? '';
    }

    public function scopeForTemplate($query, $templateId)
    {
        return $query->where('prod_template_id', $templateId)
            ->with([
                'parameter' => function ($q) use ($templateId) {
                    $q->with([
                        'options' => function ($opt) use ($templateId) {
                            $opt->where('prod_template_id', $templateId)
                                ->with([
                                    'dependencies' => function ($dep) use ($templateId) {
                                        $dep->where('prod_template_id', $templateId)
                                            ->with(['requiredParameter.options' => function ($q) use ($templateId) {
                                                $q->where('prod_template_id', $templateId);
                                            }]);
                                    }
                                ]);
                        },
                    ]);
                }
            ]);
    }



}
