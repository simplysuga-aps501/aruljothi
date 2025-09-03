<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Transport\TruckCapacity;


class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'prod_template_id',
        'unit_id',
        'hsncode_id',
        'stock_count',
        'selling_price',
        'manufacturing_cost',
        'weight_kg',
        'modified_by',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function hsncode(): BelongsTo
    {
        return $this->belongsTo(Hsncode::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'prod_template_id');
    }

    public function parameterValues(): HasMany
    {
        return $this->hasMany(ParameterValue::class, 'product_id');
    }

    public function truckCapacities(): HasMany
    {
        return $this->hasMany(TruckCapacity::class, 'product_id');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'modified_by');
    }
}
