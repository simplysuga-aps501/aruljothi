<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    // 📋 Return leads as JSON (API use)
    public function index()
    {
        $leads = Lead::orderBy('created_at', 'desc')->get();
        return response()->json($leads);
    }

    // ✅ Store lead from form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string',
            'lead_date' => 'required|date',
            'buyer_name' => 'required|string',
            'buyer_location' => 'nullable|string',
            'buyer_contact' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'platform_keyword' => 'nullable|string',
        ]);

        $validated['assigned_to'] = Auth::user()->name;

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead added successfully!');
    }

    // 📝 Show all leads in blade view
    public function showAll()
    {
         $leads = Lead::whereNotIn('status', ['Cancelled', 'Completed'])
                         ->orderBy('created_at', 'asc')
                         ->get();

            $users = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })->pluck('name', 'id');

            $currentUser = Auth::user()->name;

            return view('leads.index', compact('leads', 'users', 'currentUser'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->status = $request->status;
        $lead->assigned_to = $request->assigned_to;
        $lead->save();

        return redirect()->back()->with('success', 'Lead status updated.');
    }

    public function updateFull(Request $request, $id)
    {
        $validated = $request->validate([
            'platform' => 'required|string',
            'lead_date' => 'required|date',
            'buyer_name' => 'required|string',
            'buyer_location' => 'nullable|string',
            'buyer_contact' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'platform_keyword' => 'nullable|string',
            'product_detail' => 'nullable|string',
            'delivery_location' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully!');
    }

    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->pluck('name', 'id');
        $currentUser = Auth::user()->name;

        return view('leads.edit', compact('lead', 'users', 'currentUser'));
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead moved to trash.');
    }

    public function showAudits($id)
    {
        $lead = Lead::findOrFail($id);
        $audits = $lead->audits()->latest()->get();

        return view('leads.audits', compact('lead', 'audits'));
    }

    // ✅ Centralized list of allowed statuses
    private function allowedStatuses(): array
    {
        return [
            'New Lead',
            'Lead Followup',
            'Quotation',
            'PO',
            'Cancelled',
            'Completed',
        ];
    }
}
