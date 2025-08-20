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

      <form action="{{ route('leads.store') }}" method="POST" onsubmit="return validateForm();">
        @csrf

        <div class="card-body">
          <div class="row">

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

            <!-- Platform Keyword -->
            <div class="form-group col-md-4">
              <label>Item Searched</label>
              <input type="text" name="platform_keyword" value="{{ old('platform_keyword') }}" class="form-control"
                     maxlength="100"
                     pattern="^[a-zA-Z0-9\s,.-]+$">
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

            <!-- Buyer Location -->
            <div class="form-group col-md-4">
              <label>Buyer Location</label>
              <input type="text" name="buyer_location" value="{{ old('buyer_location') }}" class="form-control"
                     minlength="3" maxlength="100"
                     pattern="^[a-zA-Z0-9\s,.-]+$">
            </div>

            <!-- Product Detail -->
            <div class="form-group col-md-12">
              <label>Product Details (Name; Quantity; Price/Unit)</label>
              <textarea name="product_detail" rows="2" class="form-control"
                        maxlength="300">{{ old('product_detail') }}</textarea>
            </div>

            <!-- Delivery Location -->
            <div class="form-group col-md-4">
              <label>Delivery Location</label>
              <input type="text" name="delivery_location" value="{{ old('delivery_location') }}" class="form-control"
              minlength="3" maxlength="100"
              pattern="^[a-zA-Z0-9\s,.-]+$">
            </div>

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
                 <option value="{{ $user->name }}"
                   @selected(old('assigned_to', auth()->id()) == $user->id)>
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

            <!-- Current Remark -->
            <div class="form-group col-md-12">
              <label for="current_remark">Current Remark <span class="text-danger">*</span></label>
              <textarea name="current_remark" id="current_remark"
                        rows="2"
                        class="form-control"
                        maxlength="1000"
                        placeholder="Add your remark..."
                        required
                        style="resize: vertical;"></textarea>
            </div>
          </div>
        </div>
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
    </div>
  </div>
</section>
@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
    <style>
        .multiselect-container > li > a,
        .multiselect-container > li.multiselect-group label,
        .multiselect-container > li.multiselect-all label,
        .btn-group > .multiselect{
            text-align: left !important;
        }

    </style>
@stop

@section('js')
    <!--Multiselect-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
    <!--Validation-->
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

    @include('leads.partials.shared-js')
    <script>
      function validateForm() {
        const buyerName = document.getElementById('buyer_name').value.trim();
        const contact = document.getElementById('buyer_contact').value.trim();
        const leadDate = document.getElementById('lead_date').value;
        const now = new Date();
        const inputDate = new Date(leadDate);

        if (buyerName.length < 3 || buyerName.length > 100) {
          alert("Buyer Name must be between 3 and 100 characters.");
          return false;
        }

        if (!/^[6-9][0-9]{9}$/.test(contact)) {
          alert("Enter a valid 10-digit Indian contact number.");
          return false;
        }

        if (inputDate > now) {
          alert("Lead date cannot be in the future.");
          return false;
        }

        return true;
      }

      document.addEventListener('DOMContentLoaded', () => {

        const now = new Date();
        const pad = (n) => n.toString().padStart(2, '0');
        const localDateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        const leadInput = document.getElementById('lead_date');
        if (leadInput) {
          leadInput.max = localDateTime;
        }
      });
      function initTagMultiselect() {
          $('#tags').multiselect('destroy').multiselect({
              includeSelectAllOption: true,
              buttonWidth: '100%',
              nonSelectedText: 'Select Tags',
              numberDisplayed: 2,
              enableFiltering: true,
              enableCaseInsensitiveFiltering: true
          });
      }

      $(document).ready(function() {
          initTagMultiselect();
          initDaysCalculation();
      });
    </script>
@stop
