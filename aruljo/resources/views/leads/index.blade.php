@extends('adminlte::page')

@section('title', 'All Leads')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">View Leads</h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">View Leads</li>
        </ol>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success" id="flashSuccess">{{ session('success') }}</div>
    @endif
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">
                <div class="card-header">
                    <ul class="nav nav-pills">
                        @if ($isEuser)
                            {{-- Only Euser or No Roles: show only My Leads --}}
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'my' ? 'active' : '' }}"
                                    href="{{ route('leads.index', ['tab' => 'my']) }}">
                                    My Leads
                                </a>
                            </li>
                        @else
                            {{-- Other roles: show all three --}}
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}"
                                    href="{{ route('leads.index', ['tab' => 'active']) }}">
                                    Active Leads
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'my' ? 'active' : '' }}"
                                    href="{{ route('leads.index', ['tab' => 'my']) }}">
                                    My Leads
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}"
                                    href="{{ route('leads.index', ['tab' => 'all']) }}">
                                    All Leads
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                         <h5 class="font-weight-bold mb-0">{{ $tab === 'all' ? 'All Leads' : 'Active Leads' }}</h5>
                         @if ($tab === 'all')
                             @hasanyrole('admin|owner')
                                 <a href="{{ route('leads.export') }}" class="btn btn-success">
                                     <i class="fas fa-download"></i> Download All Leads
                                 </a>
                             @endhasanyrole
                         @endif
                     </div>
                    <div class="table-responsive">
                        <table id="leads_table" class="table table-bordered table-hover nowrap text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    @if ($tab === 'all')
                                        <th>Platform</th>
                                    @endif
                                    <th>Buyer</th>
                                    <th>Lead Date</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Follow-up</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                    </table>
                    @if ($tab === 'all')
                        <div class="alert alert-info py-2 px-3">
                            Showing leads from the last 60 days only. Download the excel to see all the leads.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-adminlte-modal id="deleteModal" title="Confirm Delete" theme="danger" icon="fas fa-exclamation-triangle"
            size="md">
            <p class="text-center">Are you sure you want to delete this lead?</p>
            <x-slot name="footerSlot">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <x-adminlte-button label="Yes, Delete" type="submit" theme="danger" icon="fas fa-trash" />
                    <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancel</button>
                </form>
            </x-slot>
        </x-adminlte-modal>

        </div>
    </section>
@include('leads.partials.edit')
@stop

@section('css')
    <!--Datatable CSS-->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.7/css/columnControl.dataTables.min.css">


    <!--Select2 Tags JS-->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
    <!--Auto Complete-->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">


    <style>
        .lead-row {
            cursor: pointer;
        }

        .multiselect-container>li>a,
        .multiselect-container>li.multiselect-group label,
        .multiselect-container>li.multiselect-all label,
        .btn-group>.multiselect {
            text-align: left !important;
        }
        .product-pills .pill {
            display: inline-flex;
            align-items: center;
            justify-content: space-between; /* Push icon to the right */
            max-width: 100%;
            word-break: break-word;
            white-space: normal;
            padding: 5px 10px;
            margin: 3px;
        }

        .product-pills .pill i {
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

    <!--Multiselect-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
    <!--Validation-->
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

    <!--Auto Complete-->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>


    @include('leads.partials.shared-js')
    @include('shared_js.whatsapp-copy')
    @include('shared_js.product-autocomplete')
    <script>
        $(document).ready(function() {
            $('#leads_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('leads.index', ['tab' => $tab]), [] , false }}",
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false},
                        @if ($tab === 'all')
                            {data: 'platform', name: 'platform'},
                        @endif
                        {data: 'buyer', name: 'buyer_name'},
                        {data: 'lead_date', name: 'lead_date'},
                        {data: 'buyer_contact', name: 'buyer_contact'},
                        {data: 'status', name: 'status'},
                        {data: 'assigned_to', name: 'assigned_to'},
                        {data: 'follow_up_date', name: 'follow_up_date'},
                        {data: 'actions', name: 'actions', orderable:false, searchable:false},
                    ],
                    responsive: true,
                    pageLength: 25,
                    order: [],
                    language: { emptyTable: "No leads available for this tab." }
                });
                setTimeout(() => {
                    $('#flashSuccess').fadeOut();
                }, 3000);

            });
        function setDeleteAction(actionUrl) {
            document.getElementById('deleteForm').setAttribute('action', actionUrl);
        }


    </script>
    @stack('scripts');
@stop
