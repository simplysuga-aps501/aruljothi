<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterOptionDependency extends Model
{
    use HasFactory;

    protected $table = 'prod_parameter_option_dependencies';

    protected $fillable = [
        'option_id',
        'req_param_id',
        'prod_template_id',
        'is_required',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function option()
    {
        return $this->belongsTo(ParameterOptionConfig::class, 'option_id');
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'req_param_id')
                    ->with('unitConfigs.unit');
    }

    public function requiredParameter()
    {
        return $this->belongsTo(Parameter::class, 'req_param_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }
}
