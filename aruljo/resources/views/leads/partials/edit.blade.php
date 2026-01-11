<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" role="dialog" aria-labelledby="editLeadModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
        <form id="editLeadForm" method="POST" action="">
            @csrf
            @method('PUT')

                <!-- ============================ MODAL HEADER ============================ -->
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lead</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- ============================ MODAL BODY ============================ -->
                <div class="modal-body">
                    <div class="row">

                        <!-- Platform -->
                        <div class="col-md-4">
                            <x-adminlte-select name="platform" label="Platform" fgroup-class="mb-3" required>
                                <option value="">Select Platform</option>
                                @foreach ($platforms as $platform)
                                    <option value="{{ $platform }}">{{ $platform }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <!-- Lead Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="lead_date" label="Lead Date" type="datetime-local"
                                fgroup-class="mb-3" required />
                        </div>

                        <!-- Item Searched -->
                        <div class="col-md-4">
                            <x-adminlte-input name="platform_keyword" label="Item Searched" placeholder="Item Searched"
                                fgroup-class="mb-3" />
                        </div>

                        <!-- Buyer Name -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_name" label="Buyer Name" placeholder="Name"
                                fgroup-class="mb-3" required />
                        </div>

                        <!-- Buyer Contact -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_contact" label="Buyer Contact" placeholder="Phone"
                                fgroup-class="mb-3" required pattern="[0-9]{10}"
                                title="Enter a valid 10-digit phone number" />
                        </div>

                        <!-- Buyer Location -->
                        <div class="col-md-4">
                            <x-adminlte-input name="buyer_location" id="edit_buyer_location" class="buyer_location"
                                label="Buyer Location" fgroup-class="mb-3" />
                        </div>


                        <!-- ============================ PRODUCTS ============================ -->
                        <div class="col-md-12 product-pills-container">
                            <label>Products</label>
                            <div class="row mb-2 g-2">
                                <div class="col-md-8">
                                    <input type="text" class="form-control product-search"
                                        placeholder="Type product name">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control product-qty" placeholder="Qty"
                                        min="1">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100 product-add">Add</button>
                                </div>
                            </div>

                            <!-- Error Alert -->
                            <div class="alert alert-danger product-alert d-none" role="alert"></div>

                            <!-- Pills Container -->
                            <div class="product-pills mb-2 p-2"
                                style="border:1px solid #d2d6de; border-radius:5px; display:flex; flex-wrap:wrap; gap:5px;">
                            </div>


                            <!-- Hidden textarea for storing product list -->
                            <textarea name="product_detail" class="d-none product-detail" rows="2">{{ old('product_detail') }}</textarea>
                        </div>

                        <!-- ============================ Delivery Location ============================ -->

                        <div class="col-md-2">
                            <x-adminlte-input name="pincode" label="Enter Pincode" placeholder="Enter Pincode"
                                fgroup-class="mb-3" maxlength="6" id="edit_pincode_input" class="pincode_input"
                                type="text" oninput="this.value=this.value.replace(/[^0-9]/g,'');" />
                            <input type="hidden" name="delivery_location_id" id="edit_delivery_location_id"
                                class="delivery_location_id">
                        </div>

                        <!-- Delivery Location (auto-filled) -->
                        <div class="col-md-6">
                            <x-adminlte-input name="delivery_location" class="delivery_location"
                                label="Delivery Location" placeholder="Delivery location will appear here"
                                fgroup-class="mb-3" id="edit_delivery_location" readonly />
                        </div>

                        <!-- Distance & Duration -->
                        <div class="col-md-4">
                            <label for="edit_distance" class="text-dark">Distance from Mfg Unit</label>
                            <div class="input-group mb-3">
                                {{-- Distance --}}
                                <input type="text" id="edit_distance_km" name="distance_km"
                                    placeholder="Distance" class="form-control editable_field distance_km" readonly
                                    value="{{ old('distance_km', $lead->distance ?? '') }}"
                                    style="background-color: #d1ecf1; color: #0c5460;">
                                <span class="input-group-text">km</span>

                                {{-- Duration --}}
                                <input type="text" id="edit_duration_minutes" name="duration_minutes"
                                    placeholder="Duration" class="form-control editable_field duration_minutes"
                                    readonly value="{{ old('duration_minutes', $lead->duration ?? '') }}"
                                    style="background-color: #d1ecf1; color: #0c5460;">
                                <span class="input-group-text">mins</span>
                            </div>

                            <!-- Shared Alert Container -->
                            <div class="distance_alert text-danger" style="display:none;"></div>
                            <div id="edit_loader" class="loader text-center my-1" style="display:none;">
                                <i class="fas fa-spinner fa-spin fa-lg text-primary"></i>
                                <p class="mt-1 mb-0" style="font-size: 0.8rem;">Calculating...</p>
                            </div>
                        </div>
                        {{-- Transport Quote Section --}}

                        <!-- Estimated Cost -->
                        <div class="col-md-4">
                            <label for="estimated_cost">Estimated Cost (₹)</label>
                            <div class="input-group mb-3">
                                <input type="text" id="estimated_cost" name="estimated_cost" class="form-control"
                                    placeholder="Estimated Cost" readonly>
                                <div class="input-group-append">
                                    <span id="toggle_calc_details" class="input-group-text" style="cursor:pointer;"
                                        title="View calculation details">
                                        <i class="fas fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Draft Quote Button -->
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="calculate_quote_btn">
                                <i class="fas fa-calculator"></i> Calculate Draft Quote
                            </button>
                        </div>
                        <div class="quote_alert text-danger" style="display:none;"></div>
                      <!-- ============================ QUOTE CALCULATION DETAILS ============================ -->
                      <div class="col-12 mt-3">
                          <div class="collapse" id="calcDetailsCollapse">
                              <div class="card shadow-sm border-0 bg-light quote-card">
                                  <div class="card-body p-3">
                                      <div class="table-responsive" id="calcDetailsBody">
                                          {{-- The generated $details_html from QuoteCalculatorService will be injected here --}}
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>

                        <!-- Expected Delivery Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="expected_delivery_date" label="Expected Delivery Date"
                                type="date" min="{{ date('Y-m-d') }}" data-output="edit_delivery_days_left"
                                fgroup-class="mb-3" />
                            <small id="edit_delivery_days_left" class="text-muted"></small>
                        </div>

                        <!-- Follow-up Date -->
                        <div class="col-md-4">
                            <x-adminlte-input name="follow_up_date" label="Follow-up Date" type="date"
                                min="{{ date('Y-m-d') }}" data-output="edit_followup_days_left"
                                fgroup-class="mb-3" />
                            <small id="edit_followup_days_left" class="text-muted"></small>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-4">
                            <x-adminlte-select name="assigned_to" label="Assigned To" fgroup-class="mb-3">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <!-- Tags -->
                        <div class="col-md-4">
                            <label for="tags" class="text-dark">Tags</label>
                            <select id="tags" name="tags[]" multiple class="form-control">
                                @foreach ($allTags as $tag)
                                    <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <x-adminlte-select name="status" label="Status" fgroup-class="mb-3" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <div class="col-md-2">
                            <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                            <button type="button" class="btn btn-success w-100" id="sendStatusWhatsappBtn">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                            <button type="button" class="btn btn-secondary w-100" id="copy_whatsapp_text">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <!-- Current Remark -->
                        <div class="col-md-12">
                            <x-adminlte-input name="current_remark" label="New Remark" placeholder="Add a remark"
                                fgroup-class="mb-3" required />
                        </div>

                        <!-- Past Remarks -->
                        <div class="col-md-12">
                            <x-adminlte-textarea name="past_remarks" label="Past Remarks" rows="4"
                                fgroup-class="mb-3" disabled />
                        </div>

                    </div>
                </div>

                <!-- ============================ MODAL FOOTER ============================ -->
                <div class="modal-footer">
                    <x-adminlte-button type="submit" label="Update Lead" theme="primary" />
                </div>
                <input type="hidden" name="tab" id="editLeadTab" value="">
        </form>
    </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            var products = @json($productsArray);
            // Open modal with AJAX
            $(document).on('click', '.open-edit-lead-modal', function() {
                const leadId = $(this).data('lead-id');
                const modal = $('#editLeadModal');
                const form = $('#editLeadForm');

                // ===== RESET MODAL =====
                modal.find('input:not([type=hidden]), select, textarea').val('');
                modal.find('.product-pills').empty();
                modal.find('.product-alert').addClass('d-none').text('');
                modal.find('.quote_alert').hide().html('');
                modal.find('input[name="quote_edit_data"]').val('');

                // 🔹 Reset previous quote section completely
                modal.find('#calcDetailsBody').empty();                 // clear quote table
                modal.find('#calcDetailsCollapse').collapse('hide');    // collapse quote details
                modal.find('#estimated_cost').val('');                  // clear estimated cost
                modal.find('#calculate_quote_btn').text("Draft Quote"); // reset button text

                 // Get current tab from URL
                const urlParams = new URLSearchParams(window.location.search);
                const currentTab = urlParams.get('tab') || 'active';

                // Store it in hidden field
                $('#editLeadTab').val(currentTab);

                $.get(`/leads/${leadId}/edit`, function(data) {
                    form.attr('action', `/leads/${leadId}`);

                    // ===== FILL FIELDS =====
                    modal.find('input[name="buyer_name"]').val(data.buyer_name);
                    modal.find('input[name="buyer_contact"]').val(data.buyer_contact);
                    modal.find('input[name="lead_date"]').val(data.lead_date);
                    modal.find('input[name="pincode"]').val(data.pincode);
                    modal.find('#edit_buyer_location').val(data.buyer_location);
                    modal.find('#edit_distance_km').val(data.distance_km);
                    modal.find('#edit_duration_minutes').val(data.duration_minutes);
                    modal.find('select[name="platform"]').val(data.platform);
                    modal.find('input[name="platform_keyword"]').val(data.platform_keyword);
                    modal.find('input[name="delivery_location"]').val(data.delivery_location);
                    modal.find('input[name="delivery_location_id"]').val(data.delivery_location_id);
                    modal.find('input[name="expected_delivery_date"]').val(data
                        .expected_delivery_date);
                    modal.find('input[name="follow_up_date"]').val(data.follow_up_date);
                    modal.find('select[name="status"]').val(data.status);
                    modal.find('select[name="assigned_to"]').val(data.assigned_to);
                    modal.find('textarea[name="current_remark"]').val('');
                    modal.find('select[name="tags[]"]').val(data.tags);

                    // Product detail
                    modal.find('textarea[name="product_detail"]').val(Array.isArray(data
                            .product_detail) ? data.product_detail.join('\n') : data
                        .product_detail || '');

                    // Past remarks
                    modal.find('textarea[name="past_remarks"]').val(Array.isArray(data
                        .past_remarks) ? data.past_remarks.join('\n') : data.past_remarks || '');

                    // ===== INIT SCRIPTS =====
                    initProductPills(".product-pills-container", products);
                    modal.find('.product-search').autocomplete({
                        source: products.map(p => p.name),
                        minLength: 1,
                        appendTo: "#editLeadModal"
                    });
                    initTagMultiselect(modal.find('#tags'));
                    initDaysCalculation(modal);
                    initPincodeAutocomplete(
                        "#edit_pincode_input",
                        "#edit_delivery_location",
                        "#edit_delivery_location_id",
                        "#edit_distance_km",
                        "#edit_duration_minutes",
                        "#edit_loader",
                        "#editLeadModal"
                    );
                    modal.modal('show');
                    // Initialize quote calculator after modal is shown
                    initDistanceDurationEditable();
                    initQuoteCalculator('#editLeadModal');

                    // 🔹 Load related quotation details (read-only mode)
                    // 🔹 Load related quotation details (read-only mode)
                    // 🔹 Load related quotation details (read-only mode)
                    if (data.quotation_id) {
                        let dataUrl = `/quotations/${data.quotation_id}/data`;
                        if (data.version_id) dataUrl += `?version_id=${data.version_id}`;

                        $.get(dataUrl, function (quoteData) {
                            const $calcBody = modal.find('#calcDetailsBody');

                            if (quoteData.versionData) {
                                modal.find('#calculate_quote_btn').text("Recalculate Quote");

                                // Render read-only quotation view
                                renderReadOnlyQuote(quoteData.versionData, $calcBody);
                                modal.find('#calcDetailsCollapse').collapse('show');

                                if (quoteData.versionData.net_total !== undefined) {
                                    modal.find('#estimated_cost').val(quoteData.versionData.net_total);
                                }

                            } else {
                                // 🟡 Quotation exists but has no version data
                                const createUrl = `/quotations/create?lead_id=${leadId}`;
                                $calcBody.html(`
                                    <div class="alert alert-info text-center mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        No quote has been generated yet.<br>
                                        Click <strong>Draft Quote</strong> to create one, or
                                        <a href="${createUrl}" target="_blank" rel="noopener"
                                           class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-file-invoice"></i> Create Quotation
                                        </a>
                                    </div>
                                `);
                                modal.find('#calcDetailsCollapse').collapse('show');
                                modal.find('#estimated_cost').val('');
                                modal.find('#calculate_quote_btn').text("Draft Quote");
                            }
                        }).fail(function() {
                            console.error('Failed to load quotation data.');
                            modal.find('.quote_alert').show().text('Failed to load quotation details.');
                        });

                    } else {
                        // 🟡 Lead has no quotation at all
                        const createUrl = `/quotations/create?lead_id=${leadId}`;
                        modal.find('#calcDetailsBody').html(`
                          <div class="alert bg-light border text-center mb-0">
                              <i class="fas fa-info-circle text-secondary"></i>
                              No quote has been generated yet.<br>
                              Click <strong>Draft Quote</strong> for quick quote, or
                              <a href="${createUrl}" target="_blank" rel="noopener"> Create Quotation
                              </a>
                          </div>
                        `);
                        modal.find('#calcDetailsCollapse').collapse('show');
                        modal.find('#estimated_cost').val('');
                        modal.find('#calculate_quote_btn').text("Draft Quote");
                    }

                    $(document).on('change', '#editLeadModal select[name="status"]', function() {
                        const selected = $(this).val();
                        if (selected === 'Cancelled') {
                            const send = confirm("Please send a WhatsApp message to inform the customer about cancellation.\n\nDo you want to open WhatsApp now?");
                            if (send) {
                                $('#sendStatusWhatsappBtn').trigger('click');
                            }
                        }
                    });

                });
            });

        });
    </script>
@endpush
