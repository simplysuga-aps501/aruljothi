<!-- Edit Transport Office Modal -->
<div class="modal fade" data-focus="false" id="editOfficeModal" tabindex="-1" role="dialog" aria-labelledby="editOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editOfficeForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Transport Office</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Office Name -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Office Name <span class="text-danger">*</span></label>
                                <x-adminlte-input name="name" id="edit_name" required minlength="3" maxlength="50"
                                    placeholder="Enter office name" />
                                <div class="invalid-feedback">Please enter a valid office name (min 3 characters).</div>
                            </div>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_contact_person">Contact Person <span class="text-danger">*</span></label>
                                <x-adminlte-input name="contact_person" id="edit_contact_person" required minlength="3" maxlength="50" />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone">Phone <span class="text-danger">*</span></label>
                                <x-adminlte-input name="phone" id="edit_phone" required pattern="[0-9]{10}" maxlength="10" />
                                <div class="invalid-feedback">Enter a valid 10-digit phone number.</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">Email</label>
                                <x-adminlte-input name="email" id="edit_email" type="email" />
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                        </div>

                        <!-- GST Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_gst_number">GST Number</label>
                                <x-adminlte-input name="gst_number" id="edit_gst_number"
                                    pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" />
                                <small class="form-text text-muted">Optional — format: 22AAAAA0000A1Z5</small>
                                <div class="invalid-feedback">Enter a valid GST number.</div>
                            </div>
                        </div>

                        <!-- Pincode -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_pincode">Pincode <span class="text-danger">*</span></label>
                                <x-adminlte-input name="pincode" id="edit_pincode" required maxlength="6"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');" />
                                <input type="hidden" name="location_id" id="edit_location_id">
                                <div class="invalid-feedback">Enter a valid 6-digit pincode.</div>
                            </div>
                            <div class="pincode_alert text-danger text-sm" style="display:none;"></div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_location">Location <span class="text-danger">*</span></label>
                                <x-adminlte-input name="location" id="edit_location" readonly required/>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_address">Address</label>
                                <x-adminlte-textarea name="address" id="edit_address" rows="2" />
                            </div>
                        </div>

                       <!-- State -->
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="edit_stateSelect">Select State</label>
                              <select id="edit_stateSelect" class="form-control">
                                  <option value="">-- Select State --</option>
                                  @foreach($states as $state)
                                      <option value="{{ $state->state }}">{{ $state->state }}</option>
                                  @endforeach
                              </select>
                          </div>
                      </div>

                      <!-- District -->
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="edit_districtSelect">Select District</label>
                              <select id="edit_districtSelect" class="form-control">
                                  <option value="">-- Select District --</option>
                              </select>
                          </div>
                      </div>

                      <!-- Selected Pills -->
                      <div class="col-md-12 mt-2">
                          <label>Preferred Districts</label>
                          <div id="edit_selectedDistrictsContainer" class="border rounded p-2">
                              <small class="text-muted">No districts selected yet.</small>
                          </div>
                      </div>

                      <!-- Hidden input for form submission -->
                      <input type="hidden" name="preferred_districts" id="edit_preferredDistrictsInput" />

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Update Office</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(document).ready(function() {
    // ✅ Initialize the district selector for the edit modal
    window.editOfficeSelector = initOfficeLocationSelector({
        stateSelect: '#edit_stateSelect',
        districtSelect: '#edit_districtSelect',
        container: '#edit_selectedDistrictsContainer',
        hiddenInput: '#edit_preferredDistrictsInput'
    });

    // 🔥 Click handler for edit button
    $(document).on('click', '.open-edit-modal', function() {
        const id = $(this).data('id');

        $.get(`/transport/offices/${id}/edit`, function(response) {
            const office = response.office;

            // Fill simple fields
            $('#edit_id').val(office.id);
            $('#edit_name').val(office.name);
            $('#edit_contact_person').val(office.contact_person);
            $('#edit_phone').val(office.phone);
            $('#edit_email').val(office.email);
            $('#edit_gst_number').val(office.gst_number);
            $('#edit_location_id').val(office.location_id);
            $('#edit_address').val(office.address);

            // Prefill location
            if (office.location && typeof office.location === 'object') {
                const loc = office.location;
                const fullLocation = `${loc.place || ''}, ${loc.district || ''}, ${loc.state || ''} - ${loc.pincode || ''}`.trim();
                $('#edit_pincode').val(loc.pincode || '');
                $('#edit_location').val(fullLocation);
            } else {
                $('#edit_location').val(office.location || '');
            }

            // Prefill preferred districts
            if (office.preferred_districts) {
                let districts = Array.isArray(office.preferred_districts)
                    ? office.preferred_districts
                    : JSON.parse(office.preferred_districts);
                editOfficeSelector.prefill(districts);
            } else {
                editOfficeSelector.clearAll();
            }

            // ✅ Set the form action
            $('#editOfficeForm').attr('action', `/transport/offices/${id}`);
            initOfficePincodeAutocomplete("#edit_pincode", "#edit_location", "#edit_location_id", ".pincode_alert");
            $('#editOfficeModal').modal('show');
        });
    });
});
</script>
@endpush
