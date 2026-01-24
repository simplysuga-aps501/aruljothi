<script>
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
                            url: "{{ route('distance.byPincode',[],false) }}",
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

                    $.get('{{ route('distance.calc',[],false) }}', { to_id: ui.item.id })
                        .done(function(data) {
                            $(loader).hide();
                            if (data.distance_km != null && data.duration_minutes != null) {
                                $(distanceInput).val(Math.round(parseFloat(data.distance_km))).trigger('change');
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
</script>
