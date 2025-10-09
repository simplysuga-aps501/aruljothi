<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transport\TruckAgency;
use App\Models\Transport\TruckAgencyRate;
use App\Models\Transport\TruckType;
use App\Models\DistancePincode;
use Illuminate\Support\Facades\Log;


class TruckAgencyController extends Controller
{
    // Display the agencies index page
    public function index()
    {
        $agencies = TruckAgency::with('agencyRates.truckType', 'agencyRates.location')->get();
        $truckTypes = TruckType::all();
        $locations  = DistancePincode::all();
        $states = DistancePincode::select('state')
                    ->distinct()
                    ->orderBy('state')
                    ->get();

        return view('transport.agency_index', compact('agencies', 'truckTypes', 'locations','states'));
    }

    // Store new agency & rates
    public function store(Request $request)
    {
        $request->validate([
            'agency_name'   => 'required|string',
            'truck_type_id' => 'required|exists:tp_truck_types,id',
            'location_id'   => 'required|array',
            'location_id.*' => 'exists:distance_pincodes,id',
            'rate'          => 'required|numeric|min:0',
        ]);

        // Create agency
        $agency = TruckAgency::create(['name' => $request->agency_name]);

        foreach ($request->location_id as $locId) {
            TruckAgencyRate::create([
                'truck_agency_id' => $agency->id,         // updated column name
                'truck_type_id'   => $request->truck_type_id,
                'location_id'     => $locId,
                'fixed_rate'      => $request->rate,      // matches model
            ]);
        }
        return redirect()->back()->with('success', 'Agency and rates added successfully!');
    }


    // Load states for initial dropdown
    public function create()
    {
        $states = DistancePincode::select('state')
            ->distinct()
            ->orderBy('state')
            ->get();
        Log::info('States loaded from DistancePincode:', $states->toArray());

        return view('transport.agency.create', compact('states'));
    }

    // AJAX: get districts for a state
    public function getDistricts($state)
    {
        $districts = DistancePincode::where('state', $state)
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->get();

        return response()->json($districts);
    }

    // AJAX: get places for a district
    public function getPlaces($state, $district)
    {
        $places = DistancePincode::where('state', $state)
            ->where('district', $district)
            ->select('id', 'place', 'pincode')
            ->orderBy('place')
            ->get();

        return response()->json($places);
    }

}
