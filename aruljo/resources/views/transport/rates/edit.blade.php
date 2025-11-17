<div class="modal fade" id="editRateModal" tabindex="-1" aria-labelledby="editRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editRateForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="editRateModalLabel">Edit Rate</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-row mb-3">
                        <div class="col-md-4">
                            <label>State</label>
                            <input type="text" id="edit_state" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>District</label>
                            <input type="text" id="edit_district" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Place</label>
                            <input type="text" id="edit_place" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="col-md-4">
                            <label>Truck Type</label>
                            <input type="text" id="edit_truck_type_name" class="form-control" readonly>
                            <input type="hidden" name="truck_type_id" id="edit_truck_type_id">
                        </div>
                        <div class="col-md-4">
                            <label>Office</label>
                            <select name="office_id" id="edit_office_id" class="form-control">
                                <option value="">-- Select Office --</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Rate</label>
                            <input type="number" step="0.01" name="rate" id="edit_rate" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <label>Remarks</label>
                            <input type="text" name="remarks" id="edit_remarks" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    // open modal when clicking place name
    $(document).on('click', '.open-edit-modal', function () {
        const id = $(this).data('id');
        $.get(`/transport/rates/${id}/edit`, function (data) {
            const form = $('#editRateForm');
            form.attr('action', `/transport/rates/${id}`);

            $('#edit_state').val(data.state);
            $('#edit_district').val(data.district);
            $('#edit_place').val(data.place);
            $('#edit_office_id').val(data.office_id);
            $('#edit_truck_type_id').val(data.truck_type_id);
            $('#edit_truck_type_name').val(data.truck_type_name);
            $('#edit_rate').val(data.rate);
            $('#edit_remarks').val(data.remarks);

            $('#editRateModal').modal('show');
        });
    });

    // reset modal on close
    $('#editRateModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>
@endpush
