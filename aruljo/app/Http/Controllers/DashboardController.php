<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Define 12 months from start of year
        $start = now()->startOfYear();
        $end = now()->endOfYear();
        $months = CarbonPeriod::create($start, '1 month', $end);

        // Monthly grouped initialization
        $monthlyGrouped = [];
        foreach ($months as $month) {
            $label = $month->format('M');
            $monthlyGrouped[$label] = ['Created' => 0, 'Completed' => 0, 'Cancelled' => 0];
        }

        // Created leads monthly
        $createdRaw = Lead::selectRaw("MONTH(lead_date) as month, YEAR(lead_date) as year, COUNT(*) as total")
            ->whereBetween('lead_date', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->groupBy(DB::raw("YEAR(lead_date), MONTH(lead_date)"))->get();

        foreach ($createdRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Created'] = $row->total;
        }

        // Completed leads monthly
        $completedRaw = Lead::where('status', 'Completed')
            ->whereBetween('updated_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("MONTH(updated_at) as month, YEAR(updated_at) as year, COUNT(*) as total")
            ->groupBy(DB::raw("YEAR(updated_at), MONTH(updated_at)"))->get();

        foreach ($completedRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Completed'] = $row->total;
        }

        // Cancelled leads monthly
        $cancelledRaw = Lead::where('status', 'Cancelled')
            ->whereBetween('updated_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("MONTH(updated_at) as month, YEAR(updated_at) as year, COUNT(*) as total")
            ->groupBy(DB::raw("YEAR(updated_at), MONTH(updated_at)"))->get();

        foreach ($cancelledRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Cancelled'] = $row->total;
        }

        // Prepare arrays for monthly chart
        $monthlyLabels = array_keys($monthlyGrouped);
        $monthlyCreated = array_column($monthlyGrouped, 'Created');
        $monthlyCompleted = array_column($monthlyGrouped, 'Completed');
        $monthlyCancelled = array_column($monthlyGrouped, 'Cancelled');

        // Platform-wise created leads initialization
        $platformLabels = $monthlyLabels;
        $platforms = config('platforms.list');
        $platformData = [];
        foreach ($platforms as $platform) {
            foreach ($platformLabels as $label) {
                $platformData[$platform][$label] = 0;
            }
        }

        $createdPlatform = Lead::selectRaw("MONTH(lead_date) as month, YEAR(lead_date) as year, platform, COUNT(*) as total")
            ->whereBetween('lead_date', [now()->startOfYear(), now()->endOfYear()])
            ->groupBy(DB::raw("YEAR(lead_date), MONTH(lead_date), platform"))
            ->orderByRaw("YEAR(lead_date), MONTH(lead_date)")->get();

        foreach ($createdPlatform as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $platformData[$row->platform][$label] += $row->total;
        }

        // --- ACTIVE LEADS BY PERSON (PER STATUS) ---
        // Get distinct assigned users (excluding Cancelled & Completed)
        $users = Lead::whereNotIn('status', ['Cancelled', 'Completed'])
            ->whereNotNull('assigned_to')
            ->distinct()
            ->pluck('assigned_to')
            ->toArray();

        // Define statuses to include
        $statuses = ['New Lead', 'Lead Followup', 'Quotation', 'PO'];

        // Initialize arrays for each status with user keys and 0 counts
        $userLabels = $users;
        $userNewLeads = array_fill_keys($users, 0);
        $userFollowUps = array_fill_keys($users, 0);
        $userQuotations = array_fill_keys($users, 0);
        $userPOStatus = array_fill_keys($users, 0);

        // Query counts grouped by assigned_to and status (only for active statuses)
        $statusCounts = Lead::selectRaw('assigned_to, status, COUNT(*) as total')
            ->whereIn('status', $statuses)
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to', 'status')
            ->get();

        foreach ($statusCounts as $row) {
            $assigned = $row->assigned_to;
            $status = $row->status;
            $count = $row->total;

            if (in_array($assigned, $users)) {
                if ($status === 'New Lead') {
                    $userNewLeads[$assigned] = $count;
                } elseif ($status === 'Lead Followup') {
                    $userFollowUps[$assigned] = $count;
                } elseif ($status === 'Quotation') {
                    $userQuotations[$assigned] = $count;
                } elseif ($status === 'PO') {
                    $userPOStatus[$assigned] = $count;
                }
            }
        }

      // --- LEAD STATUS COUNTS FOR LOGGED-IN USER ---
      $userName = Auth::user()->name;

      // Define statuses in the desired order
      $statuses = [
          'New Lead' => 0,
          'Lead Followup' => 0,
          'Quotation' => 0,
          'PO' => 0,
          'Completed' => 0,
          'Cancelled' => 0,
      ];

      // Get counts from DB
      $counts = Lead::select('status', DB::raw('COUNT(*) as total'))
          ->where('assigned_to', $userName)
          ->groupBy('status')
          ->pluck('total', 'status')
          ->toArray();

      // Merge with default zero array (preserves order)
      $leadStatusCounts = array_merge($statuses, $counts);
       return view('dashboard', compact(
           'monthlyLabels',
           'monthlyCreated',
           'monthlyCompleted',
           'monthlyCancelled',
           'platformLabels',
           'platformData',
           'userLabels',
           'userNewLeads',
           'userFollowUps',
           'userQuotations',
           'userPOStatus',
           'leadStatusCounts' // pass to view
       ));
    }
}
