<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QuoteCalculatorService
{
    public function calculateByCapacity(array $products, float $distance): array
    {
        // ========================== INPUT VALIDATION ==========================
        if (empty($products) || $distance <= 0) {
            return ['error' => 'Products and distance are required.'];
        }

        $bodyType = $distance <= 150 ? 'Open' : 'Truck';

        // ========================== FETCH TRUCK TYPES ==========================
        $trucks = DB::table('tp_truck_types')
            ->select('id', 'name', 'capacity_kg', 'rate_per_km')
            ->orderBy('capacity_kg')
            ->get()
            ->filter(fn($truck) => !in_array($truck->name, ['14 Wheel', '16 Wheel', 'Trailor']))
            ->values();

        if ($trucks->isEmpty()) {
            return ['error' => 'No truck types found.'];
        }

        // ========================== PREPARE PRODUCT DATA ==========================
        $product_rows = [];
        $net_product_weight = 0;
        foreach ($products as $p) {
            $qty = (int) ($p['qty'] ?? 0);
            $weight = (float) ($p['weight'] ?? 0);
            $price = (float) ($p['price'] ?? 0);
            $sku = $p['sku'] ?? 'Unknown';

            $net_product_weight += ($weight * $qty);

            $product_rows[] = [
                'id' => $p['id'] ?? null,
                'sku' => $sku,
                'qty' => $qty,
                'weight' => $weight,
                'total_weight' => $qty * $weight,
                'price' => $price,
            ];
        }


        // ========================== TRUCK CAPACITY OVERVIEW ==========================
        //Just for our own visualization of calculations - html
        $truckCapacityTable = '<h5>Truck Capacity Overview</h5>';
        $truckCapacityTable .= '<table class="table table-bordered table-sm w-100"><thead class="table-light"><tr>';
        $truckCapacityTable .= '<th>Product</th><th>Qty Needed</th>';
        foreach ($trucks as $truck) {
            $truckCapacityTable .= "<th>{$truck->name}</th>";
        }
        $truckCapacityTable .= '</tr></thead><tbody>';

        foreach ($product_rows as $prod) {
            $truckCapacityTable .= "<tr><td>{$prod['sku']}</td><td>{$prod['qty']}</td>";
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
        $truckCapacityTable .= '</tbody></table>';


        // ========================== OPTIMIZED TRUCK ALLOCATION ==========================
        $remainingProducts = $product_rows;
        $truckAllocations = [];
        $remainingProductWeight = $net_product_weight;

        //Loop through remaining products array till no products left
        while (array_sum(array_column($remainingProducts, 'qty')) > 0) {

            // 1️⃣ SELECT SUITABLE TRUCK
            $selectedTruck = $trucks->last(); // default to largest
            foreach ($trucks as $truck) {
                if ($truck->capacity_kg >= $remainingProductWeight) {
                    $selectedTruck = $truck;
                    break;
                }
            }

            $truckProducts = [];
            $truckTotalWeightFilled = 0;
            $sameProductLoaded = false;

            // 2️⃣ STAGE 1: TRY TO FILL FULL TRUCK WITH SAME PRODUCT
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

                // If full truck can be filled with this single product
                if ($product['qty'] >= $maxUnits) {
                    $truckProducts[] = [
                        'sku' => $product['sku'],
                        'allocated_qty' => $maxUnits,
                        'total_weight' => $practicalWeight,
                        'max_allowed_qty' => $maxUnits,
                    ];

                    $product['qty'] -= $maxUnits;
                    $remainingProductWeight -= $practicalWeight;
                    $truckTotalWeightFilled = $practicalWeight;
                    $sameProductLoaded = true;
                    break; // one full truck done
                }
            }
            unset($product);

            // 3️⃣ STAGE 2: MIX PRODUCTS WITH VOLUME CHECK
            if (!$sameProductLoaded) {
                $truckVolumeUsed = 0; // tracks fraction of truck volume used

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

                    // Maximum units that can fit by weight
                    $maxFitByWeight = floor($weightLeft / $weightPerUnit);

                    // Maximum units that can fit by volume (fraction of max_units per product)
                    $availableVolumeFraction = 1 - $truckVolumeUsed;
                    $maxFitByVolume = floor($availableVolumeFraction * $maxUnits);

                    // Allocate units considering product qty, weight limit, and volume limit
                    $allocatable = min($product['qty'], $maxFitByWeight, $maxFitByVolume);

                    if ($allocatable <= 0) continue;

                    $allocatedWeight = $allocatable * $weightPerUnit;

                    $truckProducts[] = [
                        'sku' => $product['sku'],
                        'allocated_qty' => $allocatable,
                        'total_weight' => $allocatedWeight,
                        'max_allowed_qty' => $maxUnits,
                    ];

                    // Update product and truck trackers
                    $product['qty'] -= $allocatable;
                    $remainingProductWeight -= $allocatedWeight;
                    $truckTotalWeightFilled += $allocatedWeight;
                    $truckVolumeUsed += $allocatable / $maxUnits;

                    // Stop if truck is full by either weight or volume
                    if ($truckVolumeUsed >= 1 || $truckTotalWeightFilled >= $selectedTruck->capacity_kg) {
                        break;
                    }
                }
                unset($product);
            }


            // 4️⃣ SAVE ALLOCATION
            $truckAllocations[] = [
                'truck_name' => $selectedTruck->name,
                'body_type' => $bodyType,
                'products' => $truckProducts,
                'truck_total_weight' => round($truckTotalWeightFilled, 2),
                'capacity_kg' => $selectedTruck->capacity_kg,
                'load_type' => $sameProductLoaded ? 'Single Product' : 'Mixed Load',
            ];
        }

        // ========================== BUILD OUTPUT TABLES ==========================
        $truckTable = '<h5>Truck Allocation</h5><table class="table table-bordered table-sm w-100">
            <thead class="table-light">
                <tr>
                    <th>Sl #</th>
                    <th>Truck Type</th>
                    <th>Body Type</th>
                    <th>Product</th>
                    <th>Qty Allocated</th>
                    <th>Total Weight</th>
                    <th>Max Allowed Qty</th>
                </tr>
            </thead><tbody>';

        $sl = 1;
        foreach ($truckAllocations as $truck) {
            $productsCount = count($truck['products']);
            $firstRow = true;

            foreach ($truck['products'] as $prod) {
                $truckTable .= '<tr>';
                if ($firstRow) {
                    $truckTable .= "<td rowspan='{$productsCount}'>{$sl}</td>";
                    $truckTable .= "<td rowspan='{$productsCount}'>{$truck['truck_name']}</td>";
                    $truckTable .= "<td rowspan='{$productsCount}'>{$truck['body_type']}</td>";
                    $firstRow = false;
                }

                $truckTable .= "<td>{$prod['sku']}</td>";
                $truckTable .= "<td>{$prod['allocated_qty']}</td>";
                $truckTable .= "<td>{$prod['total_weight']} kg</td>";
                $truckTable .= "<td>{$prod['max_allowed_qty']}</td>";
                $truckTable .= '</tr>';
            }

            $truckTable .= "<tr class='table-info'>
                <td colspan='5'><strong>Total in this truck</strong></td>
                <td><strong>{$truck['truck_total_weight']} kg</strong></td>
                <td></td>
            </tr>";
            $sl++;
        }
        $truckTable .= '</tbody></table>';

        // ========================== PRICE TABLE ==========================
        $totalProduct = $totalGST = $netTotal = 0;
        $priceTable = '<h5>Price Details</h5><table class="table table-bordered table-sm w-100">
            <thead class="table-light"><tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Rate/unit (₹)</th>
                <th>Total Price (₹)</th>
            </tr></thead><tbody>';

        foreach ($product_rows as $row) {
            $productTotal = $row['qty'] * $row['price'];
            $gst = $productTotal * 0.18;
            $total = $productTotal + $gst;

            $totalProduct += $productTotal;
            $totalGST += $gst;
            $netTotal += $total;

            $priceTable .= "<tr>
                <td>{$row['sku']}</td>
                <td>{$row['qty']}</td>
                <td>" . number_format($row['price'], 2) . "</td>
                <td>" . number_format($productTotal, 2) . "</td>
            </tr>";
        }

        $priceTable .= "</tbody>
            <tfoot>
                <tr><th colspan='3' class='text-end'>Total (Products):</th><th>" . number_format($totalProduct, 2) . " ₹</th></tr>
                <tr><th colspan='3' class='text-end'>GST (18%):</th><th>" . number_format($totalGST, 2) . " ₹</th></tr>
                <tr class='table-success'><th colspan='3' class='text-end'>Net Total:</th><th>" . number_format($netTotal, 2) . " ₹</th></tr>
            </tfoot></table>";

        // ========================== FINAL OUTPUT ==========================
        return [
            'total_cost' => number_format($netTotal, 2),
            'details_html' => $truckCapacityTable . '<br>' . $truckTable . '<br>' . $priceTable,
            'products' => $product_rows,
        ];
    }
}
