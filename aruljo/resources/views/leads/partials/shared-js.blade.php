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
                    console.log(productsList);
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
         const distance = getDistance();

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
                 distance_km: distance
             },
             success: function (res) {
                 loader.hide();

                 // Fill main result fields
                 $container.find('#suggested_truck_type').val(res.truck_type);
                 $container.find('#suggested_num_trucks').val(res.num_trucks);
                 $container.find('#estimated_cost').val(res.total_cost);

                 // Inject collapsible content (hidden by default)
                 const $calcBody = $container.find('#calcDetailsBody');
                 $calcBody.html(res.details_html || '<em>No calculation details available.</em>');

                 // Ensure collapse is hidden initially
                 $container.find('#calcDetailsCollapse').collapse('hide');
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
     // 📋 Copy to Clipboard
     $('#copy_whatsapp_text').on('click', function() {
         const summary = getSummaryText();
         const $alertDiv = $container.find('.quote_alert');

         if (!summary) {
             $alertDiv.text('Please generate a quote first (Draft Quote).')
                 .removeClass('text-success').addClass('text-danger')
                 .stop(true, true).show();
             setTimeout(() => $alertDiv.fadeOut(), 3000);
             return;
         }

         navigator.clipboard.writeText(summary.plainText)
             .then(() => {
                 $alertDiv.text('Quotation summary copied to clipboard!')
                     .removeClass('text-danger').addClass('text-success')
                     .stop(true, true).show();
                 setTimeout(() => $alertDiv.fadeOut(), 2000);
             })
             .catch(() => {
                 $alertDiv.text('Could not copy automatically. Please allow clipboard permission.')
                     .removeClass('text-success').addClass('text-danger')
                     .stop(true, true).show();
                 setTimeout(() => $alertDiv.fadeOut(), 3000);
             });
     });


     // 💬 Send via WhatsApp
     $('#send_whatsapp_btn').on('click', function() {
         const phone = $('input[name="buyer_contact"]').val()?.trim();
         const $alertDiv = $container.find('.quote_alert');
         if (!phone || phone.replace(/\D/g, '').length < 10) {
             $alertDiv.text('Please enter a valid buyer contact number.').stop(true, true).show();
             setTimeout(() => $alertDiv.fadeOut(), 3000);
             return;
         }

         const summary = getSummaryText();
         if (!summary) {
             $alertDiv.text('Please generate a quote first (Draft Quote).').stop(true, true).show();
             setTimeout(() => $alertDiv.fadeOut(), 3000);
             return;
         }

         const cleanPhone = phone.replace(/\D/g, '');
         const url = `https://wa.me/${cleanPhone}?text=${summary.text}`;
         window.open(url, '_blank');
     });
 }
function getSummaryText() {
    // Find summary table
    let summaryTable = $('#calcDetailsBody .summary-table').get(0);
    if (!summaryTable) {
        const tables = $('#calcDetailsBody').find('table');
        if (tables.length) summaryTable = tables.last().get(0);
    }

    if (!summaryTable) return null;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = summaryTable.outerHTML;

    const rows = tempDiv.querySelectorAll('tr');
    let items = [];

    // Build a quick SKU→Name map from pills (we already have this data)
    let skuNameMap = {};
    $('.product-pills .pill').each(function () {
        const sku = $(this).data('sku');
        const name = $(this).data('name');
        if (sku && name) skuNameMap[sku.trim()] = name.trim();
    });

    // Loop through table rows
    rows.forEach((row, idx) => {
        const cells = [...row.querySelectorAll('th,td')].map(c => c.innerText.trim());
        if (idx > 0 && cells.length >= 5) {
            const sku = cells[0];
            const productName = skuNameMap[sku] || sku; // fallback to SKU if name not found
            const qty = parseFloat(cells[1].replace(/,/g, '')) || 0;
            const rate = parseFloat(cells[2].replace(/,/g, '')) || 0;
            const transport = parseFloat(cells[3].replace(/,/g, '')) || 0;
            const total = cells[4];
            const combinedRate = rate + transport;

            items.push(`. ${productName} : ${qty} × ₹${combinedRate} = ₹${total}`);

        }
    });

    // Extract totals
    const tableText = tempDiv.innerText;
    const subtotalMatch = tableText.match(/Subtotal.*?([\d,]+\.\d{2})\s*₹?/i);
    const gstMatch = tableText.match(/GST[^\d]*(?:\(\d+%?\))?.*?([\d,]+\.\d{2})\s*₹?/i);
    const totalMatch = tableText.match(/Net\s*Total.*?([\d,]+\.\d{2})\s*₹?/i);

    const subtotal = subtotalMatch ? `₹${subtotalMatch[1]}` : '';
    const gst = gstMatch ? `₹${gstMatch[1]}` : '';
    const total = totalMatch ? `₹${totalMatch[1]}` : '';

    // Final formatted message
    let plainText = `Quotation Summary\n\n`;
    let whatsappText = `*Quotation Summary*%0A%0A`;

    plainText += items.join('\n') + '\n\n';
    whatsappText += items.map(i => i.replace(/\n/g, '%0A')).join('%0A') + '%0A%0A';

    plainText += `Subtotal: ${subtotal}\n`;
    plainText += `GST (18%): ${gst}\n`;
    plainText += `Net Total: ${total}\n\n`;
    plainText += `Thank you for your business!`;

    whatsappText += `*Subtotal:* ${subtotal}%0A`;
    whatsappText += `*GST (18%):* ${gst}%0A`;
    whatsappText += `*Net Total:* ${total}%0A%0A`;
    whatsappText += `_Thank you for your business!_`;

    return { text: whatsappText, plainText };
}

 </script>

