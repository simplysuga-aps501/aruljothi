<script>
    // ================================================================
    // QUOTE CALCULATOR INITIALIZATION
    // ================================================================
    // Main initialization function for the quote calculator
    // Sets up event handlers and collects product data from the DOM
    function initQuoteCalculator(container = document) {
        const $container = $(container);
        // Collect all product data from pill elements in the DOM
        const collectProductData = () =>
            $container.find('.product-pills .pill').map(function () {
                return {
                    name: $(this).data('name'),
                    id: $(this).data('id'),
                    sku: $(this).data('sku'),
                    qty: parseFloat($(this).data('qty')),
                    price: $(this).data('price'),
                    weight: parseFloat($(this).data('weight'))
                };
            }).get();

        // Get the distance value from input field
        const getDistance = () => parseFloat($container.find('.distance_km').val());

        // Calculate quote button click handler
        $container.off('click', '#calculate_quote_btn').on('click', '#calculate_quote_btn', function () {
            const products = collectProductData();
            const distance = getDistance();
            const delivery_location_id = $container.find('.delivery_location_id, #quote_delivery_location_id').val();

            // Validate required inputs
            if (!distance || products.length === 0) return SwalCompact.alert('Oops!','Please enter products and distance.');

            const loader = $container.find('.loader, #loader');
            loader.show();

            // Fetch reference data from backend
            $.ajax({
                url: "{{ route('leads.reference-data',[],false) }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    products,
                    distance_km: distance,
                    delivery_location_id,
                    include_draft: true // Tells backend to run calculateByCapacity() internally
                },
                success: res => {
                    loader.hide();
                    window.availableProductsForQuote = res.available_products || [];

                    // Render the quote tables with fetched data
                    renderManualQuoteTables(res, $container.find('#calcDetailsBody'));
                    $container.find('#calcDetailsCollapse').collapse('show');
                },
                error: xhr => {
                    loader.hide();
                    console.error(xhr.status, xhr.responseText);
                    SwalCompact.alert('Oops!', 'Error fetching reference data. Please try again.');
                }
            });
        });

        // Toggle calculation details visibility
        $container.find('#toggle_calc_details').off('click').on('click', function () {
            $container.find('#calcDetailsCollapse').collapse('toggle');
        });
    }

    // ================================================================
    // LOAD VERSION DATA (EDIT MODE)
    // ================================================================
    // Loads existing quote version data from database for editing
    function loadVersionData(versionId) {
        const $modal = $('#editQuotationModal');
        const $body = $modal.find('#calcDetailsBody');
        const $collapse = $modal.find('#calcDetailsCollapse');
        const $loader = $modal.find('#quote_loader');

        if (!versionId) return;

        $loader.show();

        $.ajax({
            url: `/quotations/${versionId}/db-version-data`,
            type: 'GET',
            success: function (res) {
                res.is_edit_mode = true;
                res.prices = res.available_products;
                $loader.hide();
                console.log('✅ Loaded version data from DB:', res);

                // Render quote tables with existing data
                renderManualQuoteTables(res, $body);
                $collapse.collapse('show');

                // Update the estimated cost field
                $modal.find('#estimated_cost').val(res.net_total || '');
            },
            error: function (xhr) {
                $loader.hide();
                console.error('❌ Error loading version data:', xhr.responseText);
                SwalCompact.alert('Oops!', 'Failed to load quote version data.');
            }
        });
    }

    // ================================================================
    // RENDER MAIN QUOTE TABLES
    // ================================================================
    // Master function to render all quote calculation tables
    function renderManualQuoteTables(data, $target) {
        console.log('🔍 renderManualQuoteTables start', {
          oldDistrictRates: window.lastDistrictRates,
          incomingDistrictRates: data.district_rates
        });
        // Extract data arrays from response
        const trucks = data.available_trucks || [];
        const products = data.available_products || [];
        const capacities = data.truck_capacities || [];
        const districtRates = data.district_rates || [];
        const multipliers = data.km_multipliers || [];
        const distance = parseFloat(data.distance_km) || 0;
        const drafts = data.draft_allocations || [];

        // Store data globally for later reference
        window.availableProductsForQuote = data.available_products || [];
        window.lastAvailableTrucks = trucks;
        window.lastDistrictRates = districtRates;
        window.lastKmMultipliers = multipliers;
        window.lastDistance = distance;
        window.truck_capacities = capacities;

        // 1️⃣ Build base structure (empty tables)
        $target.html(buildTruckAllocationHTML(trucks, products));
        $target.append(buildTransportTableHTML(distance));
        $target.append(buildPriceTableHTML(products));

        // 2️⃣ Prefill trucks/products if editing (draft allocations exist)
        if (drafts.length) {
            const $tbody = $target.find('#truck_allocation_body');
            $tbody.empty();

            // Loop through each truck in the draft
            drafts.forEach((truckData, index) => {
                const rowIndex = index + 1;
                const truckHTML = buildTruckRowHTML(rowIndex, trucks, products);
                const subtotalHTML = `
                    <tr class="truck-subtotal table-light text-end">
                        <td colspan="6"><strong>Truck ${rowIndex} Total:</strong></td>
                        <td class="truck-weight text-end">0 kg</td>
                    </tr>`;

                $tbody.append(truckHTML + subtotalHTML);

                const $truckRow = $tbody.find('.allocation-row').last();

                // Set truck type and body type from draft data
                $truckRow.find('.truck-select').val(truckData.truck_id);
                $truckRow.find('.body-select').val(truckData.body_type || 'Truck');

                // Add each product item for this truck
                truckData.items.forEach((item, i) => {
                    const requested = parseFloat(item.requested_qty || 0);
                    const allocated = parseFloat(item.qty || 0); // Backend sends "qty"

                    if (i === 0) {
                        // First product goes in main truck row
                        $truckRow.find('.product-select').val(item.product_id);
                        handleProductChange($truckRow.find('.product-select'), capacities);
                        $truckRow.find('.qty-input').val(allocated).trigger('input');
                        $truckRow.find('.req-input').val(requested);
                    } else {
                        // Additional products go in extension rows
                        addProductRow($truckRow.find('.add-product-row'), products, capacities);
                        const $extRow = $truckRow.nextAll('.product-extension').last();
                        $extRow.find('.product-select').val(item.product_id);
                        handleProductChange($extRow.find('.product-select'), capacities);
                        $extRow.find('.qty-input').val(allocated).trigger('input');
                        $extRow.find('.req-input').val(requested);
                    }
                });
            });

            updateTotalWeight();

            // Add "Add Truck" button at the end
            $tbody.append(`
                <tr class="add-truck-control text-center">
                    <td colspan="7">
                        <button type="button" class="btn btn-sm btn-outline-primary add-truck-row">+ Add Truck</button>
                    </td>
                </tr>
            `);

            // Force recalculation of weights and transport/unit after draft fill
            $tbody.find('.product-select').each(function () {
                handleProductChange($(this), capacities);
            });
            computeTransportPerUnit();
        }

        // 3️⃣ Attach event handlers (needed for edit adjustments)
        setupTruckAllocationEvents($target, trucks, products, capacities, distance);
        setupTransportTable($target, trucks, districtRates, multipliers, distance);
        setupPriceTable($target);

        // 4️⃣ EDIT MODE LOGIC — display DB data only (no recalculation)
        if (data.is_edit_mode) {
            // Prefill transport costs exactly from DB
            const $tbody = $('#transportTable tbody');
            $tbody.empty();

            (data.transport || []).forEach((row, i) => {
                const rate = parseFloat(row.rate || 0).toFixed(2);
                const unloading = parseFloat(row.unloading || 0).toFixed(2);
                const cost = parseFloat(row.cost || 0).toFixed(2);
                const multiplier = row.multiplier || (data.distance_km < 150 ? '1' : '-');
                const distanceVal = parseFloat(row.distance || data.distance_km || 0).toFixed(1);

                $tbody.append(`
                    <tr>
                        <td>${i + 1}</td>
                        <td>${row.truck_name}</td>
                        <td>
                            <input type="number"
                                class="form-control form-control-sm ${data.distance_km < 150 ? 'rate-km' : 'fixed-rate'}"
                                value="${rate}" step="0.01">
                        </td>
                        <td>${multiplier}</td>
                        <td>
                            <input type="number"
                                class="form-control form-control-sm transport-distance"
                                value="${distanceVal}" min="1" step="0.1">
                        </td>
                        <td>
                            <input type="number"
                                class="form-control form-control-sm unloading-cost"
                                value="${unloading}" step="0.01">
                        </td>
                        <td class="transport-cost">₹${cost}</td>
                    </tr>
                `);
            });

            // Prefill price table directly from DB data
            $('#priceTable tbody tr').each(function () {
                const productId = parseInt($(this).data('product-id'));
                const db = (data.available_products || []).find(p => p.id === productId);
                if (!db) return;

                $(this).find('.qty').val(db.requested_qty);
                $(this).find('.rate-unit').val(db.price);
                $(this).find('.transport-unit').val(db.transport_unit || 0);
                $(this).find('.total-unit-price').text(db.total_unit_price || 0);
                $(this).find('.total').text('₹' + (db.total_price || 0));
            });

            // Prefill totals section with 2-decimal precision
            const subtotal = parseFloat(data.subtotal || 0);
            const gstRate = parseFloat(data.gst_rate || 18);
            const gst = parseFloat(((subtotal * gstRate) / 100).toFixed(2));
            const net = parseFloat((subtotal + gst).toFixed(2));

            $('#subtotal').text('₹' + subtotal.toFixed(2));
            $('#gst').text('₹' + gst.toFixed(2));
            $('#net_total').text('₹' + Math.round(net).toLocaleString('en-IN'));


            $('#truck_total_weight').text(
                data.total_weight ? parseFloat(data.total_weight).toFixed(0) + ' kg' : ''
            );
            $('#total_transport_cost').text(
                '₹' + parseFloat(data.total_transport || 0).toFixed(2)
            );


            // Don't recalculate anything in edit mode
            return;
        }

        // 5️⃣ Default (create mode) - perform initial calculations
        updateTotalWeight();
        refreshTransportTable(trucks, districtRates, multipliers, distance);
        updatePriceTotals();
        computeTransportPerUnit();
    }

    // ================================================================
    // 1️⃣ TRUCK ALLOCATION TABLE HTML BUILDER
    // ================================================================
    // Builds the HTML structure for the truck allocation table
    function buildTruckAllocationHTML(trucks, products) {
        return `
        <h5 class="mt-3">Truck Allocation</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm w-100" id="truckTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Truck Type</th>
                        <th>Body Type</th>
                        <th>Product</th>
                        <th>Max Allowed Qty</th>
                        <th>Allocated – Required</th>
                        <th>Total Weight</th>
                    </tr>
                </thead>
                <tbody id="truck_allocation_body">
                    ${buildTruckRowHTML(1, trucks, products)}
                    <tr class="truck-subtotal table-light text-end">
                        <td colspan="6"><strong>Truck 1 Total:</strong></td>
                        <td class="truck-weight text-end">0 kg</td>
                    </tr>
                    <tr class="add-truck-control text-center">
                        <td colspan="7">
                            <button type="button" class="btn btn-sm btn-outline-primary add-truck-row">+ Add Truck</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <th colspan="6" class="text-end">Total Weight:</th>
                        <th id="truck_total_weight">0 kg</th>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    }

    // Builds a single truck row with all necessary controls
    function buildTruckRowHTML(index, trucks, products) {
        // Get current distance value from input
        const distance = parseFloat($('#quote_distance_km').val()) || 0;
        // Decide default body type
        const defaultBody = distance < 150 ? 'Open' : 'Truck';

        return `
        <tr class="allocation-row">
            <td>${index}</td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    <select class="form-control form-control-sm truck-select">
                        <option value="">Select Truck</option>
                        ${trucks.map(t => `<option value="${t.id}">${t.name}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-truck-row">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </td>
            <td>
                <select class="form-control form-control-sm body-select">
                    <option value="Truck" ${defaultBody === 'Truck' ? 'selected' : ''}>Truck</option>
                    <option value="Open" ${defaultBody === 'Open' ? 'selected' : ''}>Open</option>
                </select>
            </td>
            <td>
                <div class="product-cell d-flex align-items-center gap-1">
                    <select class="form-control form-control-sm product-select">
                        <option value="">Select Product</option>
                        ${products.map(p => `<option value="${p.id}" data-weight="${p.weight_kg}">${p.sku}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-success add-product-row">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </td>
            <td class="max-qty text-center">0</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <input type="number" min="0" class="form-control form-control-sm qty-input w-50" value="0">
                    <input type="number" min="0" class="form-control form-control-sm req-input w-50 bg-light" value="0" readonly>
                </div>
            </td>
            <td class="total-weight text-center">0 kg</td>
        </tr>`;
    }


    // ================================================================
    // 2️⃣ TRUCK ALLOCATION EVENTS
    // ================================================================
    // Sets up all event handlers for the truck allocation table
    function setupTruckAllocationEvents($target, trucks, products, capacities, distance) {
        // Remove any old bindings first
        $target.off('click', '.add-truck-row');
        $target.off('click', '.add-product-row');
        $target.off('click', '.remove-product-row');
        $target.off('click', '.remove-truck-row');
        $target.off('change', '.truck-select, .body-select');
        $target.off('input', '.qty-input');

        // Add new truck row
        $target.on('click', '.add-truck-row', e => addTruckRow($target, trucks, products));

        // Add new product row to existing truck
        $target.on('click', '.add-product-row', e => addProductRow($(e.currentTarget), products, capacities));

        // Handle product selection changes
        $target.on('change', '.product-select', e => handleProductChange($(e.currentTarget), capacities));

        // Remove product row
        $target.on('click', '.remove-product-row', e => removeProductRow($(e.currentTarget)));

        // Remove truck row
        $target.on('click', '.remove-truck-row', e => removeTruckRow($(e.currentTarget)));

        // When truck or body type changes, revalidate all dependent rows
        $target.on('change', '.truck-select, .body-select', function () {
            const $row = $(this).closest('tr');
            const truckSelected = !!$row.find('.truck-select').val();

            // Disable product controls when no truck is selected
            $row.nextUntil('.truck-subtotal', '.product-extension')
                .find('.product-select, .qty-input')
                .prop('disabled', !truckSelected);

            $row.find('.add-product-row').prop('disabled', !truckSelected);

            // Keep all product-extension rows in sync with current truck/body
            $row.nextUntil('.truck-subtotal', '.product-extension').each(function () {
                $(this).attr('data-truck-id', $row.find('.truck-select').val() || '');
                $(this).attr('data-body-type', $row.find('.body-select').val() || '');
            });

            // Trigger recalculation (max qty, weight, totals)
            $row.find('.product-select').trigger('change');

            // Refresh all related product-extension rows for this truck
            $row.nextUntil('.truck-subtotal', '.product-extension')
                .find('.product-select')
                .each(function () {
                    $(this).trigger('change');
                });

            updateTotalWeight();
        });

        // Handle quantity input changes
        $target.on('input', '.qty-input', e => handleQtyInput($(e.currentTarget)));
    }

    // ================================================================
    // 3️⃣ TRANSPORT COST TABLE
    // ================================================================
    // Builds the HTML structure for the transport cost table
    function buildTransportTableHTML(distance) {
        return `
            <h5 class="mt-3">Transport Cost Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm w-100" id="transportTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Truck Type</th>
                            <th>Rate</th>
                            <th>Multiplier</th>
                            <th>Distance (km)</th>
                            <th>Unloading (₹)</th>
                            <th>Total Transport (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No trucks are selected.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="6" class="text-end">Total Transport Cost:</th>
                            <th id="total_transport_cost">₹0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    }

    // Sets up event handlers for transport table
    function setupTransportTable($target, trucks, districtRates, multipliers, distance) {
        // When truck or body changes, rebuild table
        $target.on('change', '.truck-select, .body-select', () =>
            refreshTransportTable(trucks, districtRates, multipliers, distance)
        );

        // Recalculate total cost when rate/km, fixed rate, or unloading changes
        $target.on('input', '.rate-km, .fixed-rate, .unloading-cost', function () {
            const $row = $(this).closest('tr');
            const rate = parseFloat($row.find('input.rate-km, input.fixed-rate').val()) || 0;
            const multiplierText = $row.find('.multiplier').text();
            const multiplier = multiplierText && multiplierText !== '-' ? parseFloat(multiplierText) || 1 : 1;
            const dist = parseFloat($row.find('td:nth-child(5) input').val()) || 0;
            const unloading = parseFloat($row.find('.unloading-cost').val()) || 0;

            let newTotal = 0;

            // Handle per-km vs fixed-rate logic
            if (distance < 150) {
                newTotal = (rate * multiplier * dist) + unloading;
            } else {
                newTotal = rate + unloading; // Fixed rate + unloading
            }

            $row.find('.transport-cost').text('₹' + newTotal.toFixed(2));
            updateTransportTotal();
        });

        // When unloading charge changes, update total cost immediately
        $target.on('input', '.unloading-cost', function () {
            const $row = $(this).closest('tr');
            const unloading = parseFloat($(this).val()) || 0;
            const rate = parseFloat($row.find('input.rate-km, input.fixed-rate').val()) || 0;
            const multiplier = parseFloat($row.find('.multiplier').text()) || 1;
            const dist = parseFloat($row.find('td:nth-child(5) input').val()) || 0;

            // Compute base cost (depends on distance type)
            const baseCost = (window.lastDistance && window.lastDistance < 150)
                ? rate * multiplier * dist
                : rate;

            const newTotal = baseCost + unloading;
            $row.find('.transport-cost').text('₹' + newTotal.toFixed(2));
            updateTransportTotal();
        });

        // When distance changes, recalculate that truck's transport total
        $target.on('input', '.transport-distance', function () {
            const $row = $(this).closest('tr');
            const newDistance = parseFloat($(this).val()) || 0;
            const rate = parseFloat($row.find('input.rate-km, input.fixed-rate').val()) || 0;
            const unloading = parseFloat($row.find('.unloading-cost').val()) || 0;
            const multiplierText = $row.find('.multiplier').text();
            const multiplier = multiplierText && multiplierText !== '-' ? parseFloat(multiplierText) || 1 : 1;

            // Update total cost for that row
            let total = 0;
            if (window.lastDistance < 150) {
                total = (rate * multiplier * newDistance) + unloading;
            } else {
                total = rate + unloading;
            }

            $row.find('.transport-cost').text('₹' + total.toFixed(2));
            updateTransportTotal();
        });
    }
    // ================================================================
    // LIVE DISTANCE VALIDATION (INLINE WARNING)
    // ================================================================
    $(document).on('input', '.transport-distance', function () {
        const $table = $('#transportTable');
        const $rows = $table.find('tbody tr');

        // Remove old warning (if any)
        $table.next('.distance-warning').remove();

        // Collect all distances
        const distances = [];
        $rows.each(function () {
            const val = parseFloat($(this).find('.transport-distance').val());
            if (!isNaN(val) && val > 0) distances.push(val);
        });

        // Skip check if less than 2 trucks
        if (distances.length <= 1) return;

        // Compare distinct rounded values
        const uniqueDistances = [...new Set(distances.map(d => d.toFixed(2)))];

        // Show or clear warning
        if (uniqueDistances.length > 1) {
            const warningHTML = `
                <div class="distance-warning mt-2 text-danger small fw-bold">
                    ⚠️ Distance values differ between trucks
                    (${uniqueDistances.join(' km, ')} km). Please make them equal.
                </div>`;
            $table.after(warningHTML);
        }
    });

    // ================================================================
    // 4️⃣ PRICE TABLE
    // ================================================================
    // Builds the HTML structure for the price details table
    function buildPriceTableHTML(products) {
        return `
        <h5 class="mt-3">Price Details (Manual Entry)</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm w-100" id="priceTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Rate/unit</th>
                        <th>Transport/unit</th>
                        <th>Total/Unit</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    ${products.map(p => `
                    <tr
                        data-product-id="${p.id}"
                        data-product-name="${p.name || p.sku}"
                        data-product-unit="${p.unit || 'Nos'}"
                    >
                        <td class="product-name">${p.sku}</td>
                        <td><input type="number" min="0" class="form-control form-control-sm qty" value="${p.requested_qty || 0}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm rate-unit" value="${p.price || 0}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm transport-unit" value="0"></td>
                        <td class="total-unit-price text-end">0</td>
                        <td class="total text-end">0</td>
                    </tr>`).join('')}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Subtotal:</th>
                        <th id="subtotal">₹0</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">GST (18%):</th>
                        <th id="gst">₹0</th>
                    </tr>
                    <tr class="table-success">
                        <th colspan="5" class="text-end">Net Total:</th>
                        <th id="net_total">₹0</th>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    }


    // Sets up event handlers for price table
    function setupPriceTable($target) {
        $target.on('input', '.rate-unit, .transport-unit, .qty', updatePriceTotals);
    }

    // ================================================================
    // UTILITY FUNCTIONS & EVENT HANDLERS
    // ================================================================

    // Handles product selection changes - updates max qty and weights
    function handleProductChange($select, capacities) {
        const $row = $select.closest('tr');

        // Get truck and body type (from current row or parent data attributes)
        let truckId = parseInt($row.find('.truck-select').val());
        let bodyUI = $row.find('.body-select').val();

        if (!truckId || isNaN(truckId)) truckId = parseInt($row.attr('data-truck-id'));
        if (!bodyUI) bodyUI = $row.attr('data-body-type');

        const productId = parseInt($select.val());
        const productWeight = parseFloat($select.find('option:selected').data('weight')) || 0;

        // Reset if invalid selection
        if (!truckId || !productId) {
            $row.find('.max-qty').text('0');
            $row.find('.qty-input').val('0');
            $row.find('.total-weight').text('0 kg');
            updateTotalWeight();
            return;
        }

        // Convert UI body type to DB format
        const bodyDB = (bodyUI || '').toLowerCase() === 'open' ? 'open_body_truck' : 'truck';

        // Find matching capacity from truck_capacities
        const match = capacities.find(c =>
            c.truck_type_id === truckId &&
            c.product_id === productId &&
            c.body_type.toLowerCase() === bodyDB
        );

        const max = match ? match.max_units : 0;
        const existingQty = parseFloat($row.find('.qty-input').val()) || 0;

        $row.find('.max-qty').text(max);

        // Don't override prefilled quantities (keep existing qty if >0)
        if (existingQty === 0) {
            $row.find('.qty-input').val(0);
            $row.find('.total-weight').text('0 kg');
        } else {
            $row.find('.total-weight').text(Math.round(existingQty * productWeight) + ' kg');
        }

        // Set requested quantity from available products
        const requested = (window.availableProductsForQuote || []).find(p => p.id === productId)?.requested_qty || 0;
        $row.find('.req-input').val(requested);

        updateTotalWeight();
    }

    // Handles quantity input changes - validates and updates weights
    function handleQtyInput($input) {
        const $row = $input.closest('tr');
        const qty = parseFloat($input.val()) || 0;
        const $product = $row.find('.product-select option:selected');
        const productWeight = parseFloat($product.data('weight')) || 0;
        const max = parseFloat($row.find('.max-qty').text()) || 0;

        if (max && qty > max) {
            // Just warn, but continue calculation
            console.warn(`⚠️ Quantity ${qty} exceeds max allowed ${max}`);
            $row.find('.max-warning').remove();
            $input.after(`<small class="text-danger max-warning">Max: ${max}</small>`);
        } else {
            $row.find('.max-warning').remove();
        }

        // Still update weight even if above max
        const totalWeight = Math.round(qty * productWeight);
        $row.find('.total-weight').text(totalWeight + ' kg');

        updateTotalWeight();

    }


    // Adds a new truck row to the allocation table
    function addTruckRow($target, trucks, products) {
        const $tbody = $target.find('#truck_allocation_body');

        // Count only main truck rows (not subtotal)
        const rowCount = $tbody.find('.allocation-row').length + 1;

        // Build the truck + subtotal rows together
        const truckHTML = buildTruckRowHTML(rowCount, trucks, products);
        const subtotalHTML = `
            <tr class="truck-subtotal table-light text-end">
                <td colspan="6"><strong>Truck ${rowCount} Total:</strong></td>
                <td class="truck-weight text-end">0 kg</td>
            </tr>`;

        // Insert both rows before the "+ Add Truck" control
        $tbody.find('.add-truck-control').before(truckHTML + subtotalHTML);
    }

    // Adds a new product row to an existing truck
    function addProductRow($button, products, capacities) {
        const $parentRow = $button.closest('tr');
        const truckId = $parentRow.find('.truck-select').val();
        const bodyType = $parentRow.find('.body-select').val();

        if (!truckId) {
            SwalCompact.alert('Oops!', 'Please select a truck before adding products.');
            return;
        }

        // Build new product extension row
        const newRow = `
            <tr class="product-extension"
                data-truck-id="${parseInt(truckId) || ''}"
                data-body-type="${bodyType || ''}">
                <td></td>
                <td colspan="2"></td>
                <td>
                    <div class="product-cell d-flex align-items-center gap-1">
                        <select class="form-control form-control-sm product-select">
                            <option value="">Select Product</option>
                            ${products.map(p =>
                                `<option value="${p.id}" data-weight="${p.weight_kg}">
                                    ${p.sku}
                                </option>`
                            ).join('')}
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-product-row">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </td>
                <td class="max-qty text-center">0</td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <input type="number" min="0" class="form-control form-control-sm qty-input w-50" value="0">
                        <input type="number" min="0" class="form-control form-control-sm req-input w-50 bg-light" value="0" readonly>
                    </div>
                </td>
                <td class="total-weight text-center">0 kg</td>
            </tr>`;

        // Find last product-extension row before subtotal, if any
        const $lastExtension = $parentRow.nextUntil('.truck-subtotal', '.product-extension').last();

        if ($lastExtension.length) {
            $lastExtension.after(newRow); // Append after the last existing one
        } else {
            $parentRow.after(newRow); // First extra row
        }

        // After adding, immediately trigger handleProductChange on new row (if truck already selected)
        const $newSelect = ($lastExtension.length ? $lastExtension.next() : $parentRow.next()).find('.product-select');
        if ($newSelect.length) {
            handleProductChange($newSelect, capacities);
        }
    }

    // Removes a product extension row from the allocation table
    function removeProductRow($button) {
        const $row = $button.closest('tr');

        // Prevent removing the main allocation row
        if (!$row.hasClass('product-extension')) {
            SwalCompact.alert('Oops!', 'Main product row cannot be removed.');
            return;
        }

        $row.remove();
        updateTotalWeight();
    }

    // Removes a truck and all its associated product rows
    // Removes a truck and all its associated product rows
    function removeTruckRow($button) {
        const $row = $button.closest('tr');
        const $tbody = $row.closest('tbody');

        SwalCompact.confirm('Remove Truck?', 'Do you want to remove this truck and all its products?')
            .then(result => {
                if (!result.isConfirmed) return;

                // Identify where to stop deleting (before add-truck-control or next allocation-row)
                let $next = $row.next();
                while ($next.length && !$next.hasClass('allocation-row') && !$next.hasClass('add-truck-control')) {
                    const $toRemove = $next;
                    $next = $next.next();
                    $toRemove.remove();
                }

                // Remove the main truck row itself
                $row.remove();

                // If no trucks left, reinsert a fresh empty truck row
                if ($tbody.find('.allocation-row').length === 0) {
                    const truckHTML = buildTruckRowHTML(1, window.availableTrucksForQuote || [], window.availableProductsForQuote || []);
                    const subtotalHTML = `
                        <tr class="truck-subtotal table-light text-end">
                            <td colspan="6"><strong>Truck 1 Total:</strong></td>
                            <td class="truck-weight text-end">0 kg</td>
                        </tr>`;
                    $tbody.find('.add-truck-control').before(truckHTML + subtotalHTML);
                }

                // Reindex all remaining trucks
                $tbody.find('.allocation-row').each(function (i) {
                    $(this).find('td:first').text(i + 1);
                    $(this).nextAll('.truck-subtotal:first')
                           .find('strong')
                           .text(`Truck ${i + 1} Total:`);
                });

                // Update all totals
                updateTotalWeight();

                // Refresh transport table cleanly (only rebuild tbody)
                if (window.lastAvailableTrucks) {
                    refreshTransportTable(
                        window.lastAvailableTrucks,
                        window.lastDistrictRates,
                        window.lastKmMultipliers,
                        window.lastDistance
                    );
                }
            });
    }

    // ================================================================
    // TRANSPORT TABLE REFRESH
    // ================================================================
    // Rebuilds transport table based on currently selected trucks
    function refreshTransportTable(trucks, districtRates, multipliers, distance) {
        const selectedTrucks = [];
        // Collect selected trucks with their body type
        $('#truckTable .allocation-row').each(function () {
            const truckId = parseInt($(this).find('.truck-select').val());
            const bodyType = ($(this).find('.body-select').val() || 'Truck').trim();
            if (truckId) selectedTrucks.push({ truckId, bodyType });
        });

        const $tbody = $('#transportTable tbody');
        $tbody.empty();

        if (!selectedTrucks.length) {
            $tbody.html(`<tr><td colspan="7" class="text-center text-muted">No trucks are selected.</td></tr>`);
            updateTransportTotal();
            return;
        }

        let htmlRows = '';
        selectedTrucks.forEach((t, i) => {
            const truck = trucks.find(x => x.id === t.truckId);
            if (!truck) return;

            const isOpenBody = t.bodyType.toLowerCase() === 'open';
            const unloading =
                distance < 150
                    ? parseFloat(truck.unloading_charges_below_150 || 0)
                    : parseFloat(truck.unloading_charges_above_150 || 0);

            let rateDisplay = 0;
            let baseCost = 0;
            let multiplier = 1;
            let rateTypeLabel = isOpenBody ? 'Rate/km' : 'Fixed Rate (₹)';
            let rateCellExtra = '';

            if (isOpenBody) {
                // 🟢 OPEN BODY → rate/km calculation
                const ratePerKm = parseFloat(truck.rate_per_km || 0);
                const match = multipliers.find(m => distance >= m.min_km && (!m.max_km || distance < m.max_km));
                multiplier = match ? match.multiplier : 1;
                baseCost = ratePerKm * multiplier * distance;
                rateDisplay = ratePerKm;
            } else {
                // 🟠 TRUCK → fixed rate
                let fixedRate = 0;
                if (window.lastDistrictRates) {
                    const match = window.lastDistrictRates.find(r =>
                        r.truck_type_id === t.truckId
                    );
                    fixedRate = match ? parseFloat(match.rate) : 0;
                }
                baseCost = fixedRate;
                rateDisplay = fixedRate;
                // ⚠️ Warning if missing
                if (!fixedRate) {
                    rateCellExtra = `
                        <div class="rate-message text-danger small mt-1">
                            ⚠️ Missing fixed rate
                            <a href="#"
                               class="text-primary text-decoration-underline open-rate-choice-modal"
                               data-truck-id="${t.truckId}" data-truck-name="${truck.name}">
                               Update
                            </a>
                        </div>`;
                } else {
                    rateCellExtra = `<div class="rate-message text-success small mt-1">✔ Rate set</div>`;
                }
            }

            const totalCost = baseCost + unloading;
            htmlRows += `
                <tr class="allocation-row">
                    <td>${i + 1}</td>
                    <td>${truck.name}<br><small class="text-muted">${t.bodyType}</small></td>
                    <td>
                        <input type="number"
                            class="form-control form-control-sm ${isOpenBody ? 'rate-km' : 'fixed-rate'}"
                            value="${rateDisplay.toFixed(2)}">
                        <label class="small text-muted">${rateTypeLabel}</label>
                        ${rateCellExtra}
                    </td>
                    <td>${isOpenBody ? multiplier : '-'}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm transport-distance"
                            value="${distance.toFixed(1)}" min="1" step="0.1">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm unloading-cost"
                            value="${unloading.toFixed(2)}">
                    </td>
                    <td class="transport-cost">₹${totalCost.toFixed(2)}</td>
                </tr>`;
        });

        $tbody.html(htmlRows);
        updateTransportTotal();
    }



    // ================================================================
    // WEIGHT AND COST CALCULATIONS
    // ================================================================

    // Updates the total weight across all trucks
    function updateTotalWeight() {
        let grandTotal = 0;

        // Loop through each main truck row
        $('#truckTable tbody .allocation-row').each(function () {
            const $truckRow = $(this);
            let truckTotal = 0;

            // Include this truck's own product weight
            const ownWeight = parseFloat($truckRow.find('.total-weight').text()) || 0;
            truckTotal += ownWeight;

            // Include all product-extension rows until the next truck-subtotal
            let $next = $truckRow.next();
            while ($next.length && !$next.hasClass('truck-subtotal') && !$next.hasClass('allocation-row')) {
                const w = parseFloat($next.find('.total-weight').text()) || 0;
                truckTotal += w;
                $next = $next.next();
            }

            // Update that truck's subtotal row
            $truckRow.nextAll('.truck-subtotal:first')
                     .find('.truck-weight')
                     .text(Math.round(truckTotal) + ' kg');

            grandTotal += truckTotal;
        });

        // Update the footer grand total
        $('#truck_total_weight').text(Math.round(grandTotal) + ' kg');
        computeTransportPerUnit();
    }

    // Updates the total transport cost across all trucks
    function updateTransportTotal() {
        let total = 0;
        $('#transportTable .transport-cost').each(function () {
            total += parseFloat($(this).text().replace(/[₹,]/g, '')) || 0;
        });
        $('#total_transport_cost').text('₹' + total.toFixed(2));

        // Recalculate per-unit transport immediately
        computeTransportPerUnit();
    }

    // Updates price table totals (subtotal, GST, net total)
    function updatePriceTotals() {
        let subtotal = 0;

        $('#priceTable tbody tr').each(function () {
            const qty = parseFloat($(this).find('.qty').val()) || 0;
            const rate = parseFloat($(this).find('.rate-unit').val()) || 0;
            const transport = parseFloat($(this).find('.transport-unit').val()) || 0;

            const totalUnitPrice = rate + transport;
            const totalPrice = totalUnitPrice * qty;

            // Update table cells
            $(this).find('.total-unit-price').text(totalUnitPrice.toFixed(2));
            $(this).find('.total').text(totalPrice.toFixed(2));

            // Accumulate subtotal
            subtotal += totalPrice;
        });

        // Calculate GST & Net total
        const gst = subtotal * 0.18;
        const net = subtotal + gst;

        // Update table footer
        $('#subtotal').text('₹' + subtotal.toFixed(2));
        $('#gst').text('₹' + gst.toFixed(2));
        $('#net_total').text('₹' + Math.round(net).toLocaleString('en-IN'));


        // Also update hidden estimated cost field if exists
        $('#estimated_cost').val(net.toFixed(2));
    }

    // Computes transport cost per unit for each product based on weight ratio
    function computeTransportPerUnit() {
        // 1️⃣ Get total transport cost (₹)
        const totalTransport = parseFloat($('#total_transport_cost').text().replace(/[₹,]/g, '')) || 0;

        // 2️⃣ Get total truck weight (kg)
        const totalWeightText = $('#truck_total_weight').text().replace(/[^\d.]/g, '');
        const totalWeight = parseFloat(totalWeightText) || 0;

        // 3️⃣ Avoid division by zero
        if (!totalTransport || !totalWeight) {
            console.warn('⚠️ Transport per unit skipped — missing total transport or total weight');
            return;
        }

        // 4️⃣ Compute ₹ per kg
        const costPerKg = totalTransport / totalWeight;

        // 5️⃣ For each product row, compute per-unit transport cost (rounded)
        $('#priceTable tbody tr').each(function () {
            const productId = parseInt($(this).data('product-id'));
            const product = (window.availableProductsForQuote || []).find(p => p.id === productId);

            if (!product || !product.weight_kg) return;

            const weightPerUnit = parseFloat(product.weight_kg) || 0;
            const transportPerUnit = (weightPerUnit * costPerKg).toFixed(2); // Rounded to nearest ₹

            // Update UI
            $(this).find('.transport-unit').val(transportPerUnit);
        });

        // 6️⃣ Finally, update all totals
        updatePriceTotals();
    }

    // ================================================================
    // DATA COLLECTION FUNCTIONS (FOR SUBMISSION)
    // ================================================================

    // Collects truck allocation data for QuoteTruckProduct table
    function collectTruckData() {
        const trucks = [];

        $('#truckTable tbody .allocation-row').each(function (index) {
            const $truckRow = $(this);
            const truckTypeId = parseInt($truckRow.find('.truck-select').val());
            if (!truckTypeId) return;

            const bodyType = $truckRow.find('.body-select').val() || 'Truck';

            // Get matching transport row
            const $transportRow = $('#transportTable tbody tr').eq(index);

            // Distance
            const distance =
                parseFloat($transportRow.find('.transport-distance').val()) ||
                parseFloat($('.distance_km, #quote_distance_km').val()) ||
                null;

            // Costs & rates
            const truckCost = parseFloat($transportRow.find('.transport-cost').text().replace(/[₹,]/g, '')) || 0;
            const unloading = parseFloat($transportRow.find('.unloading-cost').val()) || 0;

            // 🔹 Collect both rate_per_km and fixed_rate safely
            const ratePerKm = parseFloat($transportRow.find('input.rate-km').val()) || 0;
            const fixedRate = parseFloat($transportRow.find('input.fixed-rate').val()) || 0;

            // Detect which one is active (based on distance or visibility)
            let effectiveRatePerKm = 0;
            let effectiveFixedRate = 0;

            if (distance && distance < 150) {
                effectiveRatePerKm = ratePerKm;
                effectiveFixedRate = 0;
            } else {
                effectiveRatePerKm = 0;
                effectiveFixedRate = fixedRate || ratePerKm; // fallback if same field reused
            }

            // Total weight from subtotal row
            const totalWeight =
                parseFloat(
                    $truckRow
                        .nextAll('.truck-subtotal')
                        .first()
                        .find('.truck-weight')
                        .text()
                        .replace(/[^\d.]/g, '')
                ) || 0;

            // Truck object
            const truckData = {
                truck_id: truckTypeId,
                body_type: bodyType,
                truck_cost: truckCost,
                unloading_charges: unloading,
                distance_km: distance,
                multiplier: 1,
                rate_per_km: effectiveRatePerKm,
                fixed_rate: effectiveFixedRate,
                total_weight: totalWeight,
                products: []
            };

            // Collect products for this truck
            const $productRows = $truckRow.add($truckRow.nextUntil('.truck-subtotal', '.product-extension'));
            $productRows.each(function () {
                const $row = $(this);
                const productId = parseInt($row.find('.product-select').val());
                const qty = parseFloat($row.find('.qty-input').val()) || 0;
                if (!productId || qty <= 0) return;

                const product = window.availableProductsForQuote?.find(p => p.id === productId);
                const capacity = window.truck_capacities?.find(
                    c => c.truck_type_id === truckTypeId && c.product_id === productId
                );

                truckData.products.push({
                    product_id: productId,
                    qty: qty,
                    weight_per_unit: product ? parseFloat(product.weight || product.weight_kg || 0) : 0,
                    max_allowed_qty: capacity ? parseFloat(capacity.max_units || 0) : 0
                });
            });

            trucks.push(truckData);
        });

        return trucks;
    }

    // Collects aggregated product totals for QuotePriceDetail table
    function collectPriceData() {
        const priceData = [];
        const map = {}; // Map product_id => aggregated totals

        $('#priceTable tbody tr').each(function () {
            const $row = $(this);
            const productId = parseInt($row.data('product-id'));
            if (!productId) return;

            const qty = parseFloat($row.find('.qty').val()) || 0;
            const unitPrice = parseFloat($row.find('.rate-unit').val()) || 0;
            const transportUnit = parseFloat($row.find('.transport-unit').val()) || 0;
            const totalUnitPrice = unitPrice + transportUnit;
            const totalPrice = qty * totalUnitPrice;

            // ✅ Get product name and unit from data attributes or fallback
            const productName = $row.data('product-name') || $row.find('.product-name').text().trim() || 'Product';
            const productUnit = $row.data('product-unit') || 'Nos';

            if (!map[productId]) {
                map[productId] = {
                    product_id: productId,
                    product_name: productName,    // ✅ include product name
                    product_unit: productUnit,    // ✅ include unit
                    total_qty: 0,
                    unit_price: unitPrice,
                    transport_unit: transportUnit,
                    total_unit_price: totalUnitPrice,
                    total_price: 0
                };
            }

            map[productId].total_qty += qty;
            map[productId].total_price += totalPrice;
        });

        // Convert map to array
        for (const pid in map) {
            priceData.push(map[pid]);
        }

        return priceData;
    }


    // Collects transport data per truck for submission
    function collectTransportData() {
        const transportData = [];
        $('#transportTable tbody tr').each(function () {
            const $row = $(this);
            const truckName = $row.find('td:nth-child(2)').text();
            const rate = parseFloat($row.find('input').val()) || 0;
            const cost = parseFloat($row.find('.transport-cost').text().replace(/[₹,]/g, '')) || 0;
            const unloading = parseFloat($row.find('.unloading-cost').val()) || 0;

            if (!truckName) return;

            transportData.push({
                truck_name: truckName,
                rate,
                unloading,
                cost
            });
        });
        return transportData;
    }

    // ================================================================
    // VALIDATION FUNCTIONS
    // ================================================================

    // Validates that allocated quantities match requested quantities
    function validateQuantities() {
        let valid = true;
        let alertMessages = [];

        // ==========================================================
        // 1️⃣ Validate Price Table qty (requested vs entered)
        // ==========================================================
        $('#priceTable tbody tr').each(function () {
            const $row = $(this);
            const requested = parseFloat($row.find('.qty').attr('value')) || 0; // original requested qty
            const entered = parseFloat($row.find('.qty').val()) || 0; // current user entry
            const sku = $row.find('td:first').text();

            $row.removeClass('table-danger');

            if (requested !== entered) {
                valid = false;
                $row.addClass('table-danger');
                alertMessages.push(`Quantity mismatch for ${sku}: Requested ${requested}, Entered ${entered}`);
            }
        });

        // ==========================================================
        // 2️⃣ Collect all truck allocations (product-wise total)
        // ==========================================================
        const trucks = collectTruckData(); // [{id, name, products:[{product_id, qty}, ...]}, ...]
        const allocatedMap = {};

        trucks.forEach(t => {
            t.products.forEach(p => {
                if (!p.product_id || isNaN(p.qty)) return;
                allocatedMap[p.product_id] = (allocatedMap[p.product_id] || 0) + parseFloat(p.qty);
            });
        });

        // Clear previous highlights
        $('#truckTable tbody tr.allocation-row, #truckTable tbody tr.product-extension').removeClass('table-danger');

        // ==========================================================
        // 3️⃣ Validate Allocated Total vs Requested Total (Product-wise)
        // ==========================================================
        $('#priceTable tbody tr').each(function () {
            const $row = $(this);
            const productId = parseInt($row.data('product-id'));
            const requestedQty = parseFloat($row.find('.qty').val()) || 0;
            const allocatedQty = allocatedMap[productId] || 0;
            const sku = $row.find('td:first').text();

            if (requestedQty !== allocatedQty) {
                valid = false;

                // Highlight all truck rows for this product
                $('#truckTable tbody tr').each(function () {
                    const $truckRow = $(this);
                    const rowProductId = parseInt($truckRow.find('.product-select').val());
                    if (rowProductId === productId) {
                        $truckRow.addClass('table-danger');
                    }
                });

                $row.addClass('table-danger');
                alertMessages.push(`Allocation mismatch for ${sku}: Requested ${requestedQty}, Allocated ${allocatedQty}`);
            }
        });

        // ==========================================================
        // 4️⃣ Truck with zero allocations check
        // ==========================================================
        trucks.forEach(t => {
            const totalAlloc = t.products.reduce((sum, p) => sum + (parseFloat(p.qty) || 0), 0);
            if (totalAlloc === 0) {
                valid = false;

                // Highlight the truck row visually
                $(`#truckTable tbody tr.allocation-row[data-truck-id="${t.id}"]`).addClass('table-danger');

                alertMessages.push(`One of the trucks has no product allocations.`);
            }
        });

        // ==========================================================
        // 5️⃣ Show all alerts if any mismatch
        // ==========================================================
        if (!valid && alertMessages.length) {
            SwalCompact.alert('Validation Errors', alertMessages.join('<br>'));
        }

        return valid;
    }

</script>
