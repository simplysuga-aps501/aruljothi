<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    protected $table = 'prod_templates';
    protected $fillable = ['name', 'abbreviation','modified_by'];

    public function parameterConfigs()
    {
        return $this->hasMany(ParameterConfig::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }

}
