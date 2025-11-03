<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TpOffice;
use App\Models\DistancePincode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TpOfficeController extends Controller
{
    public function index()
    {
        $states = DistancePincode::select('state')->distinct()->orderBy('state')->get();
        $offices = TpOffice::orderByDesc('id')->get();

        foreach ($offices as $office) {
            $districts = $office->preferred_districts ?? [];
            if (is_string($districts)) {
                $districts = json_decode($districts, true) ?? [];
            }

            // Optional: prettier list for table view
            $office->preferred_districts_list = !empty($districts)
                ? collect($districts)->map(fn($d) => str_replace('::', ' - ', $d))->implode(', ')
                : '-';
        }

        return view('transport.offices.index', compact('offices', 'states'));
    }

    public function edit(TpOffice $office)
    {
        $office->load('location');
        return response()->json(['office' => $office]);
    }

    public function store(Request $request)
    {
        try {
            // Decode JSON string to array
            $request->merge([
                'preferred_districts' => json_decode($request->preferred_districts ?? '[]', true),
            ]);

            // ✅ Extract states automatically from districts
            $preferredStates = collect($request->preferred_districts)
                ->map(fn($item) => explode('::', $item)[0] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $request->merge(['preferred_states' => $preferredStates]);

            // ✅ Validate input
            $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'gst_number' => 'nullable|string|max:50',
                'preferred_states' => 'nullable|array',
                'preferred_districts' => 'nullable|array',
                'location_id' => 'required|exists:distance_pincodes,id',
            ]);

            // ✅ Attempt to create record
            $office = TpOffice::create([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'gst_number' => $request->gst_number,
                'preferred_states' => $request->preferred_states,
                'preferred_districts' => $request->preferred_districts,
                'location_id' => $request->location_id,
            ]);

            if ($office) {
                return redirect()
                    ->route('tp_offices.index')
                    ->with('success', 'Transport office added successfully!');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save the transport office. Please try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving transport office: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred while saving. Please contact admin.');
        }
    }


    public function update(Request $request, TpOffice $office)
    {
        try {
            $request->merge([
                'preferred_districts' => json_decode($request->preferred_districts ?? '[]', true),
            ]);

            // ✅ Extract states from preferred_districts
            $preferredStates = collect($request->preferred_districts)
                ->map(fn($item) => explode('::', $item)[0] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $request->merge(['preferred_states' => $preferredStates]);

            // ✅ Validate input
            $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'gst_number' => 'nullable|string|max:50',
                'preferred_states' => 'nullable|array',
                'preferred_districts' => 'nullable|array',
                'location_id' => 'required|exists:distance_pincodes,id',
            ]);

            $updated = $office->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'gst_number' => $request->gst_number,
                'preferred_states' => $request->preferred_states,
                'preferred_districts' => $request->preferred_districts,
                'location_id' => $request->location_id,
            ]);

            if ($updated) {
                return redirect()
                    ->route('tp_offices.index')
                    ->with('success', 'Transport office updated successfully!');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update the transport office. Please try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating transport office: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred while updating. Please contact admin.');
        }
    }


    public function destroy(TpOffice $office)
    {
        $office->delete();
        return redirect()->route('tp_offices.index')->with('success', 'Transport office deleted successfully!');
    }

    public function getDistricts(Request $request)
    {
        $states = (array) $request->input('states');

        $districts = DB::table('distance_pincodes')
            ->whereIn('state', $states)
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->get();

        return response()->json($districts);
    }

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
