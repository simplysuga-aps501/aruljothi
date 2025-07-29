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
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">
                <div class="card-header">
                    <ul class="nav nav-pills">
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
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="mb-3 font-weight-bold">{{ $tab === 'all' ? 'All Leads' : 'Active Leads' }}</h5>

                    <div class="table-responsive">
                        <table id="leads_table" class="table table-bordered table-hover nowrap text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    @if ($tab === 'all')
                                        <th>Platform</th>
                                    @endif
                                    <th>Lead Date</th>
                                    <th>Buyer</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Followup Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leads as $index => $lead)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        @if ($tab === 'all')
                                            <td>{{ $lead->platform }}</td>
                                        @endif

                                        @php
                                            $leadDateObj = \Carbon\Carbon::parse($lead->lead_date);
                                            $formattedShort = $leadDateObj->format('d-m-Y'); // or 'd-m-y'
                                            $formattedFull = $leadDateObj->format('d-m-Y h:i A');
                                        @endphp
                                        <td data-order="{{ $leadDateObj->format('Y-m-d H:i:s') }}">
                                            <span title="{{ $formattedFull }}">{{ $formattedShort }}</span>
                                        </td>
                                        @php
                                            $fullName = $lead->buyer_name;
                                            $shortName = \Illuminate\Support\Str::limit($fullName, 20);
                                        @endphp
                                        <td>
                                            <span @if(strlen($fullName) > 20) title="{{ $fullName }}" @endif>
                                                {{ $shortName }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="tel:{{ $lead->buyer_contact }}"
                                               onclick="copyPhone(event, '{{ $lead->buyer_contact }}')"
                                               class="text-primary">
                                                {{ $lead->buyer_contact }}
                                            </a>
                                        </td>
                                        <td>{{ $lead->status }}</td>
                                        <td>{{ $lead->assigned_to }}</td>
                                        @php
                                            $followUpDate = $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date) : null;
                                        @endphp

                                            <td @if($followUpDate) data-order="{{ $followUpDate->format('Y-m-d') }}" @endif>
                                                @if ($followUpDate)
                                                    <span class="{{ $followUpDate->isToday() ? 'bg-warning text-dark px-2 py-1 rounded' : '' }}">
                                                        {{ $followUpDate->format('d-m-Y') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
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
                                        <td colspan="{{ $tab === 'all' ? 9 : 8 }}" class="text-center text-muted">No leads found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

        </div>
    </section>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
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
