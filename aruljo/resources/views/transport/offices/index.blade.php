@extends('adminlte::page')

@section('title', 'Transport Offices')

@section('content_header')
    <h1>Transport Offices</h1>
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
        <h3 class="card-title mb-0">All Transport Offices</h3>
        <div class="ml-auto">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addOfficeModal">
                <i class="fas fa-plus"></i> Add New Office
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="officesTable" class="table table-bordered table-hover nowrap text-sm">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Office Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Preferred Districts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offices as $index => $office)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            {{-- Click office name to open edit modal --}}
                            <td>
                                <a href="javascript:void(0);"
                                   class="text-primary font-weight-bold open-edit-modal"
                                   data-id="{{ $office->id }}">
                                    {{ $office->name }}
                                </a>
                            </td>

                            <td>{{ $office->contact_person ?? '-' }}</td>


                            <td>
                               <a href="tel:{{ $office->phone }}"
                                   onclick="copyPhone(event, '{{ $office->phone }}')"
                                    class="text-primary">{{ $office->phone }}</a>
                                <a href="https://wa.me/91{{ $office->phone }}" target="_blank"
                                    class="ms-2">
                                    <x-adminlte-button label="" icon="fab fa-whatsapp" theme="success" />
                                </a>
                            </td>

                            {{-- Preferred Districts (truncate + tooltip) --}}
                            <td>
                                @php
                                    $districts = $office->preferred_districts_list ?? '-';
                                    $short = strlen($districts) > 25 ? substr($districts, 0, 25) . '…' : $districts;
                                @endphp
                                <span title="{{ $districts }}">{{ $short }}</span>
                            </td>

                            {{-- Only delete button --}}
                            <td>
                                <form action="{{ route('tp_offices.destroy', $office->id) }}" method="POST" style="display:inline-block;">
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
@include('transport.offices.create')
@include('transport.offices.edit')
@include('confirm-delete-modal')
@stop

@section('css')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.7/css/columnControl.dataTables.min.css">

<!-- Multiselect CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<style>

    a.open-edit-modal {
        cursor: pointer;
        text-decoration: none;
    }
    a.open-edit-modal:hover {
        text-decoration: underline;
    }

    .multiselect-container {
        max-height: 250px;
        overflow-y: auto !important;
    }
    .ui-autocomplete {
        z-index: 99999 !important;
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }
</style>
@stop

@section('js')
<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/columncontrol/1.0.7/js/dataTables.columnControl.min.js"></script>

<!-- Multiselect Scripts -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>


<script>
    $(document).ready(function () {
        // Initialize DataTable
        new DataTable('#officesTable', {
            responsive: true,
            stateSave: true,
            order: [],
            language: { emptyTable: "No Products to display" },
            pageLength: 25, // default selection
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],

        });
        // Hide alerts after 3 seconds
        setTimeout(() => {
            $('#successAlert, #errorAlert').fadeOut('slow');
        }, 3000);
    });

</script>

@include('transport.offices.shared-js')
@stack('scripts')
@stop
