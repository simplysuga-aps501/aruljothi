<script>
    /* ---------------- DELIVERY & FOLLOW-UP DAYS ---------------- */
    function initDaysCalculation(container = document) {
        $(container).find('input[type="date"][data-output]').each(function() {
            const input = $(this);
            const outputId = input.data('output');
            const output = $('#' + outputId);

            const update = () => {
                const val = input.val();
                if (!val) {
                    output.text('');
                    return;
                }

                const selectedDate = new Date(val);
                const now = new Date();
                now.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);

                const diff = Math.round((selectedDate - now) / (1000 * 60 * 60 * 24));
                output.text(diff === 0 ? 'Today' : diff > 0 ? `${diff} day(s) from today` :
                    `${Math.abs(diff)} day(s) ago`);
            };

            input.off('change', update).on('change', update);
            update();
        });
    }

    /* ---------------- PRODUCT PILL HANDLING ---------------- */
    function initProductPills(containerSelector, productsList) {

        $(containerSelector).each(function () {

            let container = $(this);
            let pillsContainer = container.find(".product-pills");
            let textarea = container.find(".product-detail");
            let searchInput = container.find(".product-search");
            let qtyInput = container.find(".product-qty");
            let addBtn = container.find(".product-add");
            let alertBox = container.find(".product-alert");

            // Add pill
            addBtn.off("click").on("click", function () {
                var name = searchInput.val().trim();
                var qty = qtyInput.val().trim();

                if (!name) return showProductError(alertBox, "Enter a product name.");
                if (!qty || qty <= 0) return showProductError(alertBox, "Quantity is required.");
                if (productsList && !productsList.some(p => p.name === name)) {
                    return showProductError(alertBox, "Select a valid product from the list.");
                }

                // prevent duplicates
                var exists = false;
                pillsContainer.find(".badge").each(function () {
                    var text = $(this).clone().children().remove().end().text().trim();
                    if (text.split(" , ")[0].toLowerCase() === name.toLowerCase()) {
                        exists = true;
                        return false;
                    }
                });
                if (exists) return showProductError(alertBox, "This product already added.");

                // Find the weight from productsList
                var productObj = productsList.find(p => p.name === name);
                var weight = productObj ? parseFloat(productObj.weight) : 0;
                var sku = productObj ? productObj.sku : null;
                var id = productObj ? productObj.id : null;
                var price = productObj ? parseFloat(productObj.price) : 0;

                // Create pill with data attributes
                var pill = $('<span class="pill badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                             ' <i class="fas fa-times ml-1" style="cursor:pointer;"></i></span>');
                pill.data('name',name);
                pill.data('id',id);
                pill.data('sku',sku);
                pill.data('qty', qty);
                pill.data('weight', weight);
                 pill.data('price', price);
                pill.find('i').click(function () {
                    pill.remove();
                    updateProductTextarea(pillsContainer, textarea);
                });
                pillsContainer.append(pill);
                updateProductTextarea(pillsContainer, textarea);

                searchInput.val('');
                qtyInput.val('');
            });

           // Rebuild pills from textarea
           var existing = textarea.val();
           if (existing) {
               var items = existing.split('~|~');
               items.forEach(function (item) {
                   var parts = item.split(",");
                   var name = parts[0].trim();
                   var qty  = (parts[1] || "").trim();

                   var productObj = productsList.find(p => p.name === name);
                   var weight = productObj ? parseFloat(productObj.weight) : 0;
                   var sku = productObj ? productObj.sku : null; // ✅ FIX: Declare sku here
                   var id = productObj ? productObj.id : null;
                   var price = productObj ? parseFloat(productObj.price) : 0;

                   var pill = $('<span class="pill badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                                ' <i class="fas fa-times ml-1" style="cursor:pointer;"></i></span>');
                   pill.data('name', name);
                   pill.data('id', id);
                   pill.data('sku', sku);
                   pill.data('qty', qty);
                   pill.data('weight', weight);
                   pill.data('price', price);

                   pill.find('i').click(function () {
                       pill.remove();
                       updateProductTextarea(pillsContainer, textarea);
                   });
                   pillsContainer.append(pill);
               });
           }
        });
    }

    function updateProductTextarea(pillsContainer, textarea) {
        var details = [];
        pillsContainer.find(".badge").each(function() {
            var text = $(this).clone().children().remove().end().text().trim();
            details.push(text);
        });
        textarea.val(details.join('~|~'));
    }

    function showProductError(alertBox, msg) {
        var alert = $(alertBox);
        alert.text(msg).removeClass('d-none');
        setTimeout(() => alert.addClass('d-none'), 3000);
    }

    /* ---------------- TAG MULTISELECT ---------------- */
    function initTagMultiselect(selector = '#tags') {
        $(selector).multiselect('destroy').multiselect({
            includeSelectAllOption: true,
            buttonWidth: '100%',
            nonSelectedText: 'Select Tags',
            numberDisplayed: 2,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true
        });
    }

    /* ---------------- PINCODE → LOCATION + DISTANCE ---------------- */

    function initPincodeAutocomplete(
            pincodeInput,
            buyerLocation,
            buyerLocationId,
            distanceInput,
            durationInput,
            loader,
            appendToTarget = "body"
        ) {
            let stateCache = '',
                districtCache = '';

            // Inline error element
            let $error = $('<div class="text-danger small mt-1" style="display:none;"></div>');
            $(pincodeInput).closest('.form-group').append($error);

            let opts = {
                minLength: 6,
                source: function(request, response) {
                    let pincode = request.term.trim();
                    if (pincode.length === 6 && /^\d+$/.test(pincode)) {
                        $.ajax({
                            url: '{{ route('distance.byPincode') }}',
                            data: { pincode: pincode },
                            success: function(data) {
                                if (data && data.places && data.places.length > 0) {
                                    stateCache = data.state || '';
                                    districtCache = data.district || '';
                                    response($.map(data.places, function(place, index) {
                                        return {
                                            label: `${place}, ${districtCache}, ${stateCache}`,
                                            value: place,
                                            id: data.ids[index]
                                        };
                                    }));
                                    $error.hide();
                                } else {
                                    response([]);
                                    $error.text("Invalid pincode! Please enter a valid pincode.").show();
                                    $(buyerLocation).val('');
                                    $(buyerLocationId).val('');
                                    $(distanceInput).val('');
                                    $(durationInput).val('');
                                    $(loader).hide();
                                }
                            },
                            error: function() {
                                response([]);
                                $error.text("Error fetching pincode info.").show();
                                $(buyerLocation).val('');
                                $(buyerLocationId).val('');
                                $(distanceInput).val('');
                                $(durationInput).val('');
                                $(loader).hide();
                            }
                        });
                    } else {
                        response([]);
                    }
                },
                select: function(event, ui) {
                    if (!ui.item || !ui.item.id) {
                        $error.text("Invalid pincode! Please select a valid location.").show();
                        $(pincodeInput).val('');
                        $(buyerLocation).val('');
                        $(buyerLocationId).val('');
                        $(distanceInput).val('');
                        $(durationInput).val('');
                        $(loader).hide();
                        return false;
                    }

                    let place = ui.item.value;
                    let pincode = $(pincodeInput).val().trim();
                    let fullLocation = `${place}, ${districtCache}, ${stateCache}${pincode ? ' - ' + pincode : ''}`;

                    $(buyerLocation).val(fullLocation).attr('title', fullLocation);
                    $(buyerLocationId).val(ui.item.id);

                    $(loader).show();
                    $(distanceInput).val('');
                    $(durationInput).val('');

                    $.get('{{ route('distance.calc') }}', { to_id: ui.item.id })
                        .done(function(data) {
                            $(loader).hide();
                            if (data.distance_km != null && data.duration_minutes != null) {
                                $(distanceInput).val(Math.round(parseFloat(data.distance_km)));
                                $(durationInput).val(Math.round(parseFloat(data.duration_minutes)));
                                $error.hide();
                            } else {
                                $(distanceInput).val('');
                                $(durationInput).val('');
                                $error.text("Could not calculate distance.").show();
                            }
                        })
                        .fail(function() {
                            $(loader).hide();
                            $(distanceInput).val('');
                            $(durationInput).val('');
                            $error.text("Error fetching distance.").show();
                        });
                }
            };

            if (appendToTarget && appendToTarget !== "#") {
                opts.appendTo = appendToTarget;
            }

            $(pincodeInput).autocomplete(opts);

            // clear fields when pincode is cleared
            $(pincodeInput).on("input", function() {
                if ($(this).val().trim() === "") {
                    $(buyerLocation).val("");
                    $(buyerLocationId).val("");
                    $(distanceInput).val("");
                    $(durationInput).val("");
                    $(loader).hide();
                    $error.hide();
                }
            });
        }
    function initDistanceDurationEditable() {
        $('.editable_field').off('dblclick').on('dblclick', function() {
            const inputGroup = $(this).closest('.input-group');
            const modal = $(this).closest('.modal, body'); // works for modal or page
            const pincodeInput = modal.find('.pincode_input').first();
            const $alertDiv = inputGroup.siblings('.distance_alert');

            if (!$alertDiv.length) return;

            if (!pincodeInput.val().trim()) {
                $alertDiv.text('Please enter a pincode first.').show();
                setTimeout(() => $alertDiv.fadeOut(), 3000);
                return;
            }

            $(this).prop('readonly', false).focus();
            $(this).css('background-color', '#ffffff'); // white while editing
        });

        $('.editable_field').off('blur').on('blur', function() {
            $(this).prop('readonly', true);
            $(this).css('background-color', '#d1ecf1'); // blue when readonly
        });
    }

 function initQuoteCalculator(container = document) {
     const $container = $(container);

     /**
      * Collect product data from pills
      */
     function collectProductData() {
         let products = [];
         $container.find('.product-pills .pill').each(function () {
             products.push({
                 name: $(this).data('name'),
                 id: $(this).data('id'),
                 sku: $(this).data('sku'),
                 qty: parseFloat($(this).data('qty')),
                 price: $(this).data('price'),
                 weight: parseFloat($(this).data('weight'))
             });
         });
         return products;
     }

     /**
      * Get distance safely
      */
     function getDistance() {
         const distanceInput = $container.find('.distance_km');
         return parseFloat(distanceInput.val());
     }


     // ---------------- CALCULATE QUOTE BUTTON ----------------
     $container.off('click', '#calculate_quote_btn').on('click', '#calculate_quote_btn', function () {
         const products = collectProductData();
         console.log(products);
         const distance = getDistance();
         console.log("distance",distance);
         const delivery_location_id = $container.find('.delivery_location_id').val();

         if (!distance || products.length === 0) {
             alert('Please enter products and distance.');
             return;
         }

         const loader = $container.find('.loader, #loader');
         loader.show();

         $.ajax({
             url: '{{ route("leads.calculate-quote") }}',
             type: 'POST',
             data: {
                 _token: '{{ csrf_token() }}',
                 products,
                 distance_km: distance,
                 delivery_location_id : delivery_location_id
             },
             success: function (res) {
                 loader.hide();
                  console.log("called");
                 // Fill main result fields
                 $container.find('#suggested_truck_type').val(res.truck_type);
                 $container.find('#suggested_num_trucks').val(res.num_trucks);
                 $container.find('#estimated_cost').val(res.total_cost);

                 // Inject collapsible content (hidden by default)
                 const $calcBody = $container.find('#calcDetailsBody');
                 $calcBody.html(res.details_html || '<em>No calculation details available.</em>');

                 // Ensure collapse is hidden initially
                 $container.find('#calcDetailsCollapse').collapse('show');
             },
             error: function (xhr) {
                 loader.hide();
                 console.error(xhr.status, xhr.responseText);
                 alert('Error calculating quote. Please try again.');
             }
         });
     });

     // ---------------- TOGGLE COLLAPSIBLE ----------------
     $container.find('#toggle_calc_details').off('click').on('click', function () {
         $container.find('#calcDetailsCollapse').collapse('toggle');
     });
 }
 /* ================================================================
 |   Read-Only Quote Display
 ================================================================ */
 function renderReadOnlyQuote(data, $target) {
     const trucks = data.available_trucks || [];
     const products = data.available_products || [];
     const transport = data.transport || [];
     const capacities = data.truck_capacities || [];
     const distance = parseFloat(data.distance_km) || 0;
     const summary = {
         subtotal: data.subtotal || 0,
         gst_rate: data.gst_rate || 18,
         net_total: data.net_total || 0
     };
     const drafts = data.draft_allocations || [];

     let html = ``;

     /* ========== 1️⃣ Truck Allocation Table ========== */
     html += `
         <h5 class="mt-3">Truck Allocation</h5>
         <div class="table-responsive">
         <table class="table table-bordered table-striped table-sm w-100">
             <thead>
                 <tr>
                     <th>#</th>
                     <th style="width:25%">Truck Type</th>
                     <th>Body Type</th>
                     <th style="width:30%">Product</th>
                     <th>Max Allowed</th>
                     <th>Alloc-Req</th>
                     <th>Total Weight</th>
                 </tr>
             </thead>
             <tbody>`;

     if (drafts.length) {
         drafts.forEach((truckData, i) => {
             let totalWeight = 0;

             // main truck row
             html += `
                 <tr>
                     <td>${i + 1}</td>
                     <td>${getTruckName(trucks, truckData.truck_id)}</td>
                     <td>${truckData.body_type || 'Truck'}</td>`;

             // loop products inside this truck directly
             truckData.items.forEach(item => {
                 const p = products.find(p => p.id === item.product_id);
                 const allocated = parseFloat(item.qty || 0);
                 const requested = parseFloat(item.requested_qty || 0);
                 const weight = (p?.weight_kg || 0) * allocated;
                 totalWeight += weight;
                 const maxAllowed = parseFloat(item.max_allowed_qty || 0);

                 html += `
                     <td>${p ? p.sku : '-'}</td>
                         <td>${maxAllowed}</td>
                         <td>${allocated} – ${requested}</td>
                         <td>${Math.round(weight)} kg</td>`;
             });

             html += `
                 </tr>
                 <tr class="table-light text-end">
                     <td colspan="6"><strong>Truck ${i + 1} Total:</strong></td>
                     <td>${Math.round(totalWeight)} kg</td>
                 </tr>`;
         });
     } else {
         html += `<tr><td colspan="7" class="text-center text-muted">No truck allocations found.</td></tr>`;
     }

     html += `
             </tbody>
             <tfoot>
                 <tr class="table-success">
                     <th colspan="6" class="text-end">Total Weight:</th>
                     <th>${Math.round(data.total_weight || 0)} kg</th>
                 </tr>
             </tfoot>
         </table>
     </div>
     `;


     /* ========== 2️⃣ Transport Cost Table ========== */
     html += `
         <h5 class="mt-3">Transport Cost Details</h5>
         <div class="table-responsive">
         <table class="table table-bordered table-striped table-sm w-100">
             <thead>
                 <tr>
                     <th>#</th>
                     <th>Truck Type</th>
                     <th>${distance < 150 ? 'Rate/km' : 'Fixed Rate (₹)'}</th>
                     <th>Multiplier</th>
                     <th>Distance (km)</th>
                     <th>Unloading (₹)</th>
                     <th>Total Transport (₹)</th>
                 </tr>
             </thead>
             <tbody>`;

     if (transport.length) {
         transport.forEach((t, i) => {
             html += `
                 <tr>
                     <td>${i + 1}</td>
                     <td>${t.truck_name}</td>
                     <td>${t.rate || 0}</td>
                     <td>${t.multiplier || '-'}</td>
                     <td>${distance}</td>
                     <td>${t.unloading || 0}</td>
                     <td class="text-end">₹${parseFloat(t.cost || 0).toLocaleString()}</td>
                 </tr>`;
         });
     } else {
         html += `<tr><td colspan="7" class="text-center text-muted">No transport data found.</td></tr>`;
     }

     html += `
             </tbody>
             <tfoot>
                 <tr class="table-success">
                     <th colspan="6" class="text-end">Total Transport Cost:</th>
                     <th class="text-end">₹${parseFloat(data.total_transport || 0).toLocaleString()}</th>
                 </tr>
             </tfoot>
         </table>
         </div>
     `;

     /* ========== 3️⃣ Price Table ========== */
     html += `
         <h5 class="mt-3">Price Details</h5>
         <div class="table-responsive">
         <table class="table table-bordered table-striped table-sm w-100">
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
             <tbody>`;

     if (products.length) {
         products.forEach(p => {
             const totalUnit = (parseFloat(p.price || 0) + parseFloat(p.transport_unit || 0));
             const amount = totalUnit * (p.requested_qty || 0);
             html += `
                 <tr>
                     <td>${p.sku}</td>
                     <td class="text-center">${p.requested_qty || 0}</td>
                     <td class="text-end">${parseFloat(p.price || 0).toFixed(2)}</td>
                     <td class="text-end">${parseFloat(p.transport_unit || 0).toFixed(2)}</td>
                     <td class="text-end">${totalUnit.toFixed(2)}</td>
                     <td class="text-end">₹${amount.toLocaleString()}</td>
                 </tr>`;
         });
     } else {
         html += `<tr><td colspan="6" class="text-center text-muted">No price details available.</td></tr>`;
     }

     html += `
             </tbody>
             <tfoot>
                 <tr><th colspan="5" class="text-end">Subtotal:</th><th>₹${parseFloat(summary.subtotal).toFixed(2)}</th></tr>
                 <tr><th colspan="5" class="text-end">GST (${summary.gst_rate}%):</th><th>₹${(summary.subtotal * (summary.gst_rate / 100)).toFixed(2)}</th></tr>
                 <tr class="table-success"><th colspan="5" class="text-end">Net Total:</th><th>₹${parseFloat(summary.net_total).toFixed(2)}</th></tr>
             </tfoot>
         </table>
         </div>
     `;

     $target.html(html);
 }

 /* Utility: Get truck name by id */
 function getTruckName(trucks, id) {
     const t = trucks.find(tr => tr.id == id);
     return t ? t.name : '-';
 }


 </script>

