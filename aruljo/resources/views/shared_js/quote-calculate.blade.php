    <script>
    function initQuoteCalculator(container = document) {
        const $container = $(container);

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

        const getDistance = () => parseFloat($container.find('.distance_km').val());

        $container.off('click', '#calculate_quote_btn').on('click', '#calculate_quote_btn', function () {
            const products = collectProductData();
            const distance = getDistance();
            const delivery_location_id = $container.find('.delivery_location_id, #quote_delivery_location_id').val();

            if (!distance || products.length === 0) return alert('Please enter products and distance.');

            const loader = $container.find('.loader, #loader');
            loader.show();

            $.ajax({
                url: '{{ route("leads.reference-data") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    products,
                    distance_km: distance,
                    delivery_location_id,
                    include_draft: true // 🆕 tells backend to run calculateByCapacity() internally
                },
                success: res => {
                    loader.hide();
                    console.log(res);
                    window.availableProductsForQuote = res.available_products || [];
                    // If backend sent preallocation data → show editable version
                    if (res.draft_allocations && res.draft_allocations.length) {
                        renderManualQuoteTables(res, $container.find('#calcDetailsBody'));
                        $container.find('#calcDetailsCollapse').collapse('show');
                    } else {
                        // fallback (readonly)
                        renderManualQuoteTables(res, $container.find('#calcDetailsBody'));
                        $container.find('#calcDetailsCollapse').collapse('show');
                    }
                },
                error: xhr => {
                    loader.hide();
                    console.error(xhr.status, xhr.responseText);
                    alert('Error fetching reference data. Please try again.');
                }
            });
        });

        $container.find('#toggle_calc_details').off('click').on('click', function () {
            $container.find('#calcDetailsCollapse').collapse('toggle');
        });
    }

    /* ================================================================
    |   Render Main Quote Tables
    ================================================================ */
    function renderManualQuoteTables(data, $target) {
        const trucks = data.available_trucks || [];
        const products = data.available_products || [];
        const capacities = data.truck_capacities || [];
        const districtRates = data.district_rates || [];
        const multipliers = data.km_multipliers || [];
        const distance = parseFloat(data.distance_km) || 0;
        const drafts = data.draft_allocations || [];

        window.availableProductsForQuote = data.available_products || [];
        // Store for later use when removing trucks
        window.lastAvailableTrucks = trucks;
        window.lastDistrictRates = districtRates;
        window.lastKmMultipliers = multipliers;
        window.lastDistance = distance;


        // 1️⃣ Build base structure (empty tables)
        $target.html(buildTruckAllocationHTML(trucks, products));
        $target.append(buildTransportTableHTML(distance));
        $target.append(buildPriceTableHTML(products));
        console.log(products);
        console.log('Prefilling trucks:', drafts);
        // 2️⃣ If we have draft allocations, populate them
        if (drafts.length) {

            const $tbody = $target.find('#truck_allocation_body');
            $tbody.empty(); // clear default empty row

            drafts.forEach((truckData, index) => {
                const rowIndex = index + 1;
                const truckHTML = buildTruckRowHTML(rowIndex, trucks, products);
                const subtotalHTML = `
                    <tr class="truck-subtotal table-light text-end">
                        <td colspan="6"><strong>Truck ${rowIndex} Total:</strong></td>
                        <td class="truck-weight text-end">0 kg</td>
                    </tr>`;

                // Append truck rows
                $tbody.append(truckHTML + subtotalHTML);

                const $truckRow = $tbody.find('.allocation-row').last();

                // Preselect truck and body
                $truckRow.find('.truck-select').val(truckData.truck_id);
                $truckRow.find('.body-select').val(truckData.body_type || 'Truck');

                // Fill products
                // Fill products
                truckData.items.forEach((item, i) => {
                    const requested = parseFloat(item.requested_qty || 0);

                    if (i === 0) {
                        // first product goes into main row
                        $truckRow.find('.product-select').val(item.product_id);
                        handleProductChange($truckRow.find('.product-select'), capacities);
                        $truckRow.find('.qty-input').val(item.qty).trigger('input');
                        $truckRow.find('.req-input').val(requested);
                    } else {
                        // additional products go in extension rows
                        addProductRow($truckRow.find('.add-product-row'), products, capacities);
                        const $extRow = $truckRow.nextAll('.product-extension').last();
                        $extRow.find('.product-select').val(item.product_id);
                        handleProductChange($extRow.find('.product-select'), capacities);
                        $extRow.find('.qty-input').val(item.qty).trigger('input');
                        $extRow.find('.req-input').val(requested);
                    }
                });
            });

            // Finally, add the "+ Add Truck" control and totals footer back
            $tbody.append(`
                <tr class="add-truck-control text-center">
                    <td colspan="7">
                        <button type="button" class="btn btn-sm btn-outline-primary add-truck-row">+ Add Truck</button>
                    </td>
                </tr>
            `);
            // ✅ After prefilling all data, recompute all total weights
            $tbody.find('.qty-input').each(function () {
                handleQtyInput($(this));
            });

        }

        // 3️⃣ Attach all event handlers
        setupTruckAllocationEvents($target, trucks, products, capacities, distance);
        setupTransportTable($target, trucks, districtRates, multipliers, distance);
        setupPriceTable($target);

        // 4️⃣ Update all totals immediately
        updateTotalWeight();
        refreshTransportTable(trucks, districtRates, multipliers, distance);
        updatePriceTotals();
        computeTransportPerUnit();
    }

    /* ================================================================
    |   1️⃣ Truck Allocation Table HTML Builder
    ================================================================ */
    function buildTruckAllocationHTML(trucks, products) {
        return `
        <h5 class="mt-3">Truck Allocation</h5>
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
        </table>`;
    }

    function buildTruckRowHTML(index, trucks, products) {
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
                    <option value="Truck">Truck</option>
                    <option value="Open">Open</option>
                </select>
            </td>
            <td>
                <div class="product-cell d-flex align-items-center gap-1">
                    <select class="form-control form-control-sm product-select">
                        <option value="">Select Product</option>
                        ${products.map(p => `<option value="${p.id}" data-weight="${p.weight_kg}">${p.sku}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-success add-product-row"><i class="fa fa-plus"></i></button>
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

    /* ================================================================
    |   2️⃣ Truck Allocation Events
    ================================================================ */
    function setupTruckAllocationEvents($target, trucks, products, capacities, distance) {
        $target.on('click', '.add-truck-row', e => addTruckRow($target, trucks, products));
        $target.on('click', '.add-product-row', e => addProductRow($(e.currentTarget), products, capacities));
        $target.on('change', '.product-select', e => handleProductChange($(e.currentTarget), capacities));
        $target.on('click', '.remove-product-row', e => removeProductRow($(e.currentTarget)));
        $target.on('click', '.remove-truck-row', e => removeTruckRow($(e.currentTarget)));

        // 🔹 When truck or body changes → revalidate dependent rows
        $target.on('change', '.truck-select, .body-select', function () {
            const $row = $(this).closest('tr');
            const truckSelected = !!$row.find('.truck-select').val();

            // 🔹 Disable product selects and '+' buttons when no truck selected
            $row.nextUntil('.truck-subtotal', '.product-extension')
                .find('.product-select, .qty-input')
                .prop('disabled', !truckSelected);

            $row.find('.add-product-row').prop('disabled', !truckSelected);

            // 🔹 Keep all product-extension rows in sync with current truck/body
            $row.nextUntil('.truck-subtotal', '.product-extension').each(function () {
                $(this).attr('data-truck-id', $row.find('.truck-select').val() || '');
                $(this).attr('data-body-type', $row.find('.body-select').val() || '');
            });

            // 🔹 Trigger recalculation (max qty, weight, totals)
            $row.find('.product-select').trigger('change');

            // Also refresh all related product-extension rows for this truck
            $row.nextUntil('.truck-subtotal', '.product-extension')
                .find('.product-select')
                .each(function () {
                    $(this).trigger('change');
                });

            updateTotalWeight();

        });


        $target.on('input', '.qty-input', e => handleQtyInput($(e.currentTarget)));
    }


    /* ================================================================
    |   3️⃣ Transport Cost Table
    ================================================================ */
    function buildTransportTableHTML(distance) {
        return `
        <h5 class="mt-3">Transport Cost Details</h5>
        <table class="table table-bordered table-striped table-sm w-100" id="transportTable">
            <thead>
                <tr>
                    <th>#</th><th>Truck Type</th>
                    <th>${distance < 150 ? 'Rate/km' : 'Fixed Rate (₹)'}</th>
                    ${distance < 150 ? '<th>Multiplier</th><th>Distance (km)</th>' : '<th colspan="2">Distance (km)</th>'}
                    <th>Transport Cost (₹)</th>
                </tr>
            </thead>
            <tbody><tr><td colspan="6" class="text-center text-muted">No trucks are selected.</td></tr></tbody>
            <tfoot>
                <tr class="table-success"><th colspan="5" class="text-end">Total Transport Cost:</th><th id="total_transport_cost">₹0</th></tr>
            </tfoot>
        </table>`;
    }

    function setupTransportTable($target, trucks, districtRates, multipliers, distance) {
        // When truck or body changes, rebuild table
        $target.on('change', '.truck-select, .body-select', () =>
            refreshTransportTable(trucks, districtRates, multipliers, distance)
        );

        // 🟢 When rate per km or fixed rate changes, recalc cost instantly
        $target.on('input', '.rate-km', function () {
            const $row = $(this).closest('tr');
            const rate = parseFloat($(this).val()) || 0;
            const dist = distance || 0;
            const multiplier = parseFloat($row.find('.multiplier').text()) || 1;
            const newCost = rate * multiplier * dist;
            $row.find('.transport-cost').text('₹' + Math.round(newCost));
            updateTransportTotal();
        });

        // 🟢 For fixed-rate type (distance > 150)
        $target.on('input', '.fixed-rate', function () {
            const $row = $(this).closest('tr');
            const rate = parseFloat($(this).val()) || 0;
            $row.find('.transport-cost').text('₹' + Math.round(rate));
            updateTransportTotal();
        });
    }


    /* ================================================================
    |   4️⃣ Price Table
    ================================================================ */
    function buildPriceTableHTML(products) {
        return `
        <h5 class="mt-3">Price Details (Manual Entry)</h5>
        <table class="table table-bordered table-striped table-sm w-100" id="priceTable">
            <thead><tr><th>Product</th><th>Qty</th><th>Rate/unit (₹)</th><th>Transport/unit (₹)</th><th>Total Price (₹)</th></tr></thead>
            <tbody>${products.map(p => `
                <tr data-product-id="${p.id}">
                    <td>${p.sku}</td>
                    <td><input type="number" min="0" class="form-control form-control-sm qty" value="${p.requested_qty || 0}"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm rate-unit" value="${p.price || 0}"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm transport-unit" value="0"></td>
                    <td class="total text-end">0</td>
                </tr>`).join('')}</tbody>
            <tfoot>
                <tr><th colspan="4" class="text-end">Subtotal:</th><th id="subtotal">₹0</th></tr>
                <tr><th colspan="4" class="text-end">GST (18%):</th><th id="gst">₹0</th></tr>
                <tr class="table-success"><th colspan="4" class="text-end">Net Total:</th><th id="net_total">₹0</th></tr>
            </tfoot>
        </table>`;
    }

    function setupPriceTable($target) {
        $target.on('input', '.rate-unit, .transport-unit, .qty', updatePriceTotals);
    }

    /* ================================================================
    |   Utility + Handlers
    ================================================================ */
    function handleProductChange($select, capacities) {
        const $row = $select.closest('tr');

        let truckId = parseInt($row.find('.truck-select').val());
        let bodyUI = $row.find('.body-select').val();

        if (!truckId || isNaN(truckId)) truckId = parseInt($row.attr('data-truck-id'));
        if (!bodyUI) bodyUI = $row.attr('data-body-type');

        const productId = parseInt($select.val());
        const productWeight = parseFloat($select.find('option:selected').data('weight')) || 0;

        if (!truckId || !productId) {
            $row.find('.max-qty').text('0');
            $row.find('.qty-input').val('0');
            $row.find('.total-weight').text('0 kg');
            updateTotalWeight();
            return;
        }

        const bodyDB = (bodyUI || '').toLowerCase() === 'open' ? 'open_body_truck' : 'truck';
        const match = capacities.find(c =>
            c.truck_type_id === truckId &&
            c.product_id === productId &&
            c.body_type.toLowerCase() === bodyDB
        );

        const max = match ? match.max_units : 0;
        const existingQty = parseFloat($row.find('.qty-input').val()) || 0;

        $row.find('.max-qty').text(max);

        // ✅ Don’t override prefills (keep existing qty if >0)
        if (existingQty === 0) {
            $row.find('.qty-input').val(0);
            $row.find('.total-weight').text('0 kg');
        } else {
            $row.find('.total-weight').text(Math.round(existingQty * productWeight) + ' kg');
        }

        const requested = (window.availableProductsForQuote || []).find(p => p.id === productId)?.requested_qty || 0;
        $row.find('.req-input').val(requested);

        updateTotalWeight();
    }

    function handleQtyInput($input) {
        const $row = $input.closest('tr');
        const qty = parseFloat($input.val()) || 0;
        const $product = $row.find('.product-select option:selected');
        const productWeight = parseFloat($product.data('weight')) || 0;
        const max = parseFloat($row.find('.max-qty').text()) || 0;

        if (max && qty > max) return alert(`⚠️ Max allowed quantity is ${max}`);
        $row.find('.total-weight').text(Math.round(qty * productWeight) + ' kg');
        updateTotalWeight();
    }

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
    function addProductRow($button, products, capacities) {
        const $parentRow = $button.closest('tr');
        const truckId = $parentRow.find('.truck-select').val();
        const bodyType = $parentRow.find('.body-select').val();

        if (!truckId) {
            alert('Please select a truck before adding products.');
            return;
        }

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

        // 🔹 Find last product-extension row before subtotal, if any
        const $lastExtension = $parentRow.nextUntil('.truck-subtotal', '.product-extension').last();

        if ($lastExtension.length) {
            $lastExtension.after(newRow); // append after the last existing one
        } else {
            $parentRow.after(newRow); // first extra row
        }
        // 🔹 After adding, immediately trigger handleProductChange on new row (if truck already selected)
        const $newSelect = ($lastExtension.length ? $lastExtension.next() : $parentRow.next()).find('.product-select');
        if ($newSelect.length) {
            handleProductChange($newSelect, capacities);
        }

    }

    function removeProductRow($button) {
        const $row = $button.closest('tr');

        // Prevent removing the main allocation row
        if (!$row.hasClass('product-extension')) {
            alert('Main product row cannot be removed.');
            return;
        }

        $row.remove();
        updateTotalWeight();
    }
    function removeTruckRow($button) {
        const $row = $button.closest('tr');
        const $tbody = $row.closest('tbody');

        if (!confirm('Remove this truck and all its products?')) return;

        // 🔹 Identify where to stop deleting (before add-truck-control)
        let $next = $row.next();
        while ($next.length && !$next.hasClass('allocation-row') && !$next.hasClass('add-truck-control')) {
            const $toRemove = $next;
            $next = $next.next();
            $toRemove.remove();
        }

        // 🔹 Remove the main truck row itself
        $row.remove();

        // 🔹 If no trucks left, reinsert a fresh empty truck row
        if ($tbody.find('.allocation-row').length === 0) {
            const truckHTML = buildTruckRowHTML(1, window.availableTrucksForQuote || [], window.availableProductsForQuote || []);
            const subtotalHTML = `
                <tr class="truck-subtotal table-light text-end">
                    <td colspan="6"><strong>Truck 1 Total:</strong></td>
                    <td class="truck-weight text-end">0 kg</td>
                </tr>`;
            $tbody.find('.add-truck-control').before(truckHTML + subtotalHTML);
        }

        // 🔹 Reindex all remaining trucks
        $tbody.find('.allocation-row').each(function (i) {
            $(this).find('td:first').text(i + 1);
            $(this).nextAll('.truck-subtotal:first')
                   .find('strong')
                   .text(`Truck ${i + 1} Total:`);
        });

        // 🔹 Update all totals
        updateTotalWeight();

        // ✅ Refresh transport table cleanly (only rebuild tbody, not remove the whole table)
        if (window.lastAvailableTrucks) {
            refreshTransportTable(
                window.lastAvailableTrucks,
                window.lastDistrictRates,
                window.lastKmMultipliers,
                window.lastDistance
            );
        }

    }

    function refreshTransportTable(trucks, districtRates, multipliers, distance) {
        const selectedTruckIds = [];
        $('#truckTable .truck-select').each(function () {
            const val = $(this).val();
            if (val) selectedTruckIds.push(parseInt(val));
        });

        const tbody = $('#transportTable tbody');
        tbody.empty();

        if (!selectedTruckIds.length) {
            tbody.html(`<tr><td colspan="6" class="text-center text-muted">No trucks are selected.</td></tr>`);
            updateTransportTotal();
            return;
        }

        let htmlRows = '';
        selectedTruckIds.forEach((truckId, i) => {
            const truck = trucks.find(t => t.id === truckId);
            if (!truck) return;

            let rate = truck.rate_per_km || 0;
            let multiplier = 1;
            let cost = 0;

            if (distance < 150) {
                const match = multipliers.find(m => distance >= m.min_km && (!m.max_km || distance < m.max_km));
                multiplier = match ? match.multiplier : 1;
                cost = rate * multiplier * distance;

                htmlRows += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${truck.name}</td>
                        <td><input type="number" class="form-control form-control-sm rate-km" value="${rate}"></td>
                        <td class="multiplier">${multiplier}</td>
                        <td>${distance}</td>
                        <td class="transport-cost">₹${Math.round(cost)}</td>
                    </tr>`;
            } else {
                const fixed = districtRates.find(r => r.truck_type_id === truckId);
                rate = fixed ? fixed.rate : 0;
                cost = rate;

                htmlRows += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${truck.name}</td>
                        <td><input type="number" class="form-control form-control-sm fixed-rate" value="${rate}"></td>
                        <td colspan="2">${distance}</td>
                        <td class="transport-cost">₹${Math.round(cost)}</td>
                    </tr>`;
            }
        });

        tbody.html(htmlRows);
        updateTransportTotal();
    }

    function updateTotalWeight() {
        let grandTotal = 0;

        // Loop through each main truck row
        $('#truckTable tbody .allocation-row').each(function () {
            const $truckRow = $(this);
            let truckTotal = 0;

            // Include this truck’s own product weight
            const ownWeight = parseFloat($truckRow.find('.total-weight').text()) || 0;
            truckTotal += ownWeight;

            // Include all product-extension rows until the next truck-subtotal
            let $next = $truckRow.next();
            while ($next.length && !$next.hasClass('truck-subtotal') && !$next.hasClass('allocation-row')) {
                const w = parseFloat($next.find('.total-weight').text()) || 0;
                truckTotal += w;
                $next = $next.next();
            }

            // Update that truck’s subtotal row
            $truckRow.nextAll('.truck-subtotal:first')
                     .find('.truck-weight')
                     .text(Math.round(truckTotal) + ' kg');

            grandTotal += truckTotal;
        });

        // Update the footer grand total
        $('#truck_total_weight').text(Math.round(grandTotal) + ' kg');
    }


    function updateTransportTotal() {
        let total = 0;
        $('#transportTable .transport-cost').each(function () {
            total += parseFloat($(this).text().replace(/[₹,]/g, '')) || 0;
        });
        $('#total_transport_cost').text('₹' + Math.round(total));

         // 🟢 Recalculate per-unit transport immediately
            computeTransportPerUnit();
    }

    function updatePriceTotals() {
        let subtotal = 0;

        $('#priceTable tbody tr').each(function () {
            const qty = parseFloat($(this).find('.qty').val()) || 0;
            const rate = parseFloat($(this).find('.rate-unit').val()) || 0;
            const trans = parseFloat($(this).find('.transport-unit').val()) || 0;

            const total = qty * (rate + trans);
            subtotal += total;

            // 🟢 Update per-row total cell
            $(this).find('.total').text('₹' + Math.round(total));
        });

        const gst = subtotal * 0.18;
        const net = subtotal + gst;

        // 🧾 Keep two decimals (.00) for GST and Net total
        $('#subtotal').text('₹' + Math.round(subtotal));
        $('#gst').text('₹' + gst.toFixed(2));
        $('#net_total').text('₹' + net.toFixed(2));
    }

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
            const transportPerUnit = Math.round(weightPerUnit * costPerKg); // 🟢 Rounded to nearest ₹

            // Update UI
            $(this).find('.transport-unit').val(transportPerUnit);
        });

        // 6️⃣ Finally, update all totals
        updatePriceTotals();
    }

    //Collect to store
    function collectTruckData() {
        const trucks = [];
        $('#truckTable tbody .allocation-row').each(function () {
            const $truckRow = $(this);
            const truckTypeId = parseInt($truckRow.find('.truck-select').val());
            if (!truckTypeId) return; // skip empty truck

            const bodyType = $truckRow.find('.body-select').val() || 'Truck';
            const truckData = {
                truck_type_id: truckTypeId,
                body_type: bodyType,
                truck_count: parseInt($truckRow.find('.truck-count').val()) || 1,
                truck_cost: parseFloat($truckRow.find('.truck-cost').text().replace(/[₹,]/g, '')) || 0,
                distance_km: parseFloat($truckRow.find('.truck-distance').val()) || null,
                multiplier: parseFloat($truckRow.find('.truck-multiplier').val()) || 1,
                products: []
            };

            // Include main product row
            const mainProductId = parseInt($truckRow.find('.product-select').val());
            const mainQty = parseFloat($truckRow.find('.qty-input').val()) || 0;
            const mainReq = parseFloat($truckRow.find('.req-input').val()) || 0;
            const mainUnitPrice = parseFloat($truckRow.find('.unit-price').val()) || 0;
            const mainTotalPrice = parseFloat($truckRow.find('.total-price').text().replace(/[₹,]/g, '')) || 0;

            if (mainProductId && mainQty) {
                truckData.products.push({
                    product_id: mainProductId,
                    quantity: mainQty,
                    requested_qty: mainReq,
                    unit_price: mainUnitPrice,
                    total_price: mainTotalPrice
                });
            }

            // Include extension product rows
            $truckRow.nextUntil('.truck-subtotal', '.product-extension').each(function () {
                const $ext = $(this);
                const extProductId = parseInt($ext.find('.product-select').val());
                const extQty = parseFloat($ext.find('.qty-input').val()) || 0;
                const extReq = parseFloat($ext.find('.req-input').val()) || 0;
                const extUnitPrice = parseFloat($ext.find('.unit-price').val()) || 0;
                const extTotalPrice = parseFloat($ext.find('.total-price').text().replace(/[₹,]/g, '')) || 0;

                if (extProductId && extQty) {
                    truckData.products.push({
                        product_id: extProductId,
                        quantity: extQty,
                        requested_qty: extReq,
                        unit_price: extUnitPrice,
                        total_price: extTotalPrice
                    });
                }
            });

            trucks.push(truckData);
        });

        return trucks;
    }

    function collectPriceData() {
        const priceData = [];
        $('#priceTable tbody tr').each(function () {
            const $row = $(this);
            const productId = parseInt($row.data('product-id'));
            if (!productId) return;

            const qty = parseFloat($row.find('.qty').val()) || 0;
            const unitPrice = parseFloat($row.find('.rate-unit').val()) || 0;
            const transportUnit = parseFloat($row.find('.transport-unit').val()) || 0;
            const total = parseFloat($row.find('.total').text().replace(/[₹,]/g, '')) || 0;

            priceData.push({
                product_id: productId,
                qty: qty,
                unit_price: unitPrice,
                transport_unit: transportUnit,
                total_price: total
            });
        });
        return priceData;
    }

    function collectTransportData() {
        const transportData = [];
        $('#transportTable tbody tr').each(function () {
            const $row = $(this);
            const truckName = $row.find('td:nth-child(2)').text();
            const costText = $row.find('.transport-cost').text();
            if (!truckName || costText === '') return;

            const truckId = parseInt($row.find('.truck-select').val()) || null; // optional
            const rate = parseFloat($row.find('input').val()) || 0;
            const cost = parseFloat(costText.replace(/[₹,]/g, '')) || 0;

            transportData.push({
                truck_id: truckId,
                truck_name: truckName,
                rate: rate,
                cost: cost
            });
        });
        return transportData;
    }
    </script>
