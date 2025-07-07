@extends('adminlte::page')

@section('title', 'Create Lead')

@section('content_header')
    <h1>Create Lead</h1>
@stop

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Create New Lead</h3>
      </div>

      @if(session('success'))
        <div class="alert alert-success m-3">
          {{ session('success') }}
        </div>
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

      <form action="{{ route('leads.store') }}" method="POST">
        @csrf
        <div class="card-body">
          <div class="row">
            <!-- Lead Platform -->
            <div class="form-group col-md-6">
              <label for="platform">Lead Platform</label>
              <select name="platform" id="platform" class="form-control" required>
                <option value="">Select</option>
                <option value="Just Dial">Just Dial</option>
                <option value="India Mart">India Mart</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <!-- Lead Date -->
            <div class="form-group col-md-6">
              <label for="lead_date">Lead Date & Time</label>
              <input type="datetime-local" id="lead_date" name="lead_date" class="form-control"
                value="{{ old('lead_date', \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}" required>
            </div>

            <!-- Buyer Name -->
            <div class="form-group col-md-6">
              <label for="buyer_name">Buyer Name</label>
              <input type="text" id="buyer_name" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}" required>
            </div>

            <!-- Buyer Location -->
            <div class="form-group col-md-6">
              <label for="buyer_location">Buyer Location</label>
              <input type="text" id="buyer_location" name="buyer_location" class="form-control" value="{{ old('buyer_location') }}">
            </div>

            <!-- Buyer Contact -->
            <div class="form-group col-md-6">
              <label for="buyer_contact">Buyer Contact Number</label>
              <input type="text" id="buyer_contact" name="buyer_contact"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                maxlength="10"
                pattern="[6-9]{1}[0-9]{9}"
                title="Enter a valid 10-digit Indian mobile number starting with 6-9"
                class="form-control" value="{{ old('buyer_contact') }}" required>
            </div>

            <!-- Item Searched -->
            <div class="form-group col-md-6">
              <label for="platform_keyword">Item Searched</label>
              <input type="text" id="platform_keyword" name="platform_keyword" class="form-control" value="{{ old('platform_keyword') }}">
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Submit
          </button>
          <a href="{{ route('leads.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </div>
      </form>
    </div>
  </div>
</section>
@stop

@section('css')
    {{-- Add custom styles if needed --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log("Create Lead page loaded"); </script>
@stop
