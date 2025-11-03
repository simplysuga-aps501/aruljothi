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
        'parameter_option',
        'dependencies',
        'modified_by',
    ];

    protected $casts = [
        'dependencies' => 'array',
    ];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }

    public function dependencies()
    {
        return $this->hasMany(ParameterOptionDependency::class, 'option_id');
    }
}
