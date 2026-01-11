<script>
/* -------------------------
   1️⃣ PINCODE AUTOCOMPLETE
-------------------------- */
function initOfficePincodeAutocomplete(pincodeInput, locationInput, locationIdInput, errorDiv) {
    let stateCache = '', districtCache = '';

    $(pincodeInput).autocomplete({
        minLength: 6,
        source: function (request, response) {
            const pincode = request.term.trim();
            if (pincode.length === 6 && /^\d+$/.test(pincode)) {
                $.ajax({
                    url: "{{ route('distance.byPincode',[],false) }}",
                    data: { pincode },
                    success: function (data) {
                        if (data?.places?.length > 0) {
                            stateCache = data.state || '';
                            districtCache = data.district || '';
                            response($.map(data.places, (place, i) => ({
                                label: `${place}, ${districtCache}, ${stateCache}`,
                                value: place,
                                id: data.ids[i]
                            })));
                            $(errorDiv).hide();
                        } else {
                            response([]);
                            $(errorDiv).text('Invalid pincode!').show();
                            $(locationInput).val('');
                            $(locationIdInput).val('');
                        }
                    },
                    error: function () {
                        response([]);
                        $(errorDiv).text('Error fetching pincode info.').show();
                        $(locationInput).val('');
                        $(locationIdInput).val('');
                    }
                });
            } else {
                response([]);
            }
        },
        select: function (event, ui) {
            if (!ui.item?.id) {
                $(errorDiv).text('Please select a valid location.').show();
                $(locationInput).val('');
                $(locationIdInput).val('');
                return false;
            }

            const pincode = $(pincodeInput).val().trim();
            const fullLocation = `${ui.item.value}, ${districtCache}, ${stateCache} - ${pincode}`;
            $(locationInput).val(fullLocation);
            $(locationIdInput).val(ui.item.id);
            $(errorDiv).hide();
        }
    });

    // Reset location if pincode is cleared
    $(pincodeInput).on("input", function () {
        if (!$(this).val().trim()) {
            $(locationInput).val('');
            $(locationIdInput).val('');
            $(errorDiv).hide();
        }
    });

    // 🚨 Final safeguard before form submission
    $(pincodeInput).closest('form').on('submit', function (e) {
        if (!$(locationIdInput).val()) {
            e.preventDefault();
            $(errorDiv).text('Please select a valid location from the list.').show();
            $(locationInput).focus();
        }
    });
}



/* -------------------------------------------
   2️⃣ STATE + DISTRICT MULTISELECT HANDLER
-------------------------------------------- */
window.initOfficeLocationSelector = function(config) {
    const { stateSelect, districtSelect, container, hiddenInput } = config;
    const selectedDistricts = new Set();

    // Load districts on state change
    $(stateSelect).on('change', function() {
        const state = $(this).val();
        $(districtSelect).empty().append('<option value="">-- Select District --</option>');
        if (!state) return;

        $.get('/transport/get-districts', { states: [state] }, function(data) {
            if (data && data.length > 0) {
                data.forEach(d => {
                    $(districtSelect).append(`<option value="${d.district}">${d.district}</option>`);
                });
            }
        });
    });

    // Add district pill
    $(districtSelect).on('change', function() {
        const district = $(this).val();
        const state = $(stateSelect).val();
        if (!district || !state) return;

        const key = `${state}::${district}`;
        if (!selectedDistricts.has(key)) {
            selectedDistricts.add(key);
            updateDistrictPills();
        }
        $(this).val('');
    });

    // Remove pill
    $(document).on('click', `${container} .remove-pill`, function() {
        const key = $(this).data('key');
        selectedDistricts.delete(key);
        updateDistrictPills();
    });

    // Update UI pills + hidden input
    function updateDistrictPills() {
        const $container = $(container);
        $container.empty();

        if (selectedDistricts.size === 0) {
            $container.html('<small class="text-muted">No districts selected yet.</small>');
        } else {
            selectedDistricts.forEach(key => {
                const [state, district] = key.split('::');
                $container.append(`
                    <span class="badge badge-primary m-1">
                        ${district} <small>(${state})</small>
                        <button type="button" class="ml-1 close text-white remove-pill"
                                data-key="${key}" style="font-size:0.8rem;">&times;</button>
                    </span>
                `);
            });
        }
        $(hiddenInput).val(JSON.stringify(Array.from(selectedDistricts)));
    }

    // Prefill for edit mode
    return {
        prefill(prefilledKeys) {
            selectedDistricts.clear();
            if (prefilledKeys && prefilledKeys.length) {
                prefilledKeys.forEach(k => selectedDistricts.add(k));
            }
            updateDistrictPills();
        },
        clearAll() {
            selectedDistricts.clear();
            updateDistrictPills();
        },
        getSelected() {
            return Array.from(selectedDistricts);
        }
    };
};
</script>
