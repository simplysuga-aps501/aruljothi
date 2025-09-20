<?php

namespace App\Imports;

use App\Models\DistancePincode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DistancePincodesImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Validate latitude and longitude
        $latitude  = is_numeric($row['latitude'])  ? (float)$row['latitude']  : null;
        $longitude = is_numeric($row['longitude']) ? (float)$row['longitude'] : null;

        // Skip row if coordinates are invalid
        if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            // You can log or just skip silently
            \Log::warning("Skipping invalid coordinates for place: {$row['place']}", $row);
            return null; // Returning null tells Laravel Excel to skip this row
        }

        return new DistancePincode([
            'state'     => $row['state'],
            'district'  => $row['district'],
            'place'     => $row['place'],
            'pincode'   => $row['pincode'],
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ]);
    }


    // 👇 process 1000 rows at a time
    public function chunkSize(): int
    {
        return 1000;
    }
}
