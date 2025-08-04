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
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active');
        $userName = Auth::user()->name;

        if ($tab === 'all') {
            $leads = Lead::orderBy('created_at', 'desc')->get();
        } elseif ($tab === 'my') {
            $leads = Lead::where('assigned_to', $userName)
                         ->orderBy('created_at', 'desc')
                         ->get();
        } else {
            $leads = Lead::whereNotIn('status', ['Cancelled', 'Completed'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        }

        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $currentUser = $userName;

        // ✅ Add this line
        $statuses = $this->allowedStatuses();

        // ✅ Also include it here
        return view('leads.index', compact('leads', 'users', 'currentUser', 'tab', 'statuses'));
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
                         ->orderBy('created_at', 'desc')
                         ->get();

            $users = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })->pluck('name', 'id');

            $currentUser = Auth::user()->name;

            return view('leads.index', compact('leads', 'users', 'currentUser'));
    }
    public function quickEdit(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
            'current_remark' => 'nullable|string|max:500',
        ]);

        $lead = Lead::findOrFail($id);

        if ($request->filled('current_remark')) {
            $user = Auth::user()->name;
            $timestamp = now()->format('d M Y, h:i A');
            $formattedRemark = "{$user} ({$timestamp}): {$request->current_remark}";

            $existingRemarks = trim($lead->remarks ?? '');
            $lead->remarks = $existingRemarks
                        ? $formattedRemark . '~|~' . $existingRemarks
                        : $formattedRemark;
        }

        $lead->status = $validated['status'];
        $lead->assigned_to = $validated['assigned_to'];
        $lead->save();

        return response()->json(['success' => true, 'message' => 'Lead updated successfully!']);
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
            'follow_up_date' => 'nullable|date',
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
            'current_remark' => 'nullable|string|max:500',
        ]);

        $lead = Lead::findOrFail($id);

        // Append the current remark to the remarks history (if given)
        if ($request->filled('current_remark')) {
            $user = Auth::user()->name;
            $timestamp = now()->format('d M Y, h:i A'); // eg. 30 Jul 2025, 03:10 PM
            $formattedRemark = "{$user} ({$timestamp}): {$request->current_remark}";

            $existingRemarks = trim($lead->remarks ?? '');
            $lead->remarks = $existingRemarks
                        ? $formattedRemark . '~|~' . $existingRemarks
                        : $formattedRemark;
        }

        // Update other validated fields (excluding current_remark)
        $lead->fill(collect($validated)->except('current_remark')->toArray());
        $lead->save();

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

    //Quick Edit
    public function show($id)
    {
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
    }

}
