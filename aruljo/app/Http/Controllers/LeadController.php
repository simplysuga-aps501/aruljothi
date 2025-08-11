<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Tags\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{

    //Display function for create lead
    public function create()
    {
        $users = \App\Models\User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        $tags = \Spatie\Tags\Tag::all();
        $platforms = $this->allowedPlatforms();
        return view('leads.create', compact('users', 'tags', 'platforms'));
    }

    // Store function for create lead
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

        // Default assignment to creator if none given
        $validated['assigned_to'] = $validated['assigned_to'] ?? Auth::user()->name;

        $user = Auth::user()->name;
        $timestamp = now()->format('d M Y, h:i A');
        $remarkText = null;

         // Assignment remark logic
            if (!empty($validated['assigned_to']) && $validated['assigned_to'] !== $user) {
                // Assigned to someone else
                $remarkText = "assigned to {$validated['assigned_to']}";
            } else {
                // Created (self-assigned or no reassignment)
                $remarkText = "created the lead";
            }

        // Add manual remark if given
        if ($request->filled('current_remark')) {
            if ($remarkText) {
                // Merge both into one remark
                $remarkText .= " — {$request->current_remark}";
            } else {
                $remarkText = $request->current_remark;
            }
        }

        // Create new lead
        $lead = new Lead(collect($validated)->except(['current_remark', 'tags'])->toArray());

        // Add remark if available
        if ($remarkText) {
            $lead->remarks = "{$user} ({$timestamp}): {$remarkText}";
        }

        $lead->save();

        // Attach tags if any
        if ($request->has('tags')) {
            $validTags = Tag::whereIn('name->en', $request->tags)->get();
            $lead->syncTags($validTags);
        }

        return redirect()->route('leads.index')->with('success', 'Lead added successfully!');
    }

   // Display function for index
  public function index(Request $request)
  {
      $tab = $request->get('tab', 'active');
      $userName = Auth::user()->name;
      $today = now()->toDateString();

      if ($tab === 'all') {
          $leads = Lead::with('tags')
              ->orderBy('created_at', 'desc')
              ->get();
      } else {
          $query = Lead::with('tags')
              ->whereNotIn('status', ['Cancelled', 'Completed'])
              // Exclude leads with follow-up dates in the future
              ->where(function ($q) use ($today) {
                  $q->whereNull('follow_up_date')
                    ->orWhere('follow_up_date', '<=', $today);
              });

          if ($tab === 'my') {
              $query->where('assigned_to', $userName);
          }

          // Priority sorting:
          // 1. Urgent (regardless of follow-up date)
          // 2. Today follow-up (not urgent)
          // 3. Past follow-up date
          // 4. No follow-up date
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

          // Debug logs
          \Log::info("SQL (placeholders): " . $query->toSql());
          \Log::info("Bindings:", $query->getBindings());
          \Log::info("SQL (with bindings): " . vsprintf(
              str_replace('?', "'%s'", $query->toSql()),
              $query->getBindings()
          ));

          $leads = $query->get();

          // Log each lead’s priority and key info
          foreach ($leads as $lead) {
              \Log::info("Lead Order Debug", [
                  'id' => $lead->id,
                  'follow_up_date' => $lead->follow_up_date,
                  'sort_priority' => $lead->sort_priority,
                  'tags' => $lead->tags->pluck('name')->toArray(),
                  'created_at' => $lead->created_at
              ]);
          }
      }

      $users = User::whereDoesntHave('roles', function ($query) {
          $query->where('name', 'admin');
      })->get();

      $currentUser = $userName;
      $statuses = $this->allowedStatuses();
      $platforms = $this->allowedPlatforms();
      $allTags = \Spatie\Tags\Tag::pluck('name');

      return view('leads.index', compact('leads', 'users', 'currentUser', 'tab', 'statuses', 'platforms', 'allTags'));
  }



    //Display for edit lead
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
            'current_remark' => '', // input field only
            'past_remarks' => explode('~|~', $lead->remarks ?? ''),
            'tags' => $lead->tags->pluck('name')->toArray(), // if using Spatie Tags
        ]);
    }

    //Store the edit
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

        // Save old assignment before updating
        $oldAssignedTo = $lead->assigned_to;

        // Fill updated values except remarks/tags
        $lead->fill(collect($validated)->except(['current_remark', 'tags'])->toArray());

        $user = Auth::user()->name;
        $timestamp = now()->format('d M Y, h:i A');
        $remarkText = null;

        // Check if reassignment happened
        if ($oldAssignedTo !== $lead->assigned_to && $lead->assigned_to) {
            $remarkText = "reassigned to {$lead->assigned_to}";
        }

        // Add manual remark if given
        if ($request->filled('current_remark')) {
            if ($remarkText) {
                // Merge both into one remark
                $remarkText .= " — {$request->current_remark}";
            } else {
                $remarkText = $request->current_remark;
            }
        }

        // Append remark if we have one
        if ($remarkText) {
            $fullRemark = "{$user} ({$timestamp}): {$remarkText}";
            $lead->remarks = $lead->remarks
                ? $fullRemark . "~|~" . $lead->remarks
                : $fullRemark;
        }

        $lead->save();

        // Attach tags if any
        $validTags = collect($request->input('tags', []))
            ->filter()
            ->map(function ($tagName) {
                return Tag::where('name->en', $tagName)->first();
            })
            ->filter()
            ->values();

        $lead->syncTags($validTags);

        $tab = $request->query('tab', 'active');
        return redirect()->route('leads.index', ['tab' => $tab])
                         ->with('success', 'Lead updated successfully.');
    }



    //Delete the lead
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead moved to trash.');
    }

    //Display Logs for lead
    public function showAudits($id)
    {
        $lead = Lead::findOrFail($id);
        $audits = $lead->audits()->latest()->get();

        return view('leads.audits', compact('lead', 'audits'));
    }

    //Centralized list of allowed statuses
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

    //Centralized list of allowed platforms
    private function allowedPlatforms(): array
    {
        return [
            'Justdial',
            'Indiamart',
            'Others',
        ];
    }
}
