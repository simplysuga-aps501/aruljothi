@extends('adminlte::page')

@section('title', 'Create Lead')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">You are logged in</h3>
      </div>
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
