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
    <div id="leadSuccessAlert"></div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">
                <div class="card-header">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('leads.index', ['tab' => 'active']) }}">Active Leads</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'my' ? 'active' : '' }}" href="{{ route('leads.index', ['tab' => 'my']) }}">My Leads</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('leads.index', ['tab' => 'all']) }}">All Leads</a>
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
                                @foreach ($leads as $index => $lead)
                                    <tr>
                                        <td>{{ $lead->id }}</td>
                                        @if ($tab === 'all')
                                            <td>{{ $lead->platform }}</td>
                                        @endif
                                        @php
                                            $leadDateObj = \Carbon\Carbon::parse($lead->lead_date);
                                            $formattedShort = $leadDateObj->format('d-m-Y');
                                            $formattedFull = $leadDateObj->format('d-m-Y h:i A');
                                        @endphp
                                        <td data-order="{{ $leadDateObj->format('Y-m-d H:i:s') }}">
                                            <span title="{{ $formattedFull }}">{{ $formattedShort }}</span>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="lead-quick-edit" data-lead-id="{{ $lead->id }}">
                                                <span title="{{ $lead->buyer_name }}">{{ \Illuminate\Support\Str::limit($lead->buyer_name, 20) }}</span>
                                            </a>
                                        </td>

                                        <td>
                                            <a href="tel:{{ $lead->buyer_contact }}" onclick="copyPhone(event, '{{ $lead->buyer_contact }}')" class="text-primary">{{ $lead->buyer_contact }}</a>
                                        </td>
                                        <td>{{ $lead->status }}</td>
                                        <td>{{ $lead->assigned_to }}</td>
                                        @php $followUpDate = $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date) : null; @endphp
                                        <td @if($followUpDate) data-order="{{ $followUpDate->format('Y-m-d') }}" @endif>
                                            @if ($followUpDate)
                                                <span class="{{ $followUpDate->isToday() ? 'bg-warning text-dark px-2 py-1 rounded' : '' }}">{{ $followUpDate->format('d-m-Y') }}</span>
                                            @else - @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-success">Update</a>
                                                <a href="{{ route('leads.audits', $lead->id) }}" class="btn btn-info">Logs</a>
                                                <x-adminlte-button label="Delete" theme="outline-danger" icon="fas fa-trash" data-toggle="modal" data-target="#deleteModal" onclick="setDeleteAction('{{ route('leads.destroy', $lead->id) }}')" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
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
                        <x-adminlte-button label="Yes, Delete" type="submit" theme="danger" icon="fas fa-trash" />
                        <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancel</button>
                    </form>
                </x-slot>
            </x-adminlte-modal>

            {{-- Quick Edit Modal with Form --}}
            <form id="editLeadForm" method="POST">
                @csrf
                @method('PUT')
                <x-adminlte-modal id="editLeadModal" title="Quick Edit" theme="primary" icon="fas fa-edit" size="md" static-backdrop scrollable>

                    {{-- Quick Info Summary --}}

                    <div class="mb-3 small text-muted d-flex flex-wrap">
                        <span class="mr-2"><i class="fas fa-user"></i> <strong id="leadBuyer"></strong></span>
                        <span class="mr-2"><i class="fas fa-map-marker-alt"></i> <strong id="leadLocation"></strong></span>
                        <span class="mr-2">
                            <i class="fas fa-phone-alt"></i>
                            <strong id="leadContactWrapper">
                                <a href="#" id="leadContact" class="text-dark text-decoration-none"></a>
                            </strong>
                        </span>
                        <span><i class="fas fa-calendar-day"></i> <strong id="leadDate"></strong></span>
                    </div>

                    {{-- Editable Fields --}}
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <x-adminlte-select name="status" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">Status</div>
                                </x-slot>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-12 col-md-6 mt-2 mt-md-0">
                            <x-adminlte-select name="assigned_to" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">Assigned</div>
                                </x-slot>
                                @foreach($users as $user)
                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                    </div>

                    {{-- New Remark --}}
                    <x-adminlte-textarea name="current_remark" rows=2 igroup-size="sm" placeholder="Add new remark...">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">📝</div>
                        </x-slot>
                    </x-adminlte-textarea>

                    {{-- Past Remarks (read-only) --}}
                    <x-adminlte-textarea name="past_remarks" rows=4 igroup-size="sm" disabled>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">Past</div>
                        </x-slot>
                    </x-adminlte-textarea>

                    {{-- Footer --}}
                    <x-slot name="footerSlot">
                        <x-adminlte-button class="mr-auto edit-full-link" theme="outline-secondary" label="Full Edit" icon="fas fa-external-link-alt" />
                        <x-adminlte-button type="submit" theme="primary" label="Save" icon="fas fa-save" />
                    </x-slot>

                </x-adminlte-modal>
            </form>
        </div>
    </section>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
    <style>.lead-row { cursor: pointer; }</style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>

    <script>
        $(document).ready(function () {
            new DataTable('#leads_table', {
                responsive: true,
                stateSave: true,
                columnControl: true,
                ordering: true,
                language: { emptyTable: "No leads available for this tab." },
                initComplete: function () {
                    document.querySelector('#leads_table').classList.remove('opacity-0');
                }
            });

            $('#leads_table').on('click', '.lead-quick-edit', function () {
                const leadId = $(this).data('lead-id');
                $('#editLeadForm').attr('action', `/leads/${leadId}`);
                $.get(`/leads/${leadId}`, function (lead) {
                    $('#editLeadForm').attr('action', `/leads/${leadId}`);

                    $('#leadDate').text(formatLeadDate(lead.lead_date));
                    $('#leadBuyer').text(lead.buyer_name || '');
                    if (lead.buyer_location) {
                        $('#leadLocation').text(lead.buyer_location);
                        $('#leadLocation').parent().show();
                    } else {
                        $('#leadLocation').parent().hide();
                    }
                    const contact = lead.buyer_contact || '';
                    $('#leadContact').text(contact);
                    $('#leadContact').attr('href', contact ? `tel:${contact}` : '#');

                    // Editable Fields
                    $('[name="status"]').val(lead.status);
                    $('[name="assigned_to"]').val(lead.assigned_to);
                    $('[name="current_remark"]').val('');
                    $('[name="past_remarks"]').val(
                        (lead.remarks || '').split('~|~').join('\n')
                    );

                    $('.edit-full-link').attr('onclick', `window.location='/leads/${leadId}/edit'`);
                    $('#editLeadModal').modal('show');
                });
            });
        });

        function copyPhone(event, number) {
            if (!number || !navigator.clipboard) return;
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

        function formatLeadDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr; // fallback to raw if invalid

            const day = date.getDate().toString().padStart(2, '0');
            const month = date.toLocaleString('en-US', { month: 'short' }); // "Jul"
            const year = date.getFullYear();

            let hours = date.getHours();
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12 || 12;

            return `${day}${month}${year} ${hours}.${minutes} ${ampm}`;
        }
        $('#editLeadForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const action = form.attr('action');
            const formData = form.serialize();

            $.ajax({
                url: action,
                method: 'POST', // Still POST because `_method=PUT` is inside the form
                data: formData,
                success: function (res) {
                    $('#editLeadModal').modal('hide');

                    // Show success alert (customize this container if needed)
                    $('#leadSuccessAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                            ${res.message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `);

                    // Refresh DataTable
                    location.reload();
                },
                error: function (xhr) {
                    let msg = 'Failed to update lead.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });

    </script>
@stop
