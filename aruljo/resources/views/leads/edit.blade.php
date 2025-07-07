@extends('adminlte::page')

@section('title', 'Edit Lead')

@section('content_header')
    <h1>Edit Lead</h1>
@stop

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Update Lead Information</h3>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger m-3">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('leads.update.full', $lead->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
          <div class="row">
            <!-- Platform -->
            <div class="form-group col-md-6">
              <label>Platform</label>
              <select name="platform" class="form-control">
                <option value="Justdial" @selected($lead->platform === 'Justdial')>Justdial</option>
                <option value="IndiaMART" @selected($lead->platform === 'IndiaMART')>IndiaMART</option>
                <option value="Others" @selected($lead->platform === 'Others')>Others</option>
              </select>
            </div>

            <!-- Lead Date & Time -->
            <div class="form-group col-md-6">
              <label>Lead Date & Time</label>
              <input type="datetime-local" name="lead_date"
                     value="{{ \Carbon\Carbon::parse($lead->lead_date)->format('Y-m-d\TH:i') }}"
                     class="form-control">
            </div>

            <!-- Buyer Name -->
            <div class="form-group col-md-6">
              <label>Buyer Name</label>
              <input type="text" name="buyer_name" value="{{ $lead->buyer_name }}" class="form-control">
            </div>

            <!-- Buyer Location -->
            <div class="form-group col-md-6">
              <label>Buyer Location</label>
              <input type="text" name="buyer_location" value="{{ $lead->buyer_location }}" class="form-control">
            </div>

            <!-- Buyer Contact -->
            <div class="form-group col-md-6">
              <label>Buyer Contact</label>
              <input type="text" name="buyer_contact" value="{{ $lead->buyer_contact }}" class="form-control">
            </div>

            <!-- Item Searched -->
            <div class="form-group col-md-6">
              <label>Item Searched</label>
              <input type="text" name="platform_keyword" value="{{ $lead->platform_keyword }}" class="form-control">
            </div>

            <!-- Product Details -->
            <div class="form-group col-md-12">
              <label>Product Details (Name; Quantity; Price/Unit)</label>
              <textarea name="product_detail" rows="3" class="form-control">{{ $lead->product_detail }}</textarea>
            </div>

            <!-- Delivery Location -->
            <div class="form-group col-md-6">
              <label>Delivery Location</label>
              <input type="text" name="delivery_location" value="{{ $lead->delivery_location }}" class="form-control">
            </div>

            <!-- Expected Delivery Date -->
            <div class="form-group col-md-6">
              <label>Expected Delivery Date</label>
              <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                     value="{{ $lead->expected_delivery_date }}" class="form-control">
              <small id="delivery_days_left" class="form-text text-muted"></small>
            </div>

            <!-- Remarks -->
            <div class="form-group col-md-6">
              <label>Remarks</label>
              <textarea name="remarks" rows="2" class="form-control">{{ $lead->remarks }}</textarea>
            </div>

            <!-- Follow Up Date -->
            <div class="form-group col-md-6">
              <label>Follow Up Date</label>
              <input type="date" name="follow_up_date" id="follow_up_date"
                     value="{{ $lead->follow_up_date }}" class="form-control">
              <small id="followup_days_left" class="form-text text-muted"></small>
            </div>

            <!-- Status -->
            <div class="form-group col-md-6">
              <label>Status</label>
              <select name="status" class="form-control">
                @foreach(['New Lead', 'Lead Followup', 'Quotation', 'PO', 'Cancelled', 'Completed'] as $status)
                  <option value="{{ $status }}" @selected($lead->status === $status)>{{ $status }}</option>
                @endforeach
              </select>
            </div>

            <!-- Assigned To -->
            <div class="form-group col-md-6">
              <label>Assigned To</label>
              <select name="assigned_to" class="form-control">
                @foreach($users as $userId => $userName)
                  <option value="{{ $userName }}"
                    @selected($lead->assigned_to === $userName || (empty($lead->assigned_to) && $userName === $currentUser))>
                    {{ $userName }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
          </button>
          <a href="{{ route('leads.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</section>
@stop

@section('js')
<script>
  function calculateDays(inputId, outputId) {
    const input = document.getElementById(inputId);
    const output = document.getElementById(outputId);

    const update = () => {
      const date = new Date(input.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (!isNaN(date)) {
        const diff = Math.ceil((date - today) / (1000 * 60 * 60 * 24));
        output.textContent = diff >= 0
          ? `${diff} day(s) from today`
          : `${Math.abs(diff)} day(s) ago`;
      } else {
        output.textContent = '';
      }
    };

    input.addEventListener('input', update);
    update();
  }

  calculateDays('expected_delivery_date', 'delivery_days_left');
  calculateDays('follow_up_date', 'followup_days_left');
</script>
@stop
