<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateParameterUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'prod_template_id',
        'prod_parameter_id',
        'unit_id',
        'allow_custom_unit',
        'modified_by',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function unit()
    {
        return $this->belongsTo(ParameterUnit::class, 'unit_id');
    }
}
