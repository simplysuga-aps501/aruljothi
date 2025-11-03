<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hsncode extends Model
{
    use HasFactory;
    protected $fillable = [
            'name',
            'description',
            'modified_by',
        ];

    //Relationships

    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class, 'hsncode_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }


}
