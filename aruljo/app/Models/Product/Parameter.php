<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    // Explicitly set the table
    protected $table = 'prod_parameters';

    protected $fillable = [
        'name',
        'description',
        'input_type',
        'modified_by'
    ];

    public function units()
    {
        return $this->belongsToMany(
            ParameterUnit::class,
            'prod_parameter_unit_configs',
            'prod_parameter_id',
            'prod_parameter_unit_id'
        );
    }

    public function options()
    {
        return $this->hasMany(ParameterOptionConfig::class, 'prod_parameter_id');
    }

    public function template()
    {
        return $this->belongsTo(template::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
