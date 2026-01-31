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
                            <tbody>
                                @foreach ($quotations as $quotation)
                                    <tr>
                                        <td>{{ $quotation->quote_number }}</td>
                                        <td>{{ $quotation->lead->id }}</td>
                                        <td>
                                            @if($quotation->lead)
                                                <a href="{{ route('quotations.create-version', $quotation->id) }}">
                                                    {{ $quotation->lead->buyer_name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($quotation->lead && $quotation->lead->buyer_contact)
                                                @php
                                                    // Clean and normalize phone number
                                                    $contact = preg_replace('/\D/', '', $quotation->lead->buyer_contact);
                                                    if (strlen($contact) == 10) {
                                                        $contact = '91' . $contact; // Add country code if missing
                                                    }
                                                    $whatsappUrl = "https://wa.me/{$contact}";
                                                    $callUrl = "tel:+{$contact}";
                                                @endphp

                                                {{-- Click to Call --}}
                                                <a href="{{ $callUrl }}"
                                                   class="text-primary"
                                                   title="Click to call">
                                                    {{ $quotation->lead->buyer_contact }}
                                                </a>

                                                {{-- WhatsApp link --}}
                                                <a href="{{ $whatsappUrl }}"
                                                   target="_blank"
                                                   class="text-success ml-2"
                                                   title="Chat on WhatsApp">
                                                    <i class="fab fa-whatsapp fa-lg"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ number_format($quotation->total_amount, 2) }}</td>
                                        <td>{{ $quotation->modifier->name ?? $quotation->creator->name ?? '-' }}</td>
                                        <td>{{ $quotation->updated_at?->format('d-M-Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- PDF Download --}}
                                                <button class="btn btn-xs btn-danger ml-1 download-pdf" title="Download PDF"
                                                        data-id="{{ $quotation->id }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
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
@stack('styles')
@stop

@section('js')

    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/responsive.dataTables.js"></script>
    @include('shared_js.pdf-download')
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

            setTimeout(() => $('#flashSuccess').fadeOut(), 3000);

        });
    </script>
@stack('scripts')
@stop
