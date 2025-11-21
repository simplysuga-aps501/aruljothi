@extends('adminlte::page')

@section('title', 'All Quotations')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0 text-dark">Quotations</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createQuotationModal">
            <i class="fas fa-plus-circle mr-1"></i> Create Quotation
        </button>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success" id="flashSuccess">{{ session('success') }}</div>
    @endif

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="quotationsTable" class="table table-bordered table-hover nowrap text-sm w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lead</th>
                                    <th>Version</th>
                                    <th>Amount</th>
                                    <th>Created By</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quotations as $quotation)
                                    <tr>
                                        <td>{{ $quotation->id }}</td>
                                        <td>{{ $quotation->lead->buyer_name ?? '-' }}</td>
                                        <td>{{ $quotation->version_no ?? 'V1' }}</td>
                                        <td>{{ number_format($quotation->amount, 2) }}</td>
                                        <td>{{ $quotation->createdBy->name ?? '-' }}</td>
                                        <td>{{ $quotation->updated_at?->format('d-M-Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-xs btn-info view-quote" title="View" data-id="{{ $quotation->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-xs btn-warning ml-1 edit-quote" title="Edit" data-id="{{ $quotation->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-xs btn-secondary ml-1 print-quote" title="Print" data-id="{{ $quotation->id }}">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <form action="{{ route('quotations.destroy', $quotation->id) }}" method="POST" class="ml-1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <style>
        @media (max-width: 768px) {
            table.dataTable {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>

    <script>
        $(document).ready(function() {
            new DataTable('#quotationsTable', {
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    emptyTable: "No quotations found."
                },
                stateSave: true,
                stateSaveParams: function(settings, data) {
                    data.order = [];
                }
            });

            $('#createQuotationModal').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#createQuotationModal')
                });
            });

            setTimeout(() => $('#flashSuccess').fadeOut(), 3000);
        });
    </script>
@stop
