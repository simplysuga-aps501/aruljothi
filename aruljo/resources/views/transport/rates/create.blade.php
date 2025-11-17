<div class="modal fade" id="addRateModal" tabindex="-1" role="dialog" aria-labelledby="addRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="addRateModalLabel">Add New Rate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <form id="addRateForm" method="POST" action="{{ route('rates.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="state">State <span class="text-danger">*</span></label>
                        <select id="state" name="state" class="form-control">
                            <option value="">-- Select State --</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district">District <span class="text-danger">*</span></label>
                        <select id="district" name="district" class="form-control" disabled>
                            <option value="">-- Select District --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location_id">Places <span class="text-danger">*</span></label>
                        <select id="location_id" name="location_id[]" class="form-control" multiple disabled>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="truck_type_id">Truck Type <span class="text-danger">*</span></label>
                        <select id="truck_type_id" name="truck_type_id" class="form-control" required>
                            <option value="">-- Select Truck Type --</option>
                            @foreach($truckTypes as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="office_id">Office (Optional)</label>
                        <select id="office_id" name="office_id" class="form-control">
                            <option value="">-- Select Office --</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rate">Rate <span class="text-danger">*</span></label>
                        <input type="number" name="rate" id="rate" class="form-control" placeholder="Enter rate" required>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Optional"></textarea>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap5-multiselect/dist/js/bootstrap5-multiselect.min.js"></script>
<script>
$(function () {

    // State → District
    $('#state').on('change', function () {
        const state = $(this).val();
        $('#district').prop('disabled', true).html('<option>Loading...</option>');
        $('#location_id').prop('disabled', true).html('');

        if (state) {
            $.ajax({
                url: "{{ route('rates.getDistricts') }}",
                data: { state },
                success: function (districts) {
                    let options = '<option value="">-- Select District --</option>';
                    $.each(districts, (_, district) => {
                        options += `<option value="${district}">${district}</option>`;
                    });
                    $('#district').html(options).prop('disabled', false);
                }
            });
        } else {
            $('#district').html('<option value="">-- Select District --</option>');
        }
    });

    // District → Places
    $('#district').on('change', function () {
        const state = $('#state').val();
        const district = $(this).val();

        $('#location_id').prop('disabled', true).html('');

        if (state && district) {
            $.ajax({
                url: "{{ route('rates.getPlaces') }}",
                data: { state, district },
                success: function (places) {
                    let options = '';
                    $.each(places, (_, place) => {
                        options += `<option value="${place.id}">${place.place}</option>`;
                    });
                    $('#location_id').html(options).prop('disabled', false);
                    $('#location_id').multiselect('destroy');
                    initMultiSelect();
                }
            });
        }
    });
});
</script>
@endpush
