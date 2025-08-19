<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" role="dialog" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
       <form id="editLeadForm" method="POST" action="">
           @csrf
           @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lead</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Platform -->
                        <div class="col-md-4">
                            <x-adminlte-select name="platform" label="Platform" fgroup-class="mb-3" required>
                                <option value="">Select Platform</option>
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform }}">{{ $platform }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <!-- Lead Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="lead_date" label="Lead Date" type="datetime-local" fgroup-class="mb-3" required />
                        </div>

                        <!-- Platform Keyword -->
                        <div class="col-md-4">
                            <x-adminlte-input name="platform_keyword" label="Item Searched" placeholder="Item Searched" fgroup-class="mb-3" />
                        </div>

                        <!-- Buyer Name -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_name" label="Buyer Name" placeholder="Name" fgroup-class="mb-3" required />
                        </div>

                        <!-- Buyer Contact -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_contact" label="Buyer Contact" placeholder="Phone" fgroup-class="mb-3" required pattern="[0-9]{10}" title="Enter a valid 10-digit phone number" />
                        </div>

                        <!-- Buyer Location -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_location" label="Buyer Location" placeholder="City or Area" fgroup-class="mb-3" />
                        </div>

                        <!-- Product Detail -->
                        <div class="col-md-12">
                            <x-adminlte-textarea name="product_detail" label="Product Detail" fgroup-class="mb-3" rows=2 placeholder="Description..." />
                        </div>

                        <!-- Delivery Location -->
                        <div class="col-md-4">
                            <x-adminlte-input name="delivery_location" label="Delivery Location" fgroup-class="mb-3" />
                        </div>

                        <!-- Expected Delivery Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="expected_delivery_date"
                                              label="Expected Delivery Date"
                                              type="date"
                                              min="{{ date('Y-m-d') }}"
                                              data-output="edit_delivery_days_left"
                                              fgroup-class="mb-3" />
                            <small id="edit_delivery_days_left" class="text-muted"></small>
                        </div>

                        <!-- Followup Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="follow_up_date"
                                              label="Followup Date"
                                              type="date"
                                              min="{{ date('Y-m-d') }}"
                                              data-output="edit_followup_days_left"
                                              fgroup-class="mb-3" />
                            <small id="edit_followup_days_left" class="text-muted"></small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <x-adminlte-select name="status" label="Status" fgroup-class="mb-3" required>
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-4">
                            <x-adminlte-select name="assigned_to" label="Assigned To" fgroup-class="mb-3">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <!-- Tags -->
                        <div class="col-md-4">
                            <label for="tags" class="text-dark">Tags</label>
                            <select id="tags" name="tags[]" multiple class="form-control">
                                @foreach($allTags as $tag)
                                    <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Current Remark -->
                        <div class="col-md-12">
                            <x-adminlte-input name="current_remark" label="New Remark" placeholder="Add a remark" fgroup-class="mb-3" required />
                        </div>

                        <!-- Past Remarks -->
                        <div class="col-md-12">
                            <x-adminlte-textarea name="past_remarks" label="Past Remarks" rows=4 fgroup-class="mb-3" disabled />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <x-adminlte-button type="submit" label="Update Lead" theme="primary" />
                </div>
            </div>
        </form>
    </div>
</div>
