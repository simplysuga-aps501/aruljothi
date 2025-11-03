<!-- Add Transport Office Modal -->
<div class="modal fade" data-focus="false" id="addOfficeModal" tabindex="-1" role="dialog" aria-labelledby="addOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('tp_offices.store') }}" method="POST" id="addOfficeForm" class="needs-validation">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Transport Office</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Office Name -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Office Name <span class="text-danger">*</span></label>
                                <x-adminlte-input name="name" id="name" required minlength="3" maxlength="50"
                                    placeholder="Enter office name" />
                                <div class="invalid-feedback">Please enter a valid office name (min 3 characters).</div>
                            </div>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_contact_person">Contact Person <span class="text-danger">*</span></label>
                                <x-adminlte-input name="contact_person" id="contact_person" minlength="3" maxlength="50"
                                    placeholder="Optional contact name" required/>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <x-adminlte-input name="phone" id="phone" required pattern="[0-9]{10}" maxlength="10"
                                    placeholder="10-digit phone number" />
                                <div class="invalid-feedback">Enter a valid 10-digit phone number.</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <x-adminlte-input name="email" id="email" type="email"
                                    placeholder="Optional email address" />
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                        </div>

                        <!-- GST Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gst_number">GST Number</label>
                                <x-adminlte-input name="gst_number" id="gst_number"
                                    placeholder="22AAAAA0000A1Z5"
                                    pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" />
                                <small class="form-text text-muted">Optional — format: 22AAAAA0000A1Z5</small>
                                <div class="invalid-feedback">Enter a valid GST number format.</div>
                            </div>
                        </div>

                        <!-- Pincode -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pincode">Pincode <span class="text-danger">*</span></label>
                                <x-adminlte-input name="pincode" id="pincode" required maxlength="6"
                                    placeholder="Enter 6-digit pincode"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');" />
                                <input type="hidden" name="location_id" id="location_id">
                                <div class="invalid-feedback">Enter a valid 6-digit pincode.</div>
                            </div>
                            <div class="pincode_alert text-danger text-sm" style="display:none;"></div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <x-adminlte-input name="location" id="location" placeholder="Auto-filled location" readonly required/>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <x-adminlte-textarea name="address" id="address" rows="2"
                                    placeholder="Optional full address" />
                            </div>
                        </div>

                       <!-- State -->
                       <div class="col-md-6">
                           <div class="form-group">
                               <label for="stateSelect">Select State</label>
                               <select id="stateSelect" class="form-control">
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
                               <label for="districtSelect">Select District</label>
                               <select id="districtSelect" class="form-control">
                                   <option value="">-- Select District --</option>
                               </select>
                           </div>
                       </div>

                       <!-- Selected Pills -->
                       <div class="col-md-12 mt-2">
                           <label>Preferred Districts</label>
                           <div id="selectedDistrictsContainer" class="border rounded p-2">
                               <small class="text-muted">No districts selected yet.</small>
                           </div>
                       </div>

                       <!-- Hidden input for form submission -->
                       <input type="hidden" name="preferred_districts" id="preferredDistrictsInput" />
                   </div>
               </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="clearOfficeForm">Clear</button>
                    <button type="submit" class="btn btn-primary">Add Office</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    initOfficePincodeAutocomplete("#pincode", "#location", "#location_id", ".pincode_alert");

    window.addOfficeSelector = initOfficeLocationSelector({
        stateSelect: '#stateSelect',
        districtSelect: '#districtSelect',
        container: '#selectedDistrictsContainer',
        hiddenInput: '#preferredDistrictsInput'
    });
});
</script>
@endpush
