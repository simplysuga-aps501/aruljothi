@extends('adminlte::page')

@section('title', 'All Leads')

@section('content_header')
    <h1>All Leads</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table id="leads_table" class="table table-bordered table-hover nowrap text-sm">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Platform</th>
                        <th>Lead Date</th>
                        <th>Buyer</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $index => $lead)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $lead->platform }}</td>
                            <td>{{ \Carbon\Carbon::parse($lead->lead_date)->format('d-m-Y h:i A') }}</td>
                            <td>{{ $lead->buyer_name }}</td>
                            <td>
                                <a href="tel:{{ $lead->buyer_contact }}"
                                   onclick="copyPhone(event, '{{ $lead->buyer_contact }}')"
                                   class="text-primary">
                                    {{ $lead->buyer_contact }}
                                </a>
                            </td>
                            <td>{{ $lead->status }}</td>
                            <td>{{ $lead->assigned_to }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-success">Update</a>
                                    <a href="{{ route('leads.audits', $lead->id) }}" class="btn btn-info">Logs</a>
                                    <x-adminlte-button label="Delete" theme="outline-danger" icon="fas fa-trash"
                                        data-toggle="modal" data-target="#deleteModal"
                                        onclick="setDeleteAction('{{ route('leads.destroy', $lead->id) }}')"/>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <x-adminlte-modal id="deleteModal" title="Confirm Delete" theme="danger" icon="fas fa-exclamation-triangle" size="md">
        <p class="text-center">Are you sure you want to delete this lead?</p>

        <x-slot name="footerSlot">
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <x-adminlte-button label="Yes, Delete" type="submit" theme="danger" icon="fas fa-trash"/>
                <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancel</button>
            </form>
        </x-slot>
    </x-adminlte-modal>

@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
@stop

@section('js')
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>

    <script>
        new DataTable('#leads_table', {
            responsive: true,
            stateSave: true,
            columnControl: true,
            ordering: true,
            initComplete: function () {
                document.querySelector('#leads_table').classList.remove('opacity-0');
            }
        });

        function copyPhone(event, number) {
            if (!/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
                event.preventDefault();
                navigator.clipboard.writeText(number)
                    .then(() => alert('Phone number copied: ' + number))
                    .catch(() => alert('Failed to copy number.'));
            }
        }

        function setDeleteAction(actionUrl) {
            document.getElementById('deleteForm').setAttribute('action', actionUrl);
        }

    </script>
@stop
