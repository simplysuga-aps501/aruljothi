<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckType extends Model
{
    use HasFactory;

    /**
     * Explicitly set table name because it’s prefixed.
     */
    protected $table = 'tp_truck_types';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'capacity_kg',
        'description',
    ];

    /**
     * Example: One truck type can be linked to many product truck capacities.
     */
    public function productTruckCapacities()
    {
        return $this->hasMany(ProductTruckCapacity::class, 'truck_type_id');
    }
    public function agencyRates()
    {
        return $this->hasMany(TruckAgencyRate::class, 'truck_type_id');
    }
}
