<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QuoteCalculatorService
{
    public function calculate(array $products, float $distance): array
    {
        if (empty($products) || $distance <= 0) {
            return [
                'error' => 'Products and distance are required.'
            ];
        }

        // Fetch truck types
        $trucks = DB::table('tp_truck_types')
            ->select('name', 'capacity_kg', 'rate_per_km')
            ->orderBy('capacity_kg')
            ->get();

        if ($trucks->isEmpty()) {
            return [
                'error' => 'No truck types found.'
            ];
        }

        $total_weight = 0;
        $product_rows = [];

        // Product details
        foreach ($products as $product) {
            $qty = (int) ($product['qty'] ?? 0);
            $weight = (float) ($product['weight'] ?? 0);
            $sku = $product['sku'] ?? 'Unknown';
            $product_weight_total = $qty * $weight;
            $total_weight += $product_weight_total;

            $product_rows[] = [
                'sku' => $sku,
                'qty' => $qty,
                'weight' => $weight,
                'total' => $product_weight_total
            ];
        }

        // Select truck
        $selected_truck = null;
        $num_trucks = 0;
        foreach ($trucks as $truck) {
            $capacity = (float) $truck->capacity_kg;
            if ($capacity >= $total_weight) {
                $selected_truck = $truck;
                $num_trucks = 1;
                break;
            }
        }

        if (!$selected_truck) {
            $selected_truck = $trucks->last();
            $capacity = (float) $selected_truck->capacity_kg;
            $num_trucks = (int) ceil($total_weight / $capacity);
        }

        // Distance multiplier
        $minKm = DB::table('tp_min_km_multipliers')->orderBy('min_km')->get();
        $multiplier = 1.0;
        foreach ($minKm as $m) {
            $min = (float) $m->min_km;
            $max = $m->max_km ?? PHP_FLOAT_MAX;
            if ($distance >= $min && $distance < $max) {
                $multiplier = (float) $m->multiplier;
                break;
            }
        }

        $rate_per_km = (float) $selected_truck->rate_per_km;
        $total_cost = $rate_per_km * $distance * $multiplier * $num_trucks;

        // Build HTML table for modal
        $productTableHtml = '<table class="table table-bordered table-sm">';
        $productTableHtml .= '<thead><tr><th>Product</th><th>Quantity</th><th>Weight/unit (kg)</th><th>Total Weight (kg)</th></tr></thead><tbody>';
        foreach ($product_rows as $row) {
            $productTableHtml .= "<tr>
                <td>{$row['sku']}</td>
                <td>{$row['qty']}</td>
                <td>{$row['weight']}</td>
                <td>{$row['total']}</td>
            </tr>";
        }
        $productTableHtml .= "<tr>
            <td><strong>Total</strong></td>
            <td></td>
            <td></td>
            <td><strong>{$total_weight}</strong></td>
        </tr>";
        $productTableHtml .= '</tbody></table>';

        $summaryTableHtml = '<table class="table table-bordered table-sm">';
        $summaryTableHtml .= '<thead><tr><th>Truck Type</th><th>Capacity (kg)</th><th>Trucks Required</th><th>Distance (km)</th><th>Multiplier</th><th>Rate/km (₹)</th><th>Total Cost (₹)</th></tr></thead><tbody>';
        $summaryTableHtml .= "<tr>
            <td>{$selected_truck->name}</td>
            <td>{$selected_truck->capacity_kg}</td>
            <td>{$num_trucks}</td>
            <td>{$distance}</td>
            <td>{$multiplier}</td>
            <td>{$rate_per_km}</td>
            <td>" . number_format($total_cost, 2) . "</td>
        </tr>";
        $summaryTableHtml .= '</tbody></table>';

        $htmlDetails = $productTableHtml . '<br>' . $summaryTableHtml;

        // Plain text for WhatsApp (monospaced for readability)
        $textDetails = "📦 Products:\n";
        $textDetails .= "Product | Qty | Wt/unit | Total Wt\n";
        foreach ($product_rows as $row) {
            $textDetails .= "{$row['sku']} | {$row['qty']} | {$row['weight']} | {$row['total']}\n";
        }
        $textDetails .= "TOTAL WEIGHT: {$total_weight}\n\n";
        $textDetails .= "🚚 Truck & Cost Summary:\n";
        $textDetails .= "Truck | Capacity | Trucks | Distance | Multiplier | Rate/km | Total\n";
        $textDetails .= "{$selected_truck->name} | {$selected_truck->capacity_kg} | {$num_trucks} | {$distance} | {$multiplier} | {$rate_per_km} | " . number_format($total_cost, 2) . "\n";

        return [
            'truck_type' => $selected_truck->name,
            'num_trucks' => $num_trucks,
            'total_cost' => number_format($total_cost, 2),
            'details_html' => $htmlDetails, // modal
            'details_text' => $textDetails  // WhatsApp
        ];
    }
}
