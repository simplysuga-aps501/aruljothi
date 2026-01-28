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

    public function options()
    {
        return $this->hasMany(ParameterOptionConfig::class, 'prod_parameter_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
