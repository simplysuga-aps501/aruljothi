@extends('adminlte::page')

@section('title', 'Create Lead')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Create Lead</h1>
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Create Lead</li>
    </ol>
</div>
@stop

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">

      {{-- Flash Messages --}}
      @if(session('success'))
        <div class="alert alert-success m-3">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger m-3">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Form --}}
      <form action="{{ route('leads.store') }}" method="POST" onsubmit="return validateForm();">
        @csrf
        <div class="card-body">
          <div class="row">

            {{-- Platform --}}
            <div class="col-md-4">
                <x-adminlte-select name="platform" label="Platform" fgroup-class="mb-3" required>
                    <option value="">Select</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform }}" @selected(old('platform') === $platform)>{{ $platform }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            {{-- Lead Date --}}
            <div class="col-md-4">
                <x-adminlte-input name="lead_date" label="Lead Date & Time" type="datetime-local"
                    fgroup-class="mb-3" required
                    :value="old('lead_date', \Carbon\Carbon::now()->format('Y-m-d\TH:i'))"/>
            </div>

            {{-- Item Searched --}}
            <div class="col-md-4">
                <x-adminlte-input name="platform_keyword" label="Item Searched" placeholder="Item Searched"
                    fgroup-class="mb-3" maxlength="100" pattern="^[a-zA-Z0-9\s,.-]+$"
                    :value="old('platform_keyword')"/>
            </div>

            {{-- Buyer Name --}}
            <div class="col-md-4">
                <x-adminlte-input name="buyer_name" label="Buyer Name" placeholder="Name" type="text"
                    fgroup-class="mb-3" required minlength="3" maxlength="100"
                    pattern="^[a-zA-Z0-9\s.]+$" title="Only letters, numbers, spaces, and dots allowed."
                    :value="old('buyer_name')"/>
            </div>

            {{-- Buyer Contact --}}
            <div class="col-md-4">
                <x-adminlte-input name="buyer_contact" label="Buyer Contact" placeholder="Phone" type="text"
                    fgroup-class="mb-3" required maxlength="15" minlength="10"
                    pattern="[6-9]{1}[0-9]{9}" title="Valid 10-digit number starting with 6-9"
                    :value="old('buyer_contact')"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'');"/>
                <div id="duplicateAlert" class="text-danger small d-none"></div>
            </div>
            {{-- Buyer Location --}}
            <div class="col-md-4">
                <x-adminlte-input name="buyer_location" label="Buyer Location"
                                  placeholder="Enter buyer location" fgroup-class="mb-3"
                                  :value="old('buyer_location')" />
            </div>

            {{-- Products --}}
            <div class="col-md-12 product-pills-container">
                <label>Products</label>
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
                <div class="product-pills mb-2" style="border:1px solid #d2d6de; padding:10px; border-radius:5px;"></div>
                <textarea name="product_detail" class="d-none product-detail" rows="2">{{ old('product_detail') }}</textarea>
            </div>

            {{-- Delivery Pincode --}}
            <div class="col-md-2">
                <x-adminlte-input name="pincode" label="Enter Pincode" placeholder="Enter Pincode" type="text"
                    fgroup-class="mb-3" maxlength="6"
                    id="pincode_input" class="pincode_input"
                    :value="old('pincode')"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'');"/>
                <input type="hidden" name="delivery_location_id" id="delivery_location_id" class="delivery_location_id"/>
            </div>

            {{-- Delivery Location --}}
            <div class="col-md-6">
                <x-adminlte-input name="delivery_location" class="delivery_location" label="Delivery Location" placeholder="Delivery location will appear here"
                    fgroup-class="mb-3" readonly :value="old('delivery_location')"/>
            </div>

           {{-- Distance & Duration --}}
           <div class="col-md-4">
               <label for="distance" class="text-dark">Distance from Mfg Unit</label>
               <div class="input-group mb-3">
                   {{-- Distance --}}
                   <input type="text" id="distance_km" name="distance_km" placeholder="Distance"
                       class="form-control editable_field distance_km" readonly
                       value="{{ old('distance_km') ?? '' }}" style="background-color: #d1ecf1; color: #0c5460;">
                   <span class="input-group-text">km</span>

                   {{-- Duration --}}
                   <input type="text" id="duration_minutes" name="duration_minutes" placeholder="Duration"
                       class="form-control editable_field duration_minutes" readonly
                       value="{{ old('duration_minutes') ?? '' }}" style="background-color: #d1ecf1; color: #0c5460;">
                   <span class="input-group-text">mins</span>
               </div>
                <!-- Shared Alert Container -->
               <div class="distance_alert text-danger" style="display:none;"></div>
               <div id="loader" class="text-center my-1 " style="display:none;">
                   <i class="fas fa-spinner fa-spin fa-lg text-primary"></i>
                   <p class="mt-1 mb-0" style="font-size: 0.8rem;">Calculating...</p>
               </div>
           </div>
            {{-- Transport Quote Section --}}

            {{-- Estimated Cost --}}
            <div class="col-md-4">
                <label for="estimated_cost">Estimated Cost (₹)</label>
                <div class="input-group mb-1">
                    <input type="text" id="estimated_cost" name="estimated_cost"
                           class="form-control" placeholder="Estimated Cost" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-info" type="button" id="toggle_calc_details">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            {{-- Calculate Button --}}
            <div class="col-md-4">
               <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
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

            {{-- Expected Delivery & Follow-up --}}
            <div class="col-md-4">
                <x-adminlte-input name="expected_delivery_date" label="Expected Delivery Date" type="date"
                    fgroup-class="mb-3" min="{{ date('Y-m-d') }}"
                    data-output="delivery_days_left" :value="old('expected_delivery_date')"/>
                <small id="delivery_days_left" class="text-muted"></small>
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="follow_up_date" label="Follow Up Date" type="date"
                    fgroup-class="mb-3" min="{{ date('Y-m-d') }}"
                    data-output="followup_days_left" :value="old('follow_up_date')"/>
                <small id="followup_days_left" class="text-muted"></small>
            </div>

            {{-- Assigned To --}}
            <div class="col-md-4">
                <x-adminlte-select name="assigned_to" label="Assigned To" fgroup-class="mb-3">
                    @foreach($users as $user)
                        <option value="{{ $user->name }}" @selected(old('assigned_to', auth()->id()) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            {{-- Tags --}}
            <div class="col-md-4">
                <label for="tags" class="text-dark">Tags</label>
                <select id="tags" name="tags[]" multiple class="form-control">
                    @foreach(\Spatie\Tags\Tag::all() as $tag)
                        <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Status --}}
            <div class="col-md-4">
                <x-adminlte-select name="status" label="Status" fgroup-class="mb-3">
                    @foreach(['New Lead', 'Lead Followup', 'Quotation', 'PO', 'Cancelled', 'Completed'] as $status)
                        <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        {{-- Copy --}}
             <div class="col-md-2">
                <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                <button type="button" class="btn btn-secondary w-100" id="copy_whatsapp_text">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        {{-- Whatsapp --}}
            <div class="col-md-2">
                <label>&nbsp;</label> {{-- Keeps vertical alignment with other inputs --}}
                <button type="button" class="btn btn-success w-100" id="sendStatusWhatsappBtn">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
            </div>
            {{-- Current Remark --}}
            <div class="col-md-12">
                <x-adminlte-input name="current_remark" label="Current Remark" placeholder="Add your remark"
                    fgroup-class="mb-3" required :value="old('current_remark')"/>
            </div>

          </div>
        </div>

        {{-- Buttons --}}
        <div class="form-group row mt-2 px-4">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
            <div class="col-12 col-md-6">
                <x-adminlte-button label="Submit" id="saveBtn" type="submit" theme="primary" icon="fas fa-save" class="btn-block"/>
            </div>
        </div>
      </form>
    </div>
  </div>

@stop

@section('css')
    <!--Datatable CSS-->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.7/css/columnControl.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
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
    </style>
@stop

@section('js')
    <!--Datatable JS-->
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.7/js/dataTables.columnControl.min.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
    @include('leads.partials.shared-js')
    @include('shared_js.whatsapp-copy')

    <script>
        $(document).ready(function() {
            var products = @json($productsArray);
            $(".product-search").autocomplete({
                source: products.map(p => p.name),
                minLength: 1
            });
            initProductPills(".product-pills-container", products);
            initTagMultiselect();
            initDaysCalculation();
            initPincodeAutocomplete("#pincode_input", "#delivery_location", "#delivery_location_id", "#distance_km","#duration_minutes","#loader");
            initDistanceDurationEditable();
            initQuoteCalculator();

            //Avoid duplicate entry of leads in past 3 days
            const $buyerContact = $('#buyer_contact');
            const $contactError = $('#duplicateAlert');
            let lastCheckedNumber = '';

            $buyerContact.on('blur', function ()
            {
                const number = $buyerContact.val().trim();
                // Only check valid 10-digit numbers
                if (number.length === 10 && /^\d+$/.test(number)) {
                    // Prevent redundant AJAX
                    if (number === lastCheckedNumber) return;
                    lastCheckedNumber = number;

                    $.ajax({
                        url: '{{ route("leads.checkDuplicate") }}',
                        type: 'GET',
                        data: { buyer_contact: number },
                        success: function (response) {
                            if (response.exists) {
                                $contactError
                                    .removeClass('d-none')
                                    .text('⚠️ The number was already added in the last 3 days.');
                            } else {
                                $contactError.addClass('d-none').text('');
                            }
                        },
                        error: function () {
                            console.error('Error checking duplicate number.');
                        }
                    });
                } else {
                    $contactError.addClass('d-none').text('');
                }
            });
        });
</script>
@stop
