<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TpDistrictRate;
use App\Models\Transport\TpOffice;
use App\Models\DistancePincode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transport\TruckType;


class TpDistrictRateController extends Controller
{
    public function index()
        {
            $rates = TpDistrictRate::with(['location', 'office', 'truckType'])->latest()->get();

            // Get all distinct states for dropdown
            $states = DistancePincode::select('state')->distinct()->pluck('state');

            // Get offices for optional office dropdown
            $offices = TpOffice::orderBy('name')->get();

            // Get all truck types
            $truckTypes = TruckType::orderBy('name')->get();

            return view('transport.rates.index', compact('rates', 'states', 'offices', 'truckTypes'));
        }

        public function create()
        {
            $offices = TpOffice::orderBy('name')->get();
            $truckTypes = TruckType::orderBy('name')->get();
            return view('transport.rates.create', compact('offices', 'truckTypes'));
        }

        public function store(Request $request)
        {
            $request->validate([
                'location_id'   => 'required|array|min:1',
                'location_id.*' => 'exists:distance_pincodes,id',
                'truck_type_id' => 'required|exists:tp_truck_types,id',
                'rate'          => 'required|numeric|min:0',
                'office_id'     => 'nullable|exists:tp_offices,id',
                'remarks'       => 'nullable|string|max:255',
            ]);

            $count = 0;

            foreach ($request->location_id as $locationId) {
                TpDistrictRate::updateOrCreate(
                    [
                        'location_id'   => $locationId,
                        'truck_type_id' => $request->truck_type_id,
                    ],
                    [
                        'office_id' => $request->office_id,
                        'rate'      => $request->rate,
                        'remarks'   => $request->remarks,
                    ]
                );
                $count++;
            }

            return redirect()
                ->route('rates.index')
                ->with('success', "{$count} rate(s) saved successfully.");
        }

        public function edit($id)
        {
            $rate = TpDistrictRate::with(['location', 'truckType'])->findOrFail($id);

            return response()->json([
                'id'          => $rate->id,
                'state'       => $rate->location->state ?? '',
                'district'    => $rate->location->district ?? '',
                'place'       => $rate->location->place ?? '',
                'location_id' => $rate->location_id,
                'office_id'   => $rate->office_id,
                'truck_type_id'   => $rate->truck_type_id,
                'truck_type_name' => $rate->truckType->name ?? '',
                'rate'        => $rate->rate,
                'remarks'     => $rate->remarks,
            ]);
        }

        public function update(Request $request, $id)
        {
            $rate = TpDistrictRate::findOrFail($id);

            $validated = $request->validate([
                'office_id'     => 'nullable|exists:tp_offices,id',
                'rate'          => 'required|numeric|min:0',
                'remarks'       => 'nullable|string|max:255',
            ]);

            $rate->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rate updated successfully.',
                    'data'    => $rate->load('office', 'location', 'truckType'),
                ]);
            }

            return redirect()
                ->route('rates.index')
                ->with('success', 'Rate updated successfully.');
        }

        public function destroy(TpDistrictRate $rate)
        {
            $rate->delete();

            return redirect()
                ->route('rates.index')
                ->with('success', 'Rate deleted successfully.');
        }

        public function getDistricts(Request $request)
        {
            $state = $request->input('state');
            if (!$state) {
                return response()->json([]);
            }

            $districts = DistancePincode::getDistrictsByState($state);
            return response()->json($districts);
        }

        public function getPlaces(Request $request)
        {
            $state = $request->input('state');
            $district = $request->input('district');
            if (!$state || !$district) {
                return response()->json([]);
            }

            $places = DistancePincode::getPlacesByStateAndDistrict($state, $district);
            return response()->json($places);
        }

        public function audits($id)
        {
            $rate = TpDistrictRate::findOrFail($id);
            $audits = $rate->audits()->latest()->get();

            return view('audit', [
                'entity' => $rate,
                'audits' => $audits,
                'entityType' => 'Rate',
            ]);
        }

}
