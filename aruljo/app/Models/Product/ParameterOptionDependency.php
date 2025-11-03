<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterOptionDependency extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_option_dependencies';

    protected $fillable = ['option_id', 'req_param_id'];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'req_param_id');
    }

    public function option()
    {
        return $this->belongsTo(ParameterOptionConfig::class, 'option_id');
    }
}
