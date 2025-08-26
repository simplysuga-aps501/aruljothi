<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParameterValue extends Model
{
    use HasFactory;

    // ✅ Explicitly set table name with prefix
    protected $table = 'prod_parameter_values';

    protected $fillable = [
        'product_id',
        'prod_parameter_id',
        'value',
        'unit_id',
        'modified_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class, 'prod_parameter_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
