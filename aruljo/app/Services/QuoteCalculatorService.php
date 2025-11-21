<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteCalculatorService
{
   public function getQuoteReferenceData(
       array $products = [],
       ?float $distance = null,
       ?int $locationId = null,
       bool $includeDraft = false
   ): array {
       // Existing queries...
       $availableTrucks = DB::table('tp_truck_types')
           ->select('id','name','capacity_kg','rate_per_km')
           ->orderBy('capacity_kg')
           ->get();

       $productIds = collect($products)->pluck('id')->filter()->unique();

       $availableProducts = DB::table('products')
           ->when($productIds->isNotEmpty(), fn($q) => $q->whereIn('id',$productIds))
           ->select('id','sku','name','weight_kg','quote_price as price')
           ->get()
           ->map(function ($p) use ($products) {
               $match = collect($products)->firstWhere('id', $p->id);
               $p->requested_qty = $match['qty'] ?? 0; // attach requested quantity
               return $p;
           });

       $truckCapacities = DB::table('tp_truck_capacities')
           ->select('truck_type_id','product_id','body_type','max_units')
           ->get();

       $districtRates = collect();
       if ($locationId) {
           $districtRates = DB::table('tp_district_rates')
               ->select('location_id','truck_type_id','rate')
               ->where('location_id',$locationId)
               ->whereNull('deleted_at')
               ->get();
       }

       $kmMultipliers = DB::table('tp_min_km_multipliers')
           ->select('min_km','max_km','multiplier')
           ->orderBy('min_km')
           ->get();

       $response = [
           'available_trucks'   => $availableTrucks,
           'available_products' => $availableProducts,
           'truck_capacities'   => $truckCapacities,
           'district_rates'     => $districtRates,
           'km_multipliers'     => $kmMultipliers,
           'distance_km'        => $distance,
           'location_id'        => $locationId,
       ];

       if ($includeDraft && !empty($products) && $distance > 0) {
           $calc = $this->calculateByCapacity($products, $distance, $locationId);

           if (!empty($calc['allocations'] ?? [])) {
               // map to simplified draft_allocations
               $truckMap   = $availableTrucks->pluck('id', 'name');
               $skuToId    = $availableProducts->pluck('id', 'sku'); // map SKU → product_id

               $response['draft_allocations'] = collect($calc['allocations'])->map(function ($truck) use ($truckMap, $skuToId, $products) {
                   return [
                       'truck_id'  => $truckMap[$truck['truck_name']] ?? null,
                       'body_type' => $truck['body_type'] ?? 'Truck',
                       'items'     => collect($truck['products'] ?? [])->map(function ($p) use ($skuToId, $products) {
                           $sku = $p['sku'] ?? null;
                           $productId = $skuToId[$sku] ?? null;

                           // match requested qty by SKU (not id)
                           $req = collect($products)->firstWhere('sku', $sku);

                           return [
                               'product_id'    => $productId,
                               'qty'           => $p['allocated_qty'] ?? 0,
                               'requested_qty' => $req['qty'] ?? 0, // ✅ now guaranteed to work
                           ];
                       })->filter(fn($i) => $i['product_id'])->values(),
                   ];
               })->filter(fn($t) => $t['truck_id'])->values();
           }
       }
       return $response;
   }


   public function calculateByCapacity(array $products, float $distance, ?string $locationId = null): array
       {
           /* ----------------------------------------------------------------------
            |  1️⃣ INPUT VALIDATION
            ---------------------------------------------------------------------- */
           if (empty($products) || $distance <= 0) {
               return ['error' => 'Products and distance are required.'];
           }
           Log::info("\n\n================== Starting Quote Calculation ==================\n");
           /* ----------------------------------------------------------------------
           |  2️⃣ DETERMINE STATE FROM LOCATION ID
           ---------------------------------------------------------------------- */
          $state = null;
          if ($locationId) {
              $state = \App\Models\DistancePincode::where('id', $locationId)->value('state');
          }
           // 🛻 Determine body type based on distance, state, product parameters, and truck type rules
           $bodyType = 'Truck'; // Default

           // Rule 1: <150 → Open
           if ($distance < 150) {
               $bodyType = 'open_body_truck';
               Log::info('<150');
           }

           // Rule 2: If state = KL and <200 → Open
           if (strtoupper(trim($state)) === 'KL' && $distance < 200) {
               $bodyType = 'open_body_truck';
               Log::info('KL <200');
           }

           // Rule 3: If state = KL, >200, and any product length ≥ 2.5 → Open
           if (strtoupper(trim($state)) === 'KL' && $distance > 200) {
               foreach ($products as $p) {
                   $params = collect($p['parameterValues'] ?? ($p['parameters'] ?? []))->pluck('value', 'parameter');
                   $length = (float) ($params['Length'] ?? 0);
                   if ($length >= 2.5) {
                       $bodyType = 'open_body_truck';
                       Log::info('KL >200');
                       break;
                   }
               }
           }

           /*// Rule 4: If any product diameter > 900 → Open
           foreach ($products as $p) {
               $params = collect($p['parameterValues'] ?? ($p['parameters'] ?? []))->pluck('value', 'parameter');
               $diameter = (float) ($params['Diameter'] ?? 0);
               if ($diameter > 900) {
                   $bodyType = 'open_body_truck';
                   Log::info('>900');
                   break;
               }
           }*/

           // Rule 5: Trailor trucks → always open (handled later per-truck)
           Log::info('🚚 Body Type Rule Applied', ['bodyType' => $bodyType, 'distance' => $distance, 'state' => $state]);

           /* ----------------------------------------------------------------------
            |  2️⃣ FETCH TRUCK TYPES
            ---------------------------------------------------------------------- */

           // 🚛 Check if any product requires a Trailor (Distance>250 , Length ≥ 2.5m and Diameter > 300mm)
           $requiresTrailor = false;

           if ($distance >= 200) {
               $requiresTrailor = collect($products)->contains(function ($p) {
                   if (empty($p['parameterValues']) && empty($p['parameters'])) {
                       return false;
                   }

                   $params = collect($p['parameterValues'] ?? $p['parameters'])->pluck('value', 'parameter');

                   $length = (float) ($params['Length'] ?? 0);
                   $diameter = (float) ($params['Diameter'] ?? 0);

                   return $length >= 2.5 && $diameter > 300;
               });

               Log::info('🚛 Requires Trailor? ' . ($requiresTrailor ? 'Yes' : 'No'));
           }

           // 🚚 Fetch truck types dynamically
           // 🚚 Fetch truck types dynamically
           $trucks = DB::table('tp_truck_types')
               ->select('id', 'name', 'capacity_kg', 'rate_per_km')
               ->orderBy('capacity_kg')
               ->get()
               ->filter(function ($truck) use ($requiresTrailor, $distance) {
                   // Exclude big trucks always
                   if (in_array($truck->name, ['14 Wheel', '16 Wheel'])) {
                       return false;
                   }

                   // ❌ Exclude Mini Door if distance > 150 km
                   if ($distance > 150 && stripos($truck->name, 'Mini Door') !== false) {
                       Log::info("Skipping Mini Door for long distance: {$distance} km");
                       return false;
                   }

                   // Include Trailor only if required
                   if ($truck->name === 'Trailor' && !$requiresTrailor) {
                       return false;
                   }

                   return true;
               })
               ->values();


           if ($trucks->isEmpty()) {
               return ['error' => 'No suitable truck types found.'];
           }

           Log::info('✅ Available Truck Types: ' . $trucks->pluck('name')->join(', ') . "\n");
           /* ----------------------------------------------------------------------
            |  3️⃣ PREPARE PRODUCT DATA
            ---------------------------------------------------------------------- */
           $product_rows = [];
           $net_product_weight = 0;

           foreach ($products as $p) {
               $qty = (int) ($p['qty'] ?? 0);
               $weight = (float) ($p['weight'] ?? 0);
               $price = (float) ($p['price'] ?? 0);
               $sku = $p['sku'] ?? 'Unknown';

               $net_product_weight += $weight * $qty;

               $product_rows[] = [
                   'id' => $p['id'] ?? null,
                   'sku' => $sku,
                   'qty' => $qty,
                   'weight' => $weight,
                   'total_weight' => $qty * $weight,
                   'price' => $price,
               ];
           }

           // Sort products descending by unit weight
           usort($product_rows, fn($a, $b) => $b['weight'] <=> $a['weight']);

           /* ----------------------------------------------------------------------
            |  5️⃣ OPTIMIZED TRUCK ALLOCATION
            ---------------------------------------------------------------------- */
           $remainingProducts = $product_rows;
           $truckAllocations = [];
           $remainingProductWeight = $net_product_weight;

           while (array_sum(array_column($remainingProducts, 'qty')) > 0) {
               // 🛻 Select the smallest truck that can carry *all* remaining products
               $selectedTruck = null;
               foreach ($trucks as $truck)
               {
                   $canCarryAll = true;
                   $totalPossibleWeight = 0;

                   $bodyTypeCurrent = strtolower($truck->name) === 'trailor'
                       ? 'open_body_truck'
                       : $bodyType;

                   $truckVolumeLeft = 1.0; // 100% volume available

                   foreach ($remainingProducts as $product) {

                       $truckCapacity = DB::table('tp_truck_capacities')
                           ->where('truck_type_id', $truck->id)
                           ->where('product_id', $product['id'])
                           ->where('body_type', $bodyTypeCurrent)
                           ->first();

                       // ❌ Cannot carry this SKU at all
                       if (!$truckCapacity || $truckCapacity->max_units <= 0) {
                           $canCarryAll = false;
                           Log::info("❌ Truck {$truck->name} REJECTED — Not suitable for {$product['sku']}");
                           break;
                       }

                       // ❌ Truck cannot carry required qty (VOLUME FAIL)
                       if ($product['qty'] > $truckCapacity->max_units) {
                           $canCarryAll = false;
                           Log::info("❌ Truck {$truck->name} REJECTED — Cannot carry required quantity");
                           break;
                       }

                       // Calculate how much fraction of volume this product consumes
                       $volumeNeeded = $product['qty'] / $truckCapacity->max_units;

                       Log::info("Product {$product['sku']}: Needs Volume = $volumeNeeded, Volume Left = $truckVolumeLeft");

                       // ❌ Not enough volume
                       if ($volumeNeeded > $truckVolumeLeft) {
                           $canCarryAll = false;
                           Log::info("❌ Truck {$truck->name} REJECTED — volume mismatch");
                           break;
                       }

                       // Deduct used volume
                       $truckVolumeLeft -= $volumeNeeded;

                       // Weight capacity estimate
                       $totalPossibleWeight += $truckCapacity->max_units * $product['weight'];
                   }

                   // After checking all products…
                   if (!$canCarryAll) {
                       Log::info("❌ Truck {$truck->name} REJECTED — volume/sku mismatch");
                       continue;
                   }

                   // Weight check
                   if ($truck->capacity_kg >= $remainingProductWeight &&
                       $totalPossibleWeight >= $remainingProductWeight) {

                       $selectedTruck = $truck;
                       Log::info("✅ SELECTED TRUCK: {$selectedTruck->name}");
                       break;
                   }
               }

               // 🚨 If no truck can carry any product, stop to prevent infinite loop
               if (!$selectedTruck)
               {
                   foreach ($trucks->reverse() as $truckOption)
                   {
                       $canCarrySomething = false;
                       $bodyTypeCurrent = strtolower($truckOption->name) === 'trailor' ? 'open_body_truck' : $bodyType;
                       foreach ($remainingProducts as $product)
                       {
                           $truckCapacity = DB::table('tp_truck_capacities')
                                                   ->where('truck_type_id', $truck->id)
                                                   ->where('product_id', $product['id'])
                                                   ->where('body_type',$bodyTypeCurrent)
                                                   ->first();
                           if ($truckCapacity && $truckCapacity->max_units > 0) {
                               $canCarrySomething = true;
                               break;
                           }
                       }
                       if ($canCarrySomething) {
                           $selectedTruck = $truckOption;
                           Log::info('Selected  : ' . $selectedTruck->name);
                           break;
                       }
                   }

                   // 🚨 If no truck can carry any product at all, safely exit loop
                   if (!$selectedTruck) {
                       Log::error('❌ No truck found that can carry any remaining products. Breaking allocation loop.');
                       break; // safely exit the while loop
                   }
               }

               $truckProducts = [];
               $truckTotalWeightFilled = 0;
               $sameProductLoaded = false;

               // 🚚 Stage 1: Try to fill with single product
               foreach ($remainingProducts as &$product) {
                   $truckCapacity = DB::table('tp_truck_capacities')
                       ->where('truck_type_id', $selectedTruck->id)
                       ->where('product_id', $product['id'])
                       ->where('body_type',$bodyTypeCurrent)
                       ->first();

                   if (!$truckCapacity) {
                       continue;
                   }

                   $maxUnits = $truckCapacity->max_units ?? 0;
                   if ($maxUnits <= 0) {
                       continue;
                   }
                   // Fill full truck with single product
                   if ($product['qty'] >= $maxUnits) {
                       $truckProducts[] = [
                           'sku' => $product['sku'],
                           'allocated_qty' => $maxUnits,
                           'total_weight' => $maxUnits * $product['weight'],
                           'max_allowed_qty' => $maxUnits,
                       ];

                       $product['qty'] -= $maxUnits;
                       $remainingProductWeight -= $maxUnits * $product['weight'];
                       $truckTotalWeightFilled = $maxUnits * $product['weight'];
                       $sameProductLoaded = true;
                       Log::info("Filled with product : SKU={$product['sku']}, Remaining weight ={$remainingProductWeight}");
                       break;
                   }
               }
               unset($product);

               // 🧱 Stage 2: Mixed load
               if (!$sameProductLoaded) {
                   $truckVolumeUsed = 0;

                   foreach ($remainingProducts as &$product) {
                       if ($product['qty'] <= 0) {
                           continue;
                       }
                       Log::info("Product: {$product['sku']} --- Qty: {$product['qty']}");
                       $truckCapacity = DB::table('tp_truck_capacities')
                           ->where('truck_type_id', $selectedTruck->id)
                           ->where('product_id', $product['id'])
                           ->where('body_type',$bodyTypeCurrent)
                           ->first();

                       if (!$truckCapacity) {
                           continue;
                       }

                       $maxUnits = $truckCapacity->max_units ?? 0;
                       if ($maxUnits <= 0) {
                           continue;
                       }

                       $weightPerUnit = $product['weight'];
                       $weightLeft = $selectedTruck->capacity_kg - $truckTotalWeightFilled;

                       $maxFitByWeight = floor($weightLeft / $weightPerUnit);
                       $availableVolumeFraction = 1 - $truckVolumeUsed;
                       $maxFitByVolume = floor($availableVolumeFraction * $maxUnits);

                       $allocatable = min($product['qty'], $maxFitByWeight, $maxFitByVolume);
                       if ($allocatable <= 0) {
                           continue;
                       }

                       $allocatedWeight = $allocatable * $weightPerUnit;

                       $truckProducts[] = [
                           'sku' => $product['sku'],
                           'allocated_qty' => $allocatable,
                           'total_weight' => $allocatedWeight,
                           'max_allowed_qty' => $maxUnits,
                       ];

                       $product['qty'] -= $allocatable;
                       $remainingProductWeight -= $allocatedWeight;
                       $truckTotalWeightFilled += $allocatedWeight;
                       $truckVolumeUsed += $allocatable / $maxUnits;
                       Log::info("Filled with product : SKU={$product['sku']},Qty Left ={$product['qty']}, Remaining weight ={$remainingProductWeight}");
                       if ($truckVolumeUsed >= 1 || $truckTotalWeightFilled >= $selectedTruck->capacity_kg) {
                           break;
                       }
                   }
                   unset($product);
               }

              // 🧮 Determine effective rate_per_km based on distance and multipliers
              $effectiveRatePerKm = $selectedTruck->rate_per_km;
              $fixedRate = false;

              // Always get multiplier from table, regardless of km
              $multiplier = DB::table('tp_min_km_multipliers')
                  ->where('min_km', '<=', $distance)
                  ->where(function ($query) use ($distance) {
                      $query->where('max_km', '>', $distance)->orWhereNull('max_km');
                  })
                  ->value('multiplier') ?? 1;

              // Apply multiplier if found
              $effectiveRatePerKm *= $multiplier;

              // Normalize body type string
              $bodyTypeCurrent = $bodyTypeCurrent === 'open_body_truck' ? 'Open' : 'Truck';

              // 🏷️ Default transport cost (per-km × distance)
              $transportCost = round($effectiveRatePerKm * $distance, 2);

              // 🚚 If distance >150 km AND truck type is “Truck” → check fixed rate table
              if ($distance > 150 ) {
                  $districtRate = DB::table('tp_district_rates')
                      ->where('location_id', $locationId)
                      ->where('truck_type_id', $selectedTruck->id)
                      ->whereNull('deleted_at')
                      ->value('rate');

                  if ($districtRate) {
                      $transportCost = (float) $districtRate;
                      $fixedRate = true;
                      $fixedRateMessage = null;
                      Log::info("Fixed district rate applied: location_id={$locationId}, truck_type_id={$selectedTruck->id}, rate={$districtRate}");
                  } else {
                      $fixedRateMessage = "⚠️ Fixed Rate Missing";
                      Log::warning("No fixed rate for location_id={$locationId}, truck_type_id={$selectedTruck->id}");
                  }
              }

              // ✅ Save truck allocation
              $truckAllocations[] = [
                  'truck_name' => $selectedTruck->name,
                  'body_type' => $bodyTypeCurrent,
                  'rate_per_km' => round($effectiveRatePerKm, 2),
                  'multiplier' => $multiplier,
                  'transport_cost' => $transportCost,
                  'products' => $truckProducts,
                  'truck_total_weight' => round($truckTotalWeightFilled, 2),
                  'capacity_kg' => $selectedTruck->capacity_kg,
                  'load_type' => $sameProductLoaded ? 'Single Product' : 'Mixed Load',
                  'fixed_rate' => $fixedRate, // 🆕 keep a flag for display
                  'fixed_rate_message' => $fixedRateMessage ?? '',
              ];


               // Remove products with qty 0 from remainingProducts
               $remainingProducts = array_filter($remainingProducts, fn($p) => $p['qty'] > 0);
               // Reindex array
               $remainingProducts = array_values($remainingProducts);
               $remainingProductWeight = array_sum(array_map(fn($p) => $p['qty'] * $p['weight'], $remainingProducts));
               Log::info("Truck Allocation Completed for ". $selectedTruck->name . "\n");
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
                           <th>Min Km Rate</th>
                           <th>Distance</th>
                           <th>Transport Cost (₹)</th>
                       </tr>
                   </thead>
                   <tbody>';

           foreach ($truckAllocations as $truck) {
               $rateDisplay = $truck['fixed_rate']
                   ? 'Fixed Rate'
                   : number_format($truck['rate_per_km'], 2);

               $multiplierDisplay = $truck['fixed_rate']
                   ? 'Fixed Rate'
                   : $truck['multiplier'];

               $rateCell = $rateDisplay;
               if (!empty($truck['fixed_rate_message'])) {
                   $rateCell .= "<br><small style='color:#e67e22;'>{$truck['fixed_rate_message']}</small>";
               }

               $transportTable .= "
                   <tr>
                       <td>{$sl}</td>
                       <td>{$truck['truck_name']}</td>
                       <td>{$truck['body_type']}</td>
                       <td>{$rateCell}</td>
                       <td>{$multiplierDisplay}</td>
                       <td>" . number_format($distance, 2) . "</td>
                       <td>" . number_format($truck['transport_cost'], 2) . "</td>
                   </tr>";

               $totalTransportCost += $truck['transport_cost'];
               $sl++;
           }



           // ✅ Add total row at the end (for Transport Cost only)
           $transportTable .=
               "
                   </tbody>
                   <tfoot>
                       <tr class='table-success'>
                           <th colspan='6' class='text-end'>Total Transport Cost:</th>
                           <th>₹" .
               number_format($totalTransportCost, 2) .
               "</th>
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
           $costPerKg = $totalTransportWeight > 0 ? $totalTransportCost / $totalTransportWeight : 0;

           $priceTable = '
               <h5 class="mt-3">Price Details (Including Transport)</h5>
               <table class="table table-bordered table-striped table-hover table-sm w-100" id="summaryTable">
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
               $transportPerUnit = round($row['weight'] * $costPerKg);
               $finalRatePerUnit = $row['price'] + $transportPerUnit;
               $productTotal = $row['qty'] * $finalRatePerUnit;

               $gst = $productTotal * 0.18;
               $total = $productTotal + $gst;

               $totalProduct += $productTotal;
               $totalGST += $gst;
               $netTotal += $total;

               $priceTable .=
                   "
                   <tr>
                       <td>{$row['sku']}</td>
                       <td>{$row['qty']}</td>
                       <td>" .
                   number_format($row['price']) .
                   "</td>
                       <td>" .
                   number_format($transportPerUnit) .
                   "</td>
                       <td>" .
                   number_format($productTotal, 2) .
                   "</td>
                   </tr>";
           }

           $priceTable .=
               "
                   </tbody>
                   <tfoot>
                       <tr><th colspan='4' class='text-end'>Subtotal (Products + Transport):</th><th>" .
               number_format($totalProduct, 2) .
               " ₹</th></tr>
                       <tr><th colspan='4' class='text-end'>GST (18%):</th><th>" .
               number_format($totalGST, 2) .
               " ₹</th></tr>
                       <tr class='table-success'><th colspan='4' class='text-end'>Net Total:</th><th>" .
               number_format($netTotal, 2) .
               " ₹</th></tr>
                   </tfoot>
               </table>";

           /* ----------------------------------------------------------------------
            |  9️⃣ FINAL OUTPUT
            ---------------------------------------------------------------------- */
           return [
               'total_cost' => number_format($netTotal, 2),
               'details_html' => $truckTable . '<br>' . $transportTable . '<br>' . $priceTable,
               'products' => $product_rows,
               'allocations'     => $truckAllocations,
           ];
       }


}
