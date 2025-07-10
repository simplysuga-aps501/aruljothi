<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        // Define the 12-month period (Jan first)
        $start = now()->startOfYear(); // January of current year
        $end = now()->endOfYear();     // December of current year

        $months = CarbonPeriod::create($start, '1 month', $end);


        // === Initialize Monthly Grouped Labels ===
        $monthlyGrouped = [];
        foreach ($months as $month) {
            $label = $month->format('M');
            $monthlyGrouped[$label] = [
                'Created' => 0,
                'Completed' => 0,
                'Cancelled' => 0,
            ];
        }

        // === CREATED LEADS ===
        $createdRaw = Lead::selectRaw("
                MONTH(lead_date) as month,
                YEAR(lead_date) as year,
                COUNT(*) as total
            ")
            ->whereBetween('lead_date', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->groupBy(DB::raw("YEAR(lead_date), MONTH(lead_date)"))
            ->get();

        foreach ($createdRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Created'] = $row->total;
        }

        // === COMPLETED LEADS ===
        $completedRaw = Lead::where('status', 'Completed')
            ->whereBetween('updated_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("
                MONTH(updated_at) as month,
                YEAR(updated_at) as year,
                COUNT(*) as total
            ")
            ->groupBy(DB::raw("YEAR(updated_at), MONTH(updated_at)"))
            ->get();

        foreach ($completedRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Completed'] = $row->total;
        }

        // === CANCELLED LEADS ===
        $cancelledRaw = Lead::where('status', 'Cancelled')
            ->whereBetween('updated_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("
                MONTH(updated_at) as month,
                YEAR(updated_at) as year,
                COUNT(*) as total
            ")
            ->groupBy(DB::raw("YEAR(updated_at), MONTH(updated_at)"))
            ->get();

        foreach ($cancelledRaw as $row) {
            $label = Carbon::create($row->year, $row->month)->format('M');
            $monthlyGrouped[$label]['Cancelled'] = $row->total;
        }

        // === PREPARE ARRAYS FOR CHART ===
        $monthlyLabels = array_keys($monthlyGrouped);
        $monthlyCreated = array_column($monthlyGrouped, 'Created');
        $monthlyCompleted = array_column($monthlyGrouped, 'Completed');
        $monthlyCancelled = array_column($monthlyGrouped, 'Cancelled');

      // ========== PLATFORM-WISE CREATED LEADS ==========

      $platformLabels = $monthlyLabels; // Jan to Dec
      $platforms = ['Indiamart', 'Justdial', 'Others'];

      // Initialize data with 0 for each platform and month
      $platformData = [];
      foreach ($platforms as $platform) {
          foreach ($platformLabels as $label) {
              $platformData[$platform][$label] = 0;
          }
      }

      $createdPlatform = Lead::selectRaw("
              MONTH(lead_date) as month,
              YEAR(lead_date) as year,
              platform,
              COUNT(*) as total
          ")
          ->whereBetween('lead_date', [now()->startOfYear(), now()->endOfYear()])
          ->groupBy(DB::raw("YEAR(lead_date), MONTH(lead_date), platform"))
          ->orderByRaw("YEAR(lead_date), MONTH(lead_date)")
          ->get();

      foreach ($createdPlatform as $row) {
          $label = Carbon::create($row->year, $row->month)->format('M');
          $platform = $row->platform;
          $platformData[$platform][$label] += $row->total;
      }

        return view('dashboard', compact(
            'monthlyLabels',
            'monthlyCreated',
            'monthlyCompleted',
            'monthlyCancelled',
            'platformLabels',
            'platformData'
        ));
    }
}
