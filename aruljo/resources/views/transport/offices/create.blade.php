<!-- Add Transport Office Modal -->
<div class="modal fade" id="addOfficeModal" tabindex="-1" role="dialog" aria-labelledby="addOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('tp_offices.store') }}" method="POST" id="addOfficeForm" class="needs-validation" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Transport Office</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <!-- Office Name -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="name" label="Office Name" required minlength="3"
                                    placeholder="Enter office name" />
                                <div class="invalid-feedback">Please enter a valid office name (min 3 characters).</div>
                            </div>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="contact_person" label="Contact Person"
                                    placeholder="Optional contact name" />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="phone" label="Phone" required
                                    pattern="[0-9]{10}" maxlength="10"
                                    placeholder="10-digit phone number" />
                                <div class="invalid-feedback">Enter a valid 10-digit phone number.</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="email" type="email" label="Email"
                                    placeholder="Optional email address" />
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                        </div>

                        <!-- GST Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="gst_number" label="GST Number"
                                    placeholder="22AAAAA0000A1Z5"
                                    pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" />
                                <small class="form-text text-muted">Optional — format: 22AAAAA0000A1Z5</small>
                                <div class="invalid-feedback">Enter a valid GST number format.</div>
                            </div>
                        </div>

                        <!-- Pincode -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="pincode" id="pincode" label="Pincode" required
                                    maxlength="6" pattern="[0-9]{6}"
                                    placeholder="Enter 6-digit pincode"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');" />
                                <input type="hidden" name="location_id" id="location_id">
                                <div class="invalid-feedback">Enter a valid 6-digit pincode.</div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-input name="location" id="location" label="Location"
                                    placeholder="Auto-filled location" readonly />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-adminlte-textarea name="address" label="Address" rows="2"
                                    placeholder="Optional full address" />
                            </div>
                        </div>

                        <!-- State -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Select State <span class="text-danger">*</span></label>
                                <select name="state" id="state" class="form-control" required>
                                    <option value="">-- Select State --</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->state }}">{{ $state->state }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a state.</div>
                            </div>
                        </div>

                        <!-- Preferred Districts -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="district">Preferred Districts</label>
                                <select name="preferred_districts[]" id="district" multiple class="form-control">
                                    {{-- Districts loaded dynamically --}}
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

            {{-- Buttons --}}
            <div class="form-group row mt-2 px-4">
                <div class="col-12 col-md-6">
                    <x-adminlte-button label="Cancel" type="cancel" theme="secondary" icon="fas fa-save" class="btn-block"/>
                </div>
                <div class="col-12 col-md-6">
                    <x-adminlte-button label="Submit" type="submit" theme="primary" icon="fas fa-save" class="btn-block"/>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>


@push('css')
<!-- Bootstrap Multiselect CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/css/bootstrap-multiselect.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<style>
    .multiselect-container {
        max-height: 250px;
        overflow-y: auto !important;
    }
    .ui-autocomplete {
        z-index: 99999 !important;
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/js/bootstrap-multiselect.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize State and District multiselects
    $('#state').multiselect({
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 250,
        nonSelectedText: 'Select State',
        multiple: false,
        buttonWidth: '100%'
    });

    $('#district').multiselect({
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        maxHeight: 250,
        includeSelectAllOption: true,
        nonSelectedText: 'Select Districts',
        numberDisplayed: 2,
        buttonWidth: '100%'
    });

    // Load districts dynamically based on state
    $('#state').on('change', function() {
        let state = $(this).val();
        $('#district').html('<option>Loading...</option>').multiselect('rebuild');
        if (state) {
            $.get('/transport/get-districts/' + state, function(data) {
                $('#district').empty();
                data.forEach(d => $('#district').append(`<option value="${d.district}">${d.district}</option>`));
                $('#district').multiselect('rebuild');
            });
        } else {
            $('#district').empty().multiselect('rebuild');
        }
    });

    // Initialize autocomplete for Pincode
    initOfficePincodeAutocomplete('#pincode', '#location', '#location_id', '#pincode-error');

    // Rebuild multiselects when modal opens
    $('#addOfficeModal').on('shown.bs.modal', function() {
        $('#state, #district').multiselect('rebuild');
    });
});

function initOfficePincodeAutocomplete(pincodeInput, locationInput, locationIdInput, errorDiv) {
    let stateCache = '', districtCache = '';

    $(pincodeInput).autocomplete({
        minLength: 6,
        appendTo: "#addOfficeModal",
        source: function(request, response) {
            let pincode = request.term.trim();
            if (pincode.length === 6 && /^\d+$/.test(pincode)) {
                $.ajax({
                    url: '{{ route("distance.byPincode") }}',
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
                            $(errorDiv).addClass('d-none');
                        } else {
                            response([]);
                            $(errorDiv).text('Invalid pincode!').removeClass('d-none');
                            $(locationInput).val('');
                            $(locationIdInput).val('');
                        }
                    },
                    error: function() {
                        response([]);
                        $(errorDiv).text('Error fetching pincode info.').removeClass('d-none');
                        $(locationInput).val('');
                        $(locationIdInput).val('');
                    }
                });
            } else {
                response([]);
            }
        },
        select: function(event, ui) {
            if (!ui.item || !ui.item.id) {
                $(errorDiv).text('Please select a valid location.').removeClass('d-none');
                $(locationInput).val('');
                $(locationIdInput).val('');
                return false;
            }

            let pincode = $(pincodeInput).val().trim();
            let fullLocation = `${ui.item.value}, ${districtCache}, ${stateCache}${pincode ? ' - ' + pincode : ''}`;

            $(locationInput).val(fullLocation);
            $(locationIdInput).val(ui.item.id);
            $(errorDiv).addClass('d-none');
        }
    });

    $(pincodeInput).on("input", function() {
        if ($(this).val().trim() === "") {
            $(locationInput).val("");
            $(locationIdInput).val("");
            $(errorDiv).addClass('d-none');
        }
    });
}

</script>
@endpush
