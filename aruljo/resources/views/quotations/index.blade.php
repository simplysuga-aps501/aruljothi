@extends('adminlte::page')

@section('title', 'All Quotations')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0 text-dark">Quotations</h1>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Create Quotation
        </a>
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
                                    <th>Quote No</th>
                                    <th>Lead No</th>
                                    <th>Buyer Name</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Modified By</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
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
@stack('styles')
@stop

@section('js')

    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/responsive.dataTables.js"></script>
    @include('shared_js.pdf-download')
    <script>
        $(document).ready(function() {
            $(function () {
                $('#quotationsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('quotations.index'),[],false }}",
                    columns: [
                        { data: 'quote_number', name: 'quote_number' },
                        { data: 'lead_no', name: 'lead.id', orderable: false },
                        { data: 'buyer_name', name: 'lead.buyer_name', orderable: false },
                        { data: 'contact', name: 'lead.buyer_contact', orderable: false, searchable: false },
                        { data: 'amount', name: 'total_amount' },
                        { data: 'modified_by', name: 'modifier.name', orderable: false },
                        { data: 'last_updated', name: 'updated_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    order: [[0, 'desc']],
                    responsive: true,
                    pageLength: 25,
                    language: { emptyTable: "No quotations found." },
                });

                // Optional: fade success message
                setTimeout(() => $('#flashSuccess').fadeOut(), 3000);
            });


            setTimeout(() => $('#flashSuccess').fadeOut(), 3000);

        });
    </script>
@stack('scripts')
@stop
