@extends('adminlte::page')

@section('title', 'Create Lead')

{{-- ============================ PAGE HEADER ============================ --}}
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

      {{-- ============================ FLASH MESSAGES ============================ --}}
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

      {{-- ============================ FORM START ============================ --}}
      <form action="{{ route('leads.store') }}" method="POST" onsubmit="return validateForm();">
        @csrf

        <div class="card-body">
          <div class="row">

            {{-- ============================ BASIC INFO ============================ --}}
            <!-- Platform -->
            <div class="form-group col-md-4">
               <label>Platform <span class="text-danger">*</span></label>
               <select name="platform" class="form-control" required>
                   <option value="">Select</option>
                   @foreach($platforms as $platform)
                       <option value="{{ $platform }}" @selected(old('platform') === $platform)>{{ $platform }}</option>
                   @endforeach
               </select>
            </div>

            <!-- Lead Date -->
            <div class="form-group col-md-4">
              <label>Lead Date & Time <span class="text-danger">*</span></label>
              <input type="datetime-local" name="lead_date" id="lead_date"
                     value="{{ old('lead_date', \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}"
                     class="form-control" required>
            </div>

            <!-- Item Searched -->
            <div class="form-group col-md-4">
              <label>Item Searched</label>
              <input type="text" name="platform_keyword" value="{{ old('platform_keyword') }}" class="form-control"
                     maxlength="100" pattern="^[a-zA-Z0-9\s,.-]+$">
            </div>

            <!-- Buyer Name -->
            <div class="form-group col-md-4">
              <label>Buyer Name <span class="text-danger">*</span></label>
              <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" class="form-control"
                     required minlength="3" maxlength="100"
                     pattern="^[a-zA-Z0-9\s.]+$"
                     title="Only letters, numbers, spaces, and dots allowed.">
            </div>

            <!-- Buyer Contact -->
            <div class="form-group col-md-4">
              <label>Buyer Contact <span class="text-danger">*</span></label>
              <input type="text" name="buyer_contact" id="buyer_contact"
                     value="{{ old('buyer_contact') }}"
                     class="form-control"
                     oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                     maxlength="15" minlength="10"
                     pattern="[6-9]{1}[0-9]{9}"
                     title="Valid 10-digit number starting with 6-9" required>
            </div>
         {{-- ============================ LOCATION & DISTANCE ============================ --}}
                    <!-- Buyer Pincode -->
                    <div class="form-group col-md-4">
                        <label>Enter Pincode</label>
                        <input type="text" id="pincode_input" name="pincode" maxlength="6"
                               class="form-control"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                        <input type="hidden" name="buyer_location_id" class="buyer_location_id" id="buyer_location_id">
                    </div>

                    <!-- Buyer Location -->
                    <div class="form-group col-md-8">
                        <label>Buyer Location</label>
                        <input type="text" name="buyer_location" id="buyer_location" class="form-control" placeholder="Buyer location will appear here" readonly>
                    </div>

                    <!-- Distance Calculation Result -->
                    <div class="form-group col-md-4">
                        <label>Distance from Mfg Unit</label>
                        <input type="text" name="distance_result" id="distance_result"
                               class="form-control"
                               placeholder="Distance will appear here"
                               readonly
                               style="background-color: #d1ecf1; color: #0c5460;"> <!-- light blue bg, readable text -->
                        <!-- Loader -->
                        <div id="loader" class="text-center my-1" style="display:none;">
                            <i class="fas fa-spinner fa-spin fa-lg text-primary"></i>
                            <p class="mt-1 mb-0" style="font-size: 0.8rem;">Calculating...</p>
                        </div>
                    </div>

            {{-- ============================ PRODUCTS ============================ --}}
            <div class="form-group col-md-12 product-pills-container">
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

                <!-- Error Alert -->
                <div class="alert alert-danger product-alert d-none" role="alert"></div>

                <!-- Pills Container -->
                <div class="product-pills mb-2"
                     style="border:1px solid #d2d6de; padding:10px; border-radius:5px;"></div>

                <!-- Hidden textarea for storing product list -->
                <textarea name="product_detail" class="d-none product-detail" rows="2">{{ old('product_detail') }}</textarea>
            </div>



            {{-- ============================ DELIVERY & FOLLOW-UP ============================ --}}
            <!-- Expected Delivery Date -->
            <div class="form-group col-md-4">
                <label>Expected Delivery Date</label>
                <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                       value="{{ old('expected_delivery_date') }}"
                       class="form-control"
                       min="{{ date('Y-m-d') }}"
                       data-output="delivery_days_left">
                <small id="delivery_days_left" class="form-text text-muted"></small>
            </div>

            <!-- Follow-up Date -->
            <div class="form-group col-md-4">
                <label>Follow Up Date</label>
                <input type="date" name="follow_up_date" id="follow_up_date"
                       value="{{ old('follow_up_date') }}"
                       class="form-control"
                       min="{{ date('Y-m-d') }}"
                       data-output="followup_days_left">
                <small id="followup_days_left" class="form-text text-muted"></small>
            </div>

            {{-- ============================ STATUS & ASSIGNMENT ============================ --}}
            <!-- Status -->
            <div class="form-group col-md-4">
              <label>Status</label>
              <select name="status" class="form-control">
                @foreach(['New Lead', 'Lead Followup', 'Quotation', 'PO', 'Cancelled', 'Completed'] as $status)
                  <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                @endforeach
              </select>
            </div>

            <!-- Assigned To -->
            <div class="form-group col-md-4">
              <label>Assigned To</label>
              <select name="assigned_to" class="form-control">
               @foreach($users as $user)
                 <option value="{{ $user->name }}" @selected(old('assigned_to', auth()->id()) == $user->id)>
                   {{ $user->name }}
                 </option>
               @endforeach
              </select>
            </div>

            <!-- Tags -->
            <div class="col-md-4">
                <label for="tags" class="text-dark">Tags</label>
                <select id="tags" name="tags[]" multiple class="form-control">
                     @foreach(\Spatie\Tags\Tag::all() as $tag)
                        <option value="{{ $tag }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ============================ REMARK ============================ --}}
            <div class="form-group col-md-12">
              <label for="current_remark">Current Remark <span class="text-danger">*</span></label>
              <textarea name="current_remark" id="current_remark"
                        rows="2" class="form-control"
                        maxlength="1000"
                        placeholder="Add your remark..." required
                        style="resize: vertical;"></textarea>
            </div>
          </div>
        </div>

        {{-- ============================ ACTION BUTTONS ============================ --}}
        <div class="form-group row mt-2 px-4">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
            <div class="col-12 col-md-6">
                <x-adminlte-button label="Submit" type="submit" theme="primary" icon="fas fa-save"
                    class="btn-block" />
            </div>
        </div>
     </form>
     {{-- ============================ FORM END ============================ --}}
    </div>
  </div>
</section>
@stop

{{-- ============================ STYLES ============================ --}}
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
    /* Multiselect styling */
    .multiselect-container > li > a,
    .multiselect-container > li.multiselect-group label,
    .multiselect-container > li.multiselect-all label,
    .btn-group > .multiselect {
        text-align: left !important;
    }

    /* Pills for products */
    .product_pills .badge {
        display: inline-block;
        margin-bottom: 5px;
        padding: 8px 12px;
        font-size: 1rem;
        border-radius: 0.5rem;
    }
</style>
@stop

{{-- ============================ JAVASCRIPT ============================ --}}
@section('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

@include('leads.partials.shared-js')

<script>
$(document).ready(function() {
    var products = @json($products->pluck('name'));

    // Autocomplete
    $(".product-search").autocomplete({ source: products, minLength: 1 });

    // Product pills
    initProductPills(".product-pills-container", products);

    // Tag multiselect
    initTagMultiselect();

    // Delivery/followup days
    initDaysCalculation();

    // Pincode autocomplete
    initPincodeAutocomplete("#pincode_input", "#buyer_location", "#buyer_location_id", "#distance_result", "#loader");
});

</script>
@stop
