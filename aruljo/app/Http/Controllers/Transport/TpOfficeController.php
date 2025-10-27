<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TpOffice;
use App\Models\DistancePincode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load states for dropdown (distinct from distance_pincodes table)
        $states = DistancePincode::select('state')
            ->distinct()
            ->orderBy('state')
            ->get();

        // Load offices and decode preferred districts if stored as JSON
        $offices = TpOffice::orderByDesc('id')->get();

        // Add computed readable district list for display
        foreach ($offices as $office) {
            $districts = $office->preferred_districts ?? [];
            if (is_string($districts)) {
                $districts = json_decode($districts, true) ?? [];
            }
            $office->preferred_districts_list = !empty($districts)
                ? implode(', ', $districts)
                : '-';
        }

        return view('transport.offices.index', compact('offices', 'states'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $states = DB::table('distance_pincodes')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->get();

        return view('transport.office.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'default_per_km_rate' => 'nullable|numeric|min:0',
            'preferred_districts' => 'nullable|array',
            'location_id' => 'required|exists:distance_pincodes,id',
        ]);

        TpOffice::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'gst_number' => $request->gst_number,
            'default_per_km_rate' => $request->default_per_km_rate,
            'preferred_districts' => $request->preferred_districts,
            'location_id' => $request->location_id,
        ]);

        return redirect()->route('tp_offices.index')->with('success', 'Transport office added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TpOffice $tp_office)
    {
        $states = DB::table('distance_pincodes')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->get();

        return view('transport.tp_offices.edit', [
            'office' => $tp_office,
            'states' => $states,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TpOffice $tp_office)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'default_per_km_rate' => 'nullable|numeric|min:0',
            'preferred_districts' => 'nullable|array',
            'location_id' => 'required|exists:distance_pincodes,id',
        ]);

        $tp_office->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'gst_number' => $request->gst_number,
            'default_per_km_rate' => $request->default_per_km_rate,
            'preferred_districts' => $request->preferred_districts,
            'location_id' => $request->location_id,
        ]);

        return redirect()->route('tp_offices.index')->with('success', 'Transport office updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TpOffice $tp_office)
    {
        $tp_office->delete();

        return redirect()->route('tp_offices.index')->with('success', 'Transport office deleted successfully!');
    }

    /**
     * Get districts by state (AJAX)
     */
    public function getDistricts($state)
    {
        $districts = DB::table('distance_pincodes')
            ->where('state', $state)
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->get();

        return response()->json($districts);
    }

    /**
     * Get places by state and district (AJAX)
     */
    public function getPlaces($state, $district)
    {
        $places = DB::table('distance_pincodes')
            ->where('state', $state)
            ->where('district', $district)
            ->select('id', 'place', 'pincode')
            ->orderBy('place')
            ->get();

        return response()->json($places);
    }
}
