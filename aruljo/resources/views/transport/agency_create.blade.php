<!-- Add Agency Modal -->
<div class="modal fade" id="addAgencyModal" tabindex="-1" role="dialog" aria-labelledby="addAgencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('transport.agency.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAgencyModalLabel">Add Truck Agency</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Agency Name -->
                    <div class="form-group">
                        <label for="agency_name">Agency Name</label>
                        <input type="text" class="form-control" name="agency_name" id="agency_name" required>
                    </div>

                    <!-- Truck Type -->
                    <div class="form-group">
                        <label for="truck_type_id">Truck Type</label>
                        <select name="truck_type_id" id="truck_type_id" class="form-control" required>
                            <option value="">Select Truck Type</option>
                            @foreach($truckTypes as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->name }} ({{ $truck->capacity_kg }}kg)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- State -->
                    <div class="form-group">
                        <label for="state">Select State</label>
                        <select name="state" id="state" class="form-control" required>
                            <option value="">-- Select State --</option>
                            @foreach($states as $state)
                                <option value="{{ $state->state }}">{{ $state->state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- District -->
                    <div class="form-group">
                        <label for="district">Select District</label>
                        <select name="district" id="district" class="form-control" required>
                            <option value="">-- Select District --</option>
                        </select>
                    </div>

                    <!-- Places -->
                    <div class="form-group">
                        <label for="places">Select Places</label>
                        <select name="places[]" id="places" class="form-control" multiple required>
                            <!-- Options loaded via AJAX -->
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple places</small>
                        <div><input type="checkbox" id="selectAllPlaces"> Select All</div>
                    </div>

                    <!-- Rate -->
                    <div class="form-group">
                        <label for="rate">Fixed Rate (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="rate" id="rate" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Agency</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize multiselect on empty select
    $('#places').multiselect({
        includeSelectAllOption: true,
        buttonWidth: '100%',
        nonSelectedText: 'Select Places',
        numberDisplayed: 2,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true
    });

    // Load districts based on state
    $('#state').change(function() {
        var state = $(this).val();
        $('#district').html('<option>Loading...</option>');
        $('#places').html('');
        $('#places').multiselect('rebuild'); // reset multiselect

        if(state) {
            $.get('/transport/get-districts/' + state, function(data) {
                var options = '<option value="">-- Select District --</option>';
                data.forEach(function(d) {
                    options += '<option value="'+d.district+'">'+d.district+'</option>';
                });
                $('#district').html(options);
            });
        } else {
            $('#district').html('<option value="">-- Select District --</option>');
        }
    });

    // Load places based on district
    $('#district').change(function() {
        var state = $('#state').val();
        var district = $(this).val();

        if(state && district) {
            $.get('/transport/get-places/' + state + '/' + district, function(data) {
                var options = '';
                data.forEach(function(p) {
                    options += '<option value="'+p.id+'">'+p.place+' - '+p.pincode+'</option>';
                });
                $('#places').html(options);

                // Destroy any previous multiselect first
                if($('#places').data('multiselect')) {
                    $('#places').multiselect('destroy');
                }

                // Initialize multiselect AFTER options are loaded
                $('#places').multiselect({
                    includeSelectAllOption: true,
                    buttonWidth: '100%',
                    nonSelectedText: 'Select Places',
                    numberDisplayed: 2,
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true
                });
            });
        } else {
            $('#places').html('');
        }
    });


    // Select All functionality
    $('#selectAllPlaces').change(function() {
        $('#places option').prop('selected', this.checked);
        $('#places').multiselect('refresh');
    });
});
</script>
@endpush
