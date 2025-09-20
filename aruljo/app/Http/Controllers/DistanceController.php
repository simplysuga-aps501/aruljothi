<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DistancePincode;
use App\Models\DistanceCache;
use Illuminate\Support\Facades\Http;


class DistanceController extends Controller
{
    // Lookup by pincode
    public function byPincode(Request $request)
    {
        $pincode = $request->pincode;

        $records = DistancePincode::where('pincode', $pincode)->get();

        if ($records->isEmpty()) {
            return response()->json([
                'state' => '',
                'district' => '',
                'places' => [],
                'ids' => []
            ]);
        }

        return response()->json([
            'state' => $records->first()->state,
            'district' => $records->first()->district,
            'places' => $records->pluck('place'),
            'ids' => $records->pluck('id')
        ]);
    }

    protected function getDistance($startLat, $startLng, $endLat, $endLng)
    {
        $url = "https://routing.openstreetmap.de/routed-car/route/v1/driving/{$startLng},{$startLat};{$endLng},{$endLat}?overview=false";
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['routes'][0]['distance'])) {
                $meters = $data['routes'][0]['distance'];
                $km = $meters / 1000;
                $duration = $data['routes'][0]['duration'];

                return [
                    'distance_km' => round($km),
                    'duration_minutes' => round($duration / 60),
                ];
            }
        }
        return null;
    }

    public function calc(Request $request)
    {
        $toId = $request->to_id;

        // 1️⃣ Get 'from' location by name instead of hardcoding ID
        $fromLocation = DistancePincode::where('place', 'Chinnadharapuram')->first();

        if (!$fromLocation) {
            return response()->json(['error' => 'Origin location not found'], 404);
        }

        $fromId = $fromLocation->id;

        // 2️⃣ Check cache
        $cache = DistanceCache::where('from_location_id', $fromId)
            ->where('to_location_id', $toId)
            ->first();

        if ($cache && $cache->last_updated && strtotime($cache->last_updated) > strtotime('-7 days')) {
            return response()->json([
                'distance_km' => $cache->distance_km,
                'duration_minutes' => $cache->duration_minutes,
                'cached' => true
            ]);
        }

        // 3️⃣ Fetch coordinates
        $from = $fromLocation;
        $to = DistancePincode::find($toId);

        if (!$to) {
            return response()->json(['error' => 'Destination location not found'], 404);
        }

        // 4️⃣ Call OSRM API
        $result = $this->getDistance(
            $from->latitude,
            $from->longitude,
            $to->latitude,
            $to->longitude
        );

        if ($result) {
            // 5️⃣ Save/update cache
            DistanceCache::updateOrCreate(
                ['from_location_id' => $fromId, 'to_location_id' => $toId],
                [
                    'distance_km' => $result['distance_km'],
                    'duration_minutes' => $result['duration_minutes'],
                    'last_updated' => now()
                ]
            );

            return response()->json($result);
        }

        return response()->json(['error' => 'Could not fetch distance'], 500);
    }

}
