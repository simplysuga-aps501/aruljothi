<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckCapacity extends Model
{
    use HasFactory;

    protected $table = 'tp_truck_capacities';

    protected $fillable = [
        'product_id',
        'truck_type_id',
        'body_type',
        'max_units',
    ];

    /**
     * Relationships
     */

    // A truck capacity belongs to a product
    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Product::class, 'product_id');
    }

    // A truck capacity belongs to a truck type
    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type_id');
    }
    //Return truck types from schema
    public static function getBodyTypes(): array
    {
        $type = \DB::selectOne("SHOW COLUMNS FROM tp_truck_capacities WHERE Field = 'body_type'")->Type;

        preg_match("/^enum\((.*)\)$/", $type, $matches);

        $enum = str_getcsv($matches[1], ',', "'");

        // just return the raw enum values
        return $enum;
    }

    public function getPracticalWeightCapacityAttribute()
    {
        $productWeight = $this->product->weight_kg ?? 0;
        $maxUnits = $this->max_units ?? 0;

        return $productWeight * $maxUnits;
    }


}