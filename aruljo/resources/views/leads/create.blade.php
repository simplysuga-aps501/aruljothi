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
              <label for="platform">Lead Platform<span class="text-danger"> *</span></label>
              <select name="platform" id="platform" class="form-control" required>
                <option value="">Select</option>
                <option value="Justdial">Justdial</option>
                <option value="Indiamart">Indiamart</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <!-- Lead Date -->
            <div class="form-group col-md-6">
              <label for="lead_date">Lead Date & Time<span class="text-danger"> *</span></label>
              <input type="datetime-local" id="lead_date" name="lead_date" class="form-control"
                value="{{ old('lead_date', \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}" required>
            </div>

            <!-- Buyer Name -->
            <div class="form-group col-md-6">
              <label for="buyer_name">Buyer Name<span class="text-danger"> *</span></label>
              <input type="text" id="buyer_name" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}" required>
            </div>

            <!-- Buyer Location -->
            <div class="form-group col-md-6">
              <label for="buyer_location">Buyer Location</label>
              <input type="text" id="buyer_location" name="buyer_location" class="form-control" value="{{ old('buyer_location') }}">
            </div>

            <!-- Buyer Contact -->
            <div class="form-group col-md-6">
              <label for="buyer_contact">Buyer Contact Number<span class="text-danger"> *</span></label>
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
          <a href="{{ route('leads.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
          <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Submit
          </button>
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
