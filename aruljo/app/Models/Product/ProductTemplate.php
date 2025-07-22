<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'modified_by'];

    public function productParameterConfigs()
    {
        return $this->hasMany(ProductParameterConfig::class);
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
