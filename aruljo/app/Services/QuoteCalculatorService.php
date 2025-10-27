<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteCalculatorService
{
    /**
     * Calculates optimized truck allocation, transport, and pricing based on products and distance.
     *
     * @param  array  $products   Array of products (each with id, sku, qty, weight, price)
     * @param  float  $distance   Transport distance in kilometers
     * @return array              Total cost + formatted HTML tables for detailed breakdown
     */
    public function calculateByCapacity(array $products, float $distance): array
    {
        /* ----------------------------------------------------------------------
         |  1️⃣ INPUT VALIDATION
         ---------------------------------------------------------------------- */
        if (empty($products) || $distance <= 0) {
            return ['error' => 'Products and distance are required.'];
        }

        $bodyType = $distance <= 150 ? 'Open' : 'Truck';

        /* ----------------------------------------------------------------------
         |  2️⃣ FETCH TRUCK TYPES
         ---------------------------------------------------------------------- */
        $trucks = DB::table('tp_truck_types')
            ->select('id', 'name', 'capacity_kg', 'rate_per_km')
            ->orderBy('capacity_kg')
            ->get()
            ->filter(fn($truck) => !in_array($truck->name, ['14 Wheel', '16 Wheel', 'Trailor']))
            ->values();

        if ($trucks->isEmpty()) {
            return ['error' => 'No truck types found.'];
        }

        /* ----------------------------------------------------------------------
         |  3️⃣ PREPARE PRODUCT DATA
         ---------------------------------------------------------------------- */
        $product_rows = [];
        $net_product_weight = 0;

        foreach ($products as $p) {
            $qty    = (int) ($p['qty'] ?? 0);
            $weight = (float) ($p['weight'] ?? 0);
            $price  = (float) ($p['price'] ?? 0);
            $sku    = $p['sku'] ?? 'Unknown';

            $net_product_weight += ($weight * $qty);

            $product_rows[] = [
                'id'           => $p['id'] ?? null,
                'sku'          => $sku,
                'qty'          => $qty,
                'weight'       => $weight,
                'total_weight' => $qty * $weight,
                'price'        => $price,
            ];
        }

        // Sort products descending by unit weight
        usort($product_rows, fn($a, $b) => $b['weight'] <=> $a['weight']);

        /* ----------------------------------------------------------------------
         |  4️⃣ TRUCK CAPACITY OVERVIEW (HTML TABLE)
         ----------------------------------------------------------------------
        $truckCapacityTable = '
            <h5 class="mt-3">Truck Capacity Overview</h5>
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Wt</th>';

        foreach ($trucks as $truck) {
            $truckCapacityTable .= "<th>{$truck->name}</th>";
        }

        $truckCapacityTable .= '
                    </tr>
                </thead>
                <tbody>';

        foreach ($product_rows as $prod) {
            $truckCapacityTable .= "
                <tr>
                    <td>{$prod['sku']}</td>
                    <td>{$prod['qty']}</td>
                    <td>{$prod['weight']}</td>";

            foreach ($trucks as $truck) {
                $maxUnits = DB::table('tp_truck_capacities')
                    ->where('truck_type_id', $truck->id)
                    ->where('product_id', $prod['id'])
                    ->where('body_type', strtolower($bodyType) === 'open' ? 'open_body_truck' : 'truck')
                    ->value('max_units') ?? 0;

                $truckCapacityTable .= "<td>{$maxUnits}</td>";
            }

            $truckCapacityTable .= '</tr>';
        }

        $truckCapacityTable .= '</tbody></table>';*/

        /* ----------------------------------------------------------------------
         |  5️⃣ OPTIMIZED TRUCK ALLOCATION
         ---------------------------------------------------------------------- */
        $remainingProducts = $product_rows;
        $truckAllocations = [];
        $remainingProductWeight = $net_product_weight;

        while (array_sum(array_column($remainingProducts, 'qty')) > 0) {

            // 🛻 Select Suitable Truck
            $selectedTruck = $trucks->last();
            foreach ($trucks as $truck) {
                if ($truck->capacity_kg >= $remainingProductWeight) {
                    $selectedTruck = $truck;
                    break;
                }
            }

            $truckProducts = [];
            $truckTotalWeightFilled = 0;
            $sameProductLoaded = false;

            // 🚚 Stage 1: Try to fill with single product
            foreach ($remainingProducts as &$product) {
                if ($product['qty'] <= 0) continue;

                $truckCapacity = DB::table('tp_truck_capacities')
                    ->where('truck_type_id', $selectedTruck->id)
                    ->where('product_id', $product['id'])
                    ->where('body_type', strtolower($bodyType) === 'open' ? 'open_body_truck' : 'truck')
                    ->first();

                if (!$truckCapacity) continue;

                $maxUnits = $truckCapacity->max_units ?? 0;
                if ($maxUnits <= 0) continue;

                $productLimitWeight = $maxUnits * $product['weight'];
                $practicalWeight = min($productLimitWeight, $selectedTruck->capacity_kg);

                // Fill full truck with single product
                if ($product['qty'] >= $maxUnits) {
                    $truckProducts[] = [
                        'sku'            => $product['sku'],
                        'allocated_qty'  => $maxUnits,
                        'total_weight'   => $practicalWeight,
                        'max_allowed_qty'=> $maxUnits,
                    ];

                    $product['qty'] -= $maxUnits;
                    $remainingProductWeight -= $practicalWeight;
                    $truckTotalWeightFilled = $practicalWeight;
                    $sameProductLoaded = true;
                    break;
                }
            }
            unset($product);

            // 🧱 Stage 2: Mixed load
            if (!$sameProductLoaded) {
                $truckVolumeUsed = 0;

                foreach ($remainingProducts as &$product) {
                    if ($product['qty'] <= 0) continue;

                    $truckCapacity = DB::table('tp_truck_capacities')
                        ->where('truck_type_id', $selectedTruck->id)
                        ->where('product_id', $product['id'])
                        ->where('body_type', strtolower($bodyType) === 'open' ? 'open_body_truck' : 'truck')
                        ->first();

                    if (!$truckCapacity) continue;

                    $maxUnits = $truckCapacity->max_units ?? 0;
                    if ($maxUnits <= 0) continue;

                    $weightPerUnit = $product['weight'];
                    $weightLeft = $selectedTruck->capacity_kg - $truckTotalWeightFilled;

                    $maxFitByWeight = floor($weightLeft / $weightPerUnit);
                    $availableVolumeFraction = 1 - $truckVolumeUsed;
                    $maxFitByVolume = floor($availableVolumeFraction * $maxUnits);

                    $allocatable = min($product['qty'], $maxFitByWeight, $maxFitByVolume);
                    if ($allocatable <= 0) continue;

                    $allocatedWeight = $allocatable * $weightPerUnit;

                    $truckProducts[] = [
                        'sku'            => $product['sku'],
                        'allocated_qty'  => $allocatable,
                        'total_weight'   => $allocatedWeight,
                        'max_allowed_qty'=> $maxUnits,
                    ];

                    $product['qty'] -= $allocatable;
                    $remainingProductWeight -= $allocatedWeight;
                    $truckTotalWeightFilled += $allocatedWeight;
                    $truckVolumeUsed += $allocatable / $maxUnits;

                    if ($truckVolumeUsed >= 1 || $truckTotalWeightFilled >= $selectedTruck->capacity_kg) {
                        break;
                    }
                }
                unset($product);
            }

            // ✅ Save truck allocation
            $truckAllocations[] = [
                'truck_name'         => $selectedTruck->name,
                'body_type'          => $bodyType,
                'rate_per_km'        => $selectedTruck->rate_per_km,
                'transport_cost'     => $selectedTruck->rate_per_km * $distance,
                'products'           => $truckProducts,
                'truck_total_weight' => round($truckTotalWeightFilled, 2),
                'capacity_kg'        => $selectedTruck->capacity_kg,
                'load_type'          => $sameProductLoaded ? 'Single Product' : 'Mixed Load',
            ];
        }

        /* ----------------------------------------------------------------------
         |  6️⃣ TRUCK ALLOCATION TABLE (HTML)
         ---------------------------------------------------------------------- */
        $truckTable = '
            <h5 class="mt-3">Truck Allocation</h5>
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <thead class="thead-light">
                    <tr>
                        <th>Sl #</th>
                        <th>Truck Type</th>
                        <th>Body Type</th>
                        <th>Product</th>
                        <th>Max Allowed Qty</th>
                        <th>Qty Allocated</th>
                        <th>Total Weight</th>
                    </tr>
                </thead>
                <tbody>';

        $sl = 1;
        foreach ($truckAllocations as $truck) {
            $productsCount = count($truck['products']);
            $firstRow = true;

            foreach ($truck['products'] as $prod) {
                $truckTable .= '<tr>';
                if ($firstRow) {
                    $truckTable .= "<td rowspan='{$productsCount}'>{$sl}</td>
                                    <td rowspan='{$productsCount}'>{$truck['truck_name']}</td>
                                    <td rowspan='{$productsCount}'>{$truck['body_type']}</td>";
                    $firstRow = false;
                }

                $truckTable .= "
                    <td>{$prod['sku']}</td>
                    <td>{$prod['max_allowed_qty']}</td>
                    <td>{$prod['allocated_qty']}</td>
                    <td>{$prod['total_weight']} kg</td>
                </tr>";
            }

            $truckTable .= "
                <tr>
                    <td colspan='6'><strong>Total in this truck</strong></td>
                    <td><strong>{$truck['truck_total_weight']} kg</strong></td>
                </tr>";
            $sl++;
        }
        $truckTable .= '</tbody></table>';

        /* ----------------------------------------------------------------------
         |  7️⃣ TRANSPORT COST TABLE (with Total Transport Cost only)
         ---------------------------------------------------------------------- */

        $totalTransportCost = 0;
        $sl = 1;

        $transportTable = '
            <h5 class="mt-3">Transport Cost Details</h5>
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <thead class="thead-light">
                    <tr>
                        <th>Sl #</th>
                        <th>Truck Type</th>
                        <th>Body Type</th>
                        <th>Rate/km</th>
                        <th>Distance</th>
                        <th>Transport Cost (₹)</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($truckAllocations as $truck) {
            $transportTable .= "
                <tr>
                    <td>{$sl}</td>
                    <td>{$truck['truck_name']}</td>
                    <td>{$truck['body_type']}</td>
                    <td>" . number_format($truck['rate_per_km'], 2) . "</td>
                    <td>" . number_format($distance, 2) . "</td>
                    <td>" . number_format($truck['transport_cost'], 2) . "</td>
                </tr>";

            $totalTransportCost += $truck['transport_cost'];
            $sl++;
        }

        // ✅ Add total row at the end (for Transport Cost only)
        $transportTable .= "
                </tbody>
                <tfoot>
                    <tr class='table-success'>
                        <th colspan='5' class='text-end'>Total Transport Cost:</th>
                        <th>₹" . number_format($totalTransportCost, 2) . "</th>
                    </tr>
                </tfoot>
            </table>";

        /* ----------------------------------------------------------------------
         |  8️⃣ PRICE TABLE (with Transport per Unit)
         ---------------------------------------------------------------------- */
        $totalProduct = $totalGST = $netTotal = 0;

        // 🧮 Calculate cost per kg for transport
        $totalTransportCost = array_sum(array_column($truckAllocations, 'transport_cost'));
        $totalTransportWeight = array_sum(array_column($truckAllocations, 'truck_total_weight'));
        $costPerKg = $totalTransportWeight > 0 ? ($totalTransportCost / $totalTransportWeight) : 0;

        $priceTable = '
            <h5 class="mt-3">Price Details (Including Transport)</h5>
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Rate/unit (₹)</th>
                        <th>Transport/unit (₹)</th>
                        <th>Total Price (₹)</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($product_rows as $row) {
            $transportPerUnit = $row['weight'] * $costPerKg;
            $finalRatePerUnit = $row['price'] + $transportPerUnit;
            $productTotal = $row['qty'] * $finalRatePerUnit;

            $gst = $productTotal * 0.18;
            $total = $productTotal + $gst;

            $totalProduct += $productTotal;
            $totalGST += $gst;
            $netTotal += $total;

            $priceTable .= "
                <tr>
                    <td>{$row['sku']}</td>
                    <td>{$row['qty']}</td>
                    <td>" . number_format($row['price'], 2) . "</td>
                    <td>" . number_format($transportPerUnit, 2) . "</td>
                    <td>" . number_format($productTotal, 2) . "</td>
                </tr>";
        }

        $priceTable .= "
                </tbody>
                <tfoot>
                    <tr><th colspan='4' class='text-end'>Subtotal (Products + Transport):</th><th>" . number_format($totalProduct, 2) . " ₹</th></tr>
                    <tr><th colspan='4' class='text-end'>GST (18%):</th><th>" . number_format($totalGST, 2) . " ₹</th></tr>
                    <tr class='table-success'><th colspan='4' class='text-end'>Net Total:</th><th>" . number_format($netTotal, 2) . " ₹</th></tr>
                </tfoot>
            </table>";


        /* ----------------------------------------------------------------------
         |  9️⃣ FINAL OUTPUT
         ---------------------------------------------------------------------- */
        return [
            'total_cost'   => number_format($netTotal, 2),
            'details_html' => $truckTable . '<br>' . $transportTable . '<br>' . $priceTable,
            'products'     => $product_rows,
        ];
    }
}
