<?php

namespace App\Imports;

use App\Models\DistancePincode;
use Illuminate\Support\Facades\Log;
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

        // Skip invalid coordinates
        if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            Log::warning("Skipping invalid coordinates for place: {$row['place']}", $row);
            return null;
        }

        // ✅ Use updateOrCreate to prevent duplicates
        DistancePincode::updateOrCreate(
            ['pincode' => $row['pincode']], // unique key
            [
                'state'     => $row['state'] ?? null,
                'district'  => $row['district'] ?? null,
                'place'     => $row['place'] ?? null,
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ]
        );

        // Returning null prevents Laravel Excel from trying to "insert" this again.
        return null;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
