<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Tags\Tag;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display create lead form.
     */
    public function create()
    {
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $tags = Tag::all();
        $platforms = config('platforms.list');
        // Remove "Justdial" only in create
        $platforms = array_filter($platforms, fn($p) => $p !== 'Justdial');
        return view('leads.create', compact('users', 'tags', 'platforms'));
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request)
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
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
            'current_remark' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $validated['assigned_to'] = $validated['assigned_to'] ?? Auth::user()->name;

        $user = Auth::user()->name;
        $timestamp = now()->format('d M Y, h:i A');

        if (!empty($validated['assigned_to']) && $validated['assigned_to'] !== $user) {
            $remarkText = "assigned to {$validated['assigned_to']}";
        } else {
            $remarkText = "created the lead";
        }

        if ($request->filled('current_remark')) {
            $remarkText .= " — {$request->current_remark}";
        }

        $lead = new Lead(collect($validated)->except(['current_remark', 'tags'])->toArray());
        $lead->remarks = "{$user} ({$timestamp}): {$remarkText}";
        $lead->save();

        if ($request->has('tags')) {
            $validTags = Tag::whereIn('name->en', $request->tags)->get();
            $lead->syncTags($validTags);
        }

        return redirect()->route('leads.index')->with('success', 'Lead added successfully!');
    }

    /**
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'active');
        $today = now()->toDateString();

        if ($user->getRoleNames()->count() === 1 && $user->hasRole('euser')) {
            $tab = 'my';
        }

        if ($tab === 'all')
        {

            $leads = Lead::with('tags')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $query = Lead::with('tags');

            if ($tab === 'my') {
                $query->where('assigned_to', $user->name)
                      ->whereNotIn('status', ['Cancelled', 'Completed'])
                      ->where(function ($q) use ($today) {
                          $q->whereNull('follow_up_date')
                            ->orWhere('follow_up_date', '<=', $today);
                      });
            }

            if ($tab === 'active') {
                $query->whereNotIn('status', ['Cancelled', 'Completed'])
                      ->where(function ($q) use ($today) {
                          $q->whereNull('follow_up_date')
                            ->orWhere('follow_up_date', '<=', $today);
                      });
            }

            $query->select('leads.*')
                  ->selectRaw("
                      CASE
                          WHEN EXISTS (
                              SELECT 1
                              FROM taggables tg
                              JOIN tags t ON t.id = tg.tag_id
                              WHERE tg.taggable_id = leads.id
                                AND tg.taggable_type = ?
                                AND JSON_UNQUOTE(JSON_EXTRACT(t.name, '$.en')) = 'Urgent'
                          ) THEN 1
                          WHEN DATE(follow_up_date) = ? THEN 2
                          WHEN follow_up_date IS NOT NULL AND DATE(follow_up_date) < ? THEN 3
                          WHEN follow_up_date IS NULL THEN 4
                          ELSE 5
                      END as sort_priority
                  ", [Lead::class, $today, $today])
                  ->orderBy('sort_priority')
                  ->orderBy('created_at', 'desc');

            $leads = $query->get();
        }

        foreach ($leads as $lead) {
            \Log::info("Lead Order Debug", [
                'id' => $lead->id,
                'follow_up_date' => $lead->follow_up_date,
                'sort_priority' => $lead->sort_priority,
                'tags' => $lead->tags->pluck('name')->toArray(),
                'created_at' => $lead->created_at,
            ]);
        }

        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $currentUser = $user->name;
        $statuses = $this->allowedStatuses();
        $platforms = config('platforms.list');
        $allTags = Tag::pluck('name');

        return view('leads.index', compact('leads', 'users', 'currentUser', 'tab', 'statuses', 'platforms', 'allTags'));
    }

    /**
     * Show lead for editing.
     */
    public function edit(Lead $lead)
    {
        return response()->json([
            'id' => $lead->id,
            'buyer_name' => $lead->buyer_name,
            'buyer_contact' => $lead->buyer_contact,
            'lead_date' => $lead->lead_date,
            'platform' => $lead->platform,
            'platform_keyword' => $lead->platform_keyword,
            'product_detail' => $lead->product_detail,
            'buyer_location' => $lead->buyer_location,
            'delivery_location' => $lead->delivery_location,
            'expected_delivery_date' => $lead->expected_delivery_date,
            'follow_up_date' => $lead->follow_up_date,
            'status' => $lead->status,
            'assigned_to' => $lead->assigned_to,
            'current_remark' => '',
            'past_remarks' => explode('~|~', $lead->remarks ?? ''),
            'tags' => $lead->tags->pluck('name')->toArray(),
        ]);
    }

    /**
     * Update lead details.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'platform' => 'required|string',
            'lead_date' => 'required|date',
            'buyer_name' => 'required|string',
            'buyer_location' => 'nullable|string',
            'buyer_contact' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'platform_keyword' => 'nullable|string',
            'product_detail' => 'nullable|string',
            'delivery_location' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'assigned_to' => 'nullable|string',
            'current_remark' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $oldAssignedTo = $lead->assigned_to;
        $lead->fill(collect($validated)->except(['current_remark', 'tags'])->toArray());

        $user = Auth::user()->name;
        $timestamp = now()->format('d M Y, h:i A');
        $remarkText = null;

        if ($oldAssignedTo !== $lead->assigned_to && $lead->assigned_to) {
            $remarkText = "reassigned to {$lead->assigned_to}";
        }

        if ($request->filled('current_remark')) {
            $remarkText = $remarkText
                ? "{$remarkText} — {$request->current_remark}"
                : $request->current_remark;
        }

        if ($remarkText) {
            $fullRemark = "{$user} ({$timestamp}): {$remarkText}";
            $lead->remarks = $lead->remarks
                ? $fullRemark . "~|~" . $lead->remarks
                : $fullRemark;
        }

        $lead->save();

        $validTags = collect($request->input('tags', []))
            ->filter()
            ->map(fn($tagName) => Tag::where('name->en', $tagName)->first())
            ->filter()
            ->values();

        $lead->syncTags($validTags);

        $tab = $request->query('tab', 'active');

        return redirect()->route('leads.index', ['tab' => $tab])
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Delete a lead.
     */
    public function destroy($id)
    {
        Lead::findOrFail($id)->delete();

        return redirect()->route('leads.index')->with('success', 'Lead moved to trash.');
    }

    /**
     * Show audit logs for a lead.
     */
    public function showAudits($id)
    {
        $lead = Lead::findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('euser') && $lead->assigned_to !== $user->name) {
            abort(403, 'Unauthorized access to this audit log.');
        }

        $audits = $lead->audits()->latest()->get();

        return view('leads.audits', compact('lead', 'audits'));
    }

    /**
     * Allowed statuses.
     */
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
