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
                if (productsList && !productsList.includes(name)) {
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

                var pill = $('<span class="badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                             ' <i class="fas fa-times ml-1" style="cursor:pointer;"></i></span>');
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
                var items = existing.split(/~\|~|\n/);
                items.forEach(function (item) {
                    var parts = item.split(",");
                    var name = parts[0].trim();
                    var qty  = (parts[1] || "").trim();

                    var pill = $('<span class="badge badge-info mr-1 mb-1">' + name + ' , ' + qty +
                                 ' <i class="fas fa-times ml-1" style="cursor:pointer;"></i></span>');
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
        distanceResult,
        loader,
        appendToTarget = "body") {

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
                                $error.hide(); // hide error if places found
                            } else {
                                response([]);
                                $error.text("Invalid pincode! Please enter a valid pincode.").show();
                                $(buyerLocation).val('');
                                $(buyerLocationId).val('');
                                $(distanceResult).val('');
                                $(loader).hide();
                            }
                        },
                        error: function() {
                            response([]);
                            $error.text("Error fetching pincode info.").show();
                            $(buyerLocation).val('');
                            $(buyerLocationId).val('');
                            $(distanceResult).val('');
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
                    $(distanceResult).val('');
                    $(loader).hide();
                    return false;
                }

                let place = ui.item.value;
                let pincode = $(pincodeInput).val().trim();
                let fullLocation = `${place}, ${districtCache}, ${stateCache}${pincode ? ' - ' + pincode : ''}`;

                $(buyerLocation).val(fullLocation).attr('title', fullLocation);
                $(buyerLocationId).val(ui.item.id);

                $(loader).show();
                $(distanceResult).addClass('d-none').val('');

                $.get('{{ route('distance.calc') }}', { to_id: ui.item.id })
                    .done(function(data) {
                        $(loader).hide();
                        $(distanceResult).removeClass('d-none')
                            .val(data.distance_km ?
                                `${Math.round(parseFloat(data.distance_km))} km (${data.duration_minutes} mins)` :
                                "Could not calculate distance.");
                        $error.hide(); // hide error if successful
                    })
                    .fail(function() {
                        $(loader).hide();
                        $(distanceResult).removeClass('d-none').val("Error fetching distance.");
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
                $(distanceResult).val("");
                $(loader).hide();
                $error.hide();
            }
        });
    }

</script>
