@extends('adminlte::page')

@section('title', 'Edit Quotation')

@section('plugins.Select2', true)

@section('content_header')
    <h1>Edit Quotation</h1>
@stop

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">
        <form action="{{ route('quotations.store') }}" method="POST" id="createVersionQuotationForm">
        @csrf
            <div class="card-body">
                <div class="row">
                <!-- ==================== Quote Version ==================== -->
                <div class="col-12 mb-3">
                    <div class="border rounded p-3 bg-light">
                        <div class="row align-items-center">
                            <label for="version_id" class="col-md-6 col-form-label fw-bold">
                                Select Version
                            </label>
                            <div class="col-md-6">
                                <select class="form-select" onchange="location = this.value;">
                                    @foreach($quotation->versions->sortByDesc('created_at') as $v)
                                        <option value="{{ route('quotations.create-version', [
                                            'id' => $quotation->id,
                                            'version_id' => $v->id
                                        ]) }}"
                                        {{ $v->id === $version->id ? 'selected' : '' }}>
                                            V{{ $v->version_number }} — {{ $v->created_at->format('d-M-Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== LEAD DETAILS (READ ONLY) ==================== --}}
                <div id="lead-details" class="border rounded p-3 bg-light mb-3 ">
                    <h6 class="mb-3 text-primary">Lead Information</h6>
                    <div class="row text-dark">
                        <div class="col-md-4 mb-1"><strong>Buyer Name:</strong> <span id="buyer_name_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Contact:</strong> <span id="buyer_contact_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Platform:</strong> <span id="platform_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Item Searched:</strong> <span id="platform_keyword_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Buyer Location:</strong> <span id="buyer_location_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Status:</strong> <span id="status_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Assigned To:</strong> <span id="assigned_to_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Expected Delivery:</strong> <span id="expected_delivery_text"></span></div>
                        <div class="col-md-4 mb-1"><strong>Follow-up Date:</strong> <span id="follow_up_text"></span></div>
                    </div>
                </div>

                {{-- ==================== MAIN QUOTATION SECTION ==================== --}}
                {{-- DELIVERY DETAILS --}}
                <div class="border rounded p-3 mb-3 bg-light ">
                    <h6 class="text-primary mb-2"><i class="fas fa-map-marker-alt"></i> Delivery Details</h6>
                    <div class="row">
                        <div class="col-md-2">
                            <x-adminlte-input name="pincode" id="quote_pincode_input" label="Enter Pincode"
                                placeholder="Enter Pincode" maxlength="6"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                class="pincode_input" />
                            <input type="hidden" name="delivery_location_id" id="quote_delivery_location_id">
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="delivery_location" id="quote_delivery_location"
                                label="Delivery Location" placeholder="Will auto-fill from pincode" readonly />
                        </div>

                        <div class="col-md-2">
                            <label for="quote_distance_km" class="text-dark">Distance</label>
                            <div class="input-group mb-3">
                                <input type="text" id="quote_distance_km" name="distance_km"
                                    placeholder="Distance"
                                    class="form-control distance_km editable_field"
                                    readonly style="background-color:#d1ecf1; color:#495057;">
                                <span class="input-group-text">km</span>
                            </div>
                            <div class="distance_alert text-danger small mt-1" style="display:none;"></div>
                        </div>

                        <div class="col-md-2">
                            <label for="quote_duration_minutes" class="text-dark">Duration</label>
                            <div class="input-group mb-3">
                                <input type="text" id="quote_duration_minutes" name="duration_minutes"
                                    placeholder="Duration"
                                    class="form-control duration_minutes editable_field"
                                    readonly style="background-color:#d1ecf1; color:#495057;">
                                <span class="input-group-text">mins</span>
                            </div>
                            <div class="distance_alert text-danger small mt-1" style="display:none;"></div>
                        </div>

                        <div class="col-md-12 text-center" id="quote_loader" style="display:none;">
                            <i class="fas fa-spinner fa-spin fa-lg text-primary"></i>
                            <p class="mt-1 mb-0" style="font-size:0.8rem;">Calculating distance...</p>
                        </div>
                    </div>
                </div>

                {{-- PRODUCTS --}}
                <div class="col-md-12 product-pills-container border rounded p-3 mb-3 bg-light ">
                    <h6 class="text-primary mb-2"><i class="fas fa-box"></i> Products</h6>
                    <div class="row mb-2 g-2">
                        <div class="col-md-8">
                            <input type="text" class="form-control product-search" placeholder="Type product name">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control product-qty" placeholder="Qty" min="1">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100 product-add">Add</button>
                        </div>
                    </div>

                    <div class="alert alert-danger product-alert d-none" role="alert"></div>

                    <div class="product-pills mb-2 p-2" style="border:1px solid #d2d6de; border-radius:5px; display:flex; flex-wrap:wrap; gap:5px;"></div>

                    <textarea name="product_detail" class="d-none product-detail" rows="2"></textarea>


                </div>

                {{-- QUOTE & COST DETAILS --}}
                <div class="col-md-12 border rounded p-3 mb-3 bg-light ">
                    <h6 class="text-primary mb-2"><i class="fas fa-calculator"></i> Quote Estimation</h6>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="estimated_cost">Estimated Cost (₹)</label>
                            <div class="input-group">
                                <input type="text" id="estimated_cost" name="estimated_cost" class="form-control" placeholder="Estimated Cost" readonly>
                                <div class="input-group-append">
                                    <span id="toggle_calc_details" class="input-group-text" style="cursor:pointer;"
                                          title="View calculation details">
                                        <i class="fas fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary w-100" id="calculate_quote_btn">
                                <i class="fas fa-calculator"></i> Draft Quote
                            </button>
                        </div>

                        <div class="col-md-2">
                            <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                            <button type="button" class="btn btn-secondary w-100" id="copy_quote_whatsapp">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>

                        <div class="col-md-2">
                            <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                            <button type="button" class="btn btn-success w-100" id="send_quote_whatsapp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                        </div>
                    </div>

                    <div class="quote_alert text-danger mt-2" style="display:none;"></div>
                    <p class="text-muted  mt-2">
                        <i class="fas fa-info-circle text-primary"></i>
                        If you change the <strong>distance</strong> or <strong>products</strong>, please click <strong>Draft Quote</strong> again to refresh the calculation table.
                    </p>
                    <div class="collapse mt-3" id="calcDetailsCollapse">
                        <div class="card shadow-sm border-0 bg-white quote-card">
                            <div class="card-body p-3">
                                <div class="table-responsive" id="calcDetailsBody">
                                    {{-- The generated $details_html from QuoteCalculatorService will be injected here --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ==================== CUSTOMER DETAILS ==================== --}}
                <div class="col-md-12 border rounded p-3 bg-light mt-3">
                    <h5 class="mb-3 text-primary">Customer Information</h5>

                    <div class="row">
                        {{-- Name --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="customer_name" id="customer_name"
                                label="Customer Name" placeholder="Enter customer name"/>
                        </div>

                        {{-- Contact --}}
                        <div class="col-md-4">
                            <x-adminlte-input
                                name="customer_contact"
                                id="customer_contact"
                                label="Contact Number"
                                placeholder="Enter 10-digit contact number"
                                pattern="\d{10}"
                                maxlength="10"
                                minlength="10"
                                inputmode="numeric"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                            />
                        </div>

                        {{-- GST Number --}}
                        <div class="col-md-4">
                            <x-adminlte-input
                                name="customer_gst_number"
                                id="customer_gst_number"
                                label="GST Number"
                                placeholder="GST Number (optional)"
                                pattern="^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}[1-9A-Za-z]{1}Z[0-9A-Za-z]{1}$"
                                title="Enter a valid 15-character GST Number (letters can be uppercase or lowercase)"
                                maxlength="15"
                            />
                        </div>

                        {{-- Address Line 1 --}}
                        <div class="col-md-6">
                            <x-adminlte-input name="customer_address_line1" id="customer_address_line1"
                                label="Address Line 1" placeholder="Enter address line 1 (e.g., Street, Building)"/>
                        </div>

                        {{-- Address Line 2 --}}
                        <div class="col-md-6">
                            <x-adminlte-input name="customer_address_line2" id="customer_address_line2"
                                label="Address Line 2" placeholder="Enter address line 2 (optional)"/>
                        </div>

                        {{-- District --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="customer_district" id="customer_district"
                                label="District" placeholder="Enter district"/>
                        </div>

                        {{-- State --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="customer_state" id="customer_state"
                                label="State" placeholder="Enter state"/>
                        </div>

                        {{-- Pincode --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="customer_pincode" id="customer_pincode"
                                label="Pincode" placeholder="Enter 6-digit pincode"
                                maxlength="6" minlength="6"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'');"/>
                        </div>
                    </div>
                </div>
                {{-- ==================== PDF DETAILS ==================== --}}
                <div class="col-md-12 border rounded p-3 bg-light mt-3 ">
                    <h6 class="mb-3 text-primary">Quotation / PDF Details</h6>

                    <div class="row">
                        <div class="col-md-3">
                            <x-adminlte-input type="date" name="pdf_date" id="pdf_date"
                                label="Quotation Date"/>
                        </div>

                        <div class="col-md-9">
                            <x-adminlte-input name="pdf_subject" id="pdf_subject"
                                label="Subject" placeholder="Quotation subject"/>
                        </div>
                        <div class="col-md-12">
                            @include('quotations.pdf-terms-builder')
                        </div>

                        <div class="col-md-12">
                            <x-adminlte-textarea name="pdf_delivery" id="pdf_delivery"
                                label="Delivery Terms" rows="3"/>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="quote_edit_data" id="quote_edit_data">
                <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
            </div>
        </div>
    </div>
        <div class="modal-footer flex-column flex-md-row justify-content-between align-items-center gap-2">
            <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
                Cancel
            </a>

            <div class="d-flex flex-column flex-md-row gap-2">
                <button type="button" class="btn btn-outline-info" id="preview_pdf_btn">
                    <i class="fas fa-eye"></i> Preview PDF
                </button>

                <a href="{{ route('quotations.download-version', ['quotation' => $quotation->id, 'version' => $version->id]) }}"
                   class="btn btn-danger">
                    <i class="fas fa-download"></i> Download PDF
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create New Version
                </button>
            </div>
        </div>

      </form>
    </div>
</div>
@include('partials.adminlte-alert-modal')

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
.product-cell {
    display: flex;
    align-items: center;
    gap: 6px;
}
.product-cell select {
    flex: 1; /* product select takes full width minus + button */
}
.product-cell .btn {
    flex-shrink: 0;
}
#truckTable .truck-subtotal td {
  background-color: #f8f9fa;
  font-weight: 600;
}
.qty-input, .req-input {
    width: 60px !important;
    text-align: center;
    padding: 2px 4px;
    margin-right: 8px;
}
.product-cell, .d-flex.align-items-center.gap-1 {
    gap: 4px;
}
.product-pills .pill
        {
            display: inline-flex;
            align-items: center;
            justify-content: space-between; /* Push icon to the right */
            max-width: 100%;
            word-break: break-word;
            white-space: normal;
            padding: 5px 10px;
            margin: 3px;
        }

        .product-pills .pill i
        {
            margin-left: 8px;
            cursor: pointer;
            flex-shrink: 0; /* Prevent icon from shrinking */
        }
        table td,
            .table th {
              min-width: 60px;  /* adjust as needed (80–120px usually works well) */
              white-space: nowrap;  /* prevents text wrapping, keeps layout consistent */
            }
            /* Make Truck Type & Product columns wider */
            #truckTable th:nth-child(2),
            #truckTable td:nth-child(2),
            #truckTable th:nth-child(4),
            #truckTable td:nth-child(4) {
              min-width: 130px;   /* increase or decrease as needed */
            }
        .modal-footer .btn + .btn,
        .modal-footer .d-flex .btn + .btn {
            margin-left: 0;
            margin-right: 0;
            margin-top: 0.5rem;
        }
        @media (min-width: 768px) {
            .modal-footer .d-flex.flex-md-row .btn + .btn {
                margin-top: 0;
                margin-left: 0.75rem;
            }
        }

</style>

@stop

@section('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

@include('shared_js.pincode-autocomplete')
@include('shared_js.product-autocomplete')
@include('shared_js.quote-calculate')
@include('shared_js.quote-distance-editable')
@include('shared_js.whatsapp-copy')
@include('shared_js.sweetalert')
@include('shared_js.mini-rate-modal')
@include('shared_js.preview-pdf')
<script>
$(document).ready(function () {
    const products = @json($productsArray);
    // ✅ Extract quotation ID from URL (e.g., /quotations/2/edit)
    const pathParts = window.location.pathname.split('/');
    const quotationId = pathParts[pathParts.indexOf('quotations') + 1];
    const urlParams = new URLSearchParams(window.location.search);
    const versionId = urlParams.get('version_id');
    const $container = $(document);

    if (quotationId) {
        let dataUrl = `/quotations/${quotationId}/data`;
        if (versionId) {
            dataUrl += `?version_id=${versionId}`;
        }
        $.get(dataUrl, function (data) {
            $('#quotation-main-section').removeClass('d-none');
            $('#lead-details').removeClass('d-none');

            $('#buyer_name_text').text(data.buyer_name || '-');
            $('#buyer_contact_text').text(data.buyer_contact || '-');
            $('#platform_text').text(data.platform || '-');
            $('#platform_keyword_text').text(data.platform_keyword || '-');
            $('#buyer_location_text').text(data.buyer_location || '-');
            $('#status_text').text(data.status || '-');
            $('#assigned_to_text').text(data.assigned_to || '-');
            $('#expected_delivery_text').text(data.expected_delivery_date || '-');
            $('#follow_up_text').text(data.follow_up_date || '-');

            $('#quote_pincode_input').val(data.pincode);
            $('#quote_delivery_location_id').val(data.delivery_location_id);
            $('#quote_delivery_location').val(data.delivery_location);
            $('#quote_distance_km').val(data.distance_km);
            $('#quote_duration_minutes').val(data.duration_minutes);
            $('#createVersionQuotationForm').find('textarea[name="product_detail"]').val(
                Array.isArray(data.product_detail)
                    ? data.product_detail.join('\n')
                    : data.product_detail || ''
            );
            $('#estimated_cost').val(data.versionData.net_total || '');
            // ===== PREFILL CUSTOMER FIELDS =====
            $('#customer_name').val(
                (data.versionData.customer?.name) || data.buyer_name || ''
            );

            $('#customer_contact').val(
                (data.versionData.customer?.phone) || data.buyer_contact || ''
            );

            $('#customer_gst_number').val(
                (data.versionData.customer?.gst_number) || ''
            );

            // 🏠 Address fields (two lines + district/state/pincode)
            $('#customer_address_line1').val(
                (data.versionData.customer?.address_line1) || ''
            );

            $('#customer_address_line2').val(
                (data.versionData.customer?.address_line2) || ''
            );

            $('#customer_district').val(
                (data.versionData.customer?.district) || ''
            );

            $('#customer_state').val(
                (data.versionData.customer?.state) || ''
            );

            $('#customer_pincode').val(
                (data.versionData.customer?.pincode) || data.pincode || ''
            );


            // PDF fields
            const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD

            $('#pdf_date').val(data.versionData.pdf?.pdf_date || today);

            $('#pdf_subject').val(
                data.versionData.pdf?.pdf_subject || "Quotation for supply of RCC Products"
            );

            $('#pdf_terms').val(
                data.versionData.pdf?.pdf_terms ||
                "Terms & Conditions:\nThe above price includes loading and transportation.\nUnloading is under client scope."
            );

            $('#pdf_delivery').val(
                data.versionData.pdf?.pdf_delivery ||
                "Delivery will be made to the address mentioned above within the agreed timeline."
            );

            initProductPills(".product-pills-container", products);
            $(".product-pills-container .product-search").autocomplete({
                source: products.map(p => p.name),
                minLength: 1,
                select: function(event, ui) {
                    $(this).val(ui.item.value || ui.item.label || ui.item);
                    return false;
                }
            });

            initPincodeAutocomplete("#quote_pincode_input", "#quote_delivery_location",
                "#quote_delivery_location_id", "#quote_distance_km", "#quote_duration_minutes",
                "#quote_loader", "#editLeadModal");
            initDistanceDurationEditable();
            initQuoteCalculator();
            if (data.versionData)
            {
                data.versionData.is_edit_mode = true;
                renderManualQuoteTables(data.versionData, $container.find('#calcDetailsBody'));
                $('#calcDetailsCollapse').collapse('show');
            }
        });
    }

    // ✅ Submit handler (same as before)
    $(document).on('submit', '#createVersionQuotationForm', function (e) {
        e.preventDefault();

        if (!validateQuantities()) return;

        const trucks = collectTruckData();
        const prices = collectPriceData();
        const transport = collectTransportData();
        const subtotal = parseFloat($('#subtotal').text().replace(/[₹,]/g, '')) || 0;
        const gst_rate = 18;
        const gstAmount = subtotal * gst_rate / 100;
        const net_total = subtotal + gstAmount;
        const totalAmount = parseFloat($('#net_total').text().replace(/[₹,]/g, '')) || 0;
        const totalWeight = parseFloat($('#truck_total_weight').text().replace(/[^\d.]/g, '')) || 0;
        const cost_per_kg = totalWeight ? totalAmount / totalWeight : 0;
        const remarks = $('input[name="remarks"]').val() || '';
        const distance_km = parseFloat($('#quote_distance_km').val()) || 0;

        const payload = {
            trucks,
            prices,
            transport,
            subtotal,
            gst_rate,
            total_amount: totalAmount,
            net_total,
            cost_per_kg,
            distance_km,
            remarks
        };

        $('#quote_edit_data').val(JSON.stringify(payload));

        e.currentTarget.submit();
    });
});
</script>
@stack('scripts')
@stop
