@extends('adminlte::page')

@section('title', 'District Rates')

@section('content_header')
    <h1>District Rates</h1>
@stop

@section('content')
@if (session('success'))
    <x-adminlte-alert theme="success" title="Success" id="successAlert">
        {{ session('success') }}
    </x-adminlte-alert>
@endif

@if (session('error'))
    <x-adminlte-alert theme="danger" title="Error" id="errorAlert">
        {{ session('error') }}
    </x-adminlte-alert>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">All District Rates</h3>
        <div class="ml-auto">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addRateModal">
                <i class="fas fa-plus"></i> Add Rate
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="ratesTable" class="table table-bordered table-hover nowrap text-sm">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>State</th>
                        <th>District</th>
                        <th>Place</th>
                        <th>Rate</th>
                        <th>Office</th>
                        <th>Remarks</th>
                        <th>Logs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rates as $index => $rate)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rate->location->state ?? '-' }}</td>
                            <td>{{ $rate->location->district ?? '-' }}</td>
                            <td>
                                <a href="javascript:void(0);" class="open-edit-modal" data-id="{{ $rate->id }}">
                                    {{ $rate->location->place ?? '-' }}
                                </a>
                            </td>
                            <td>{{ $rate->rate ?? '-' }}</td>
                            <td>{{ $rate->office->name ?? '-' }}</td>
                            <td>{{ $rate->remarks ?? '-' }}</td>
                            <td>
                                <a href="{{ route('rates.audits', $rate->id) }}"
                                    class="btn btn-xs btn-outline-info ml-1" title="View Logs">
                                    <i class="fas fa-sticky-note"></i>
                                </a>

                            </td>
                            <td>
                                <form action="{{ route('rates.destroy', $rate->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Include CREATE MODAL --}}
@include('transport.rates.create')
@include('transport.rates.edit')
@include('confirm-delete-modal')

{{-- Include shared JS --}}
@include('transport.rates.shared-js')
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">

<style>
    .open-edit-modal { cursor: pointer; text-decoration: none; }
    .open-edit-modal:hover { text-decoration: underline; }
</style>
@stack('css')
@stop


@section('js')
<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>

<script>
    $(document).ready(function () {
        new DataTable('#ratesTable', {
            responsive: true,
            stateSave: true,
            order: [],
            language: { emptyTable: "No Rates to display" },
            pageLength: 25,
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
        });

        // Hide alerts after 3 seconds
        setTimeout(() => {
            $('#successAlert, #errorAlert').fadeOut('slow');
        }, 3000);
    });
</script>

@stack('scripts')
@stop
