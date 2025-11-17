@push('scripts')
<script>
function loadDistricts(selectState, selectDistrict, selectPlace) {
    const state = $(selectState).val();
    $(selectDistrict).prop('disabled', true).html('<option>Loading...</option>');
    $(selectPlace).prop('disabled', true).html('<option>-- Select Place --</option>');

    if (state) {
        $.get("{{ route('rates.getDistricts') }}", { state }, function (districts) {
            let options = '<option value="">-- Select District --</option>';
            $.each(districts, (_, district) => options += `<option value="${district}">${district}</option>`);
            $(selectDistrict).html(options).prop('disabled', false);
        });
    } else {
        $(selectDistrict).html('<option value="">-- Select District --</option>');
    }
}

function loadPlaces(selectState, selectDistrict, selectPlace) {
    const state = $(selectState).val();
    const district = $(selectDistrict).val();
    $(selectPlace).prop('disabled', true).html('<option>Loading...</option>');

    if (state && district) {
        $.get("{{ route('rates.getPlaces') }}", { state, district }, function (places) {
            let options = '<option value="">-- Select Place --</option>';
            $.each(places, (_, place) => options += `<option value="${place.id}">${place.place}</option>`);
            $(selectPlace).html(options).prop('disabled', false);
        });
    } else {
        $(selectPlace).html('<option value="">-- Select Place --</option>');
    }
}

// Bind events for both modals
$(function () {
    // Add Modal
    $('#state').on('change', function () {
        loadDistricts('#state', '#district', '#location_id');
    });
    $('#district').on('change', function () {
        loadPlaces('#state', '#district', '#location_id');
    });

    // Edit Modal
    $('#edit_state').on('change', function () {
        loadDistricts('#edit_state', '#edit_district', '#edit_location_id');
    });
    $('#edit_district').on('change', function () {
        loadPlaces('#edit_state', '#edit_district', '#edit_location_id');
    });

    // Reset create form on close
    $('#addRateModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('#district').prop('disabled', true).html('<option value="">-- Select District --</option>');
        $('#location_id').prop('disabled', true).html('<option value="">-- Select Place --</option>');
    });
});
function initMultiSelect() {
    $('#location_id').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        maxHeight: 300,
        nonSelectedText: 'Select Places',
        allSelectedText: 'All Selected',
    });
}

</script>
@endpush
