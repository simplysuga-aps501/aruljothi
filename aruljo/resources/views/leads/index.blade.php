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
                @php
                    $onlyEuser = auth()->user()->getRoleNames()->count() === 1 && auth()->user()->hasRole('euser');
                @endphp

                <div class="card-header">
                    @if($onlyEuser)
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'my' ? 'active' : '' }}"
                                   href="{{ route('leads.index', ['tab' => 'my']) }}">
                                    My Leads
                                </a>
                            </li>
                        </ul>
                    @else
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
                    @endif
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
                                    <th>Follow-up</th>
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
                                          $daysAgo = $leadDateObj->diffInDays(now());
                                      @endphp

                                      <td data-order="{{ $leadDateObj->format('Y-m-d H:i:s') }}">
                                          <span title="{{ $formattedFull }}">
                                              {{ $formattedShort }}
                                              <small class="text-muted">({{ str_pad($daysAgo, 2, '0', STR_PAD_LEFT) }})</small>
                                          </span>
                                      </td>

                                       <td>
                                           <a href="javascript:void(0);"
                                              class="open-edit-lead-modal"
                                              data-lead-id="{{ $lead->id }}">

                                               {{-- Show tags as badges first --}}
                                               @foreach($lead->tags as $tag)
                                                   <span class="badge badge-info">{{ $tag->name }}</span>
                                               @endforeach

                                               {{-- Then show buyer name --}}
                                               <span title="{{ $lead->buyer_name }}">
                                                   {{ \Illuminate\Support\Str::limit($lead->buyer_name, 20) }}
                                               </span>
                                           </a>
                                       </td>

                                        <td>
                                            <a href="tel:{{ $lead->buyer_contact }}" onclick="copyPhone(event, '{{ $lead->buyer_contact }}')" class="text-primary">{{ $lead->buyer_contact }}</a>
                                            <a href="https://wa.me/91{{ $lead->buyer_contact }}" target="_blank" class="ms-2">
                                                <x-adminlte-button label="" icon="fab fa-whatsapp" theme="success" />
                                            </a>
                                        </td>
                                        <td>{{ $lead->status }}</td>
                                        <td>{{ $lead->assigned_to }}</td>
                                        @php $followUpDate = $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date) : null; @endphp
                                        <td @if($followUpDate) data-order="{{ $followUpDate->format('Y-m-d') }}" @endif>
                                            @if ($followUpDate)
                                                <span class="
                                                    {{ $followUpDate->isToday() ? 'bg-warning text-dark px-2 py-1 rounded' : '' }}
                                                    {{ $followUpDate->isPast() && !$followUpDate->isToday() ? 'bg-danger text-white px-2 py-1 rounded' : '' }}
                                                ">
                                                    {{ $followUpDate->format('d-m-Y') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                       <td data-order="{{ $lead->updated_at->timestamp }}">
                                           <div class="btn-group btn-group-sm">
                                               @php
                                                   $updatedAt = \Carbon\Carbon::parse($lead->updated_at);
                                                   $now = \Carbon\Carbon::now();
                                                   $diffMinutes = $updatedAt->diffInMinutes($now);
                                                   $diffHours = $updatedAt->diffInHours($now);
                                                   $diffDays = $updatedAt->diffInDays($now);

                                                   if ($diffMinutes < 60) {
                                                       // Round to nearest 15 mins
                                                       $roundedMinutes = round($diffMinutes / 15) * 15;
                                                       $lastUpdatedText = $roundedMinutes > 0 ? "{$roundedMinutes} mins ago" : "just now";
                                                   } elseif ($diffHours < 48) {
                                                       $lastUpdatedText = "{$diffHours} hrs ago";
                                                   } else {
                                                       $lastUpdatedText = "{$diffDays} days ago";
                                                   }
                                               @endphp

                                               <div class="d-flex align-items-center">
                                                   <small style="display:inline-block; min-width:70px;">
                                                       {{ $lastUpdatedText }}
                                                   </small>
                                                   <a href="{{ route('leads.audits', $lead->id) }}"
                                                      class="btn btn-xs btn-outline-info ml-1"
                                                      title="View Logs">
                                                       <i class="fas fa-sticky-note"></i>
                                                   </a>
                                               </div>

                                               @role('admin')
                                                   <x-adminlte-button label="Delete"
                                                                      theme="outline-danger"
                                                                      icon="fas fa-trash"
                                                                      data-toggle="modal"
                                                                      data-target="#deleteModal"
                                                                      onclick="setDeleteAction('{{ route('leads.destroy', $lead->id) }}')" />
                                               @endrole
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

        </div>
    </section>
@stop

@section('css')
    <!--Datatable CSS-->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">

    <!--Select2 Tags JS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">

    <style>
        .lead-row { cursor: pointer; }

        .multiselect-container > li > a,
        .multiselect-container > li.multiselect-group label,
        .multiselect-container > li.multiselect-all label,
        .btn-group > .multiselect{
            text-align: left !important;
        }

    </style>
@stop

@section('js')

    @include('leads.partials.edit-modal')

    <!--Datatable JS-->
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>
    <!--Multiselect-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
    <!--Validation-->
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
     @include('leads.partials.shared-js')
    <script>
        $(document).ready(function () {
            new DataTable('#leads_table', {
                responsive: true,
                stateSave: true,
                ordering: true, // allow sorting
                language: { emptyTable: "No leads available for this tab." },

                stateSaveParams: function (settings, data) {
                    // Always reset ordering before saving state
                    data.order = [];
                },

                initComplete: function () {
                    document.querySelector('#leads_table').classList.remove('opacity-0');
                }
            });

            setTimeout(() => {
                $('#flashSuccess').fadeOut();
            }, 3000);
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

        function initTagMultiselect() {
                $('#tags').multiselect('destroy').multiselect({
                    includeSelectAllOption: true,
                    buttonWidth: '100%',
                    nonSelectedText: 'Select Tags',
                    numberDisplayed: 2,
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true
                });
            }

        //Edit Modal
        $(document).on('click', '.open-edit-lead-modal', function () {
            const leadId = $(this).data('lead-id');
            const currentTab = new URLSearchParams(window.location.search).get('tab') || 'active';
            $.ajax({
                 url: `/leads/${leadId}/edit?tab=${currentTab}`,
                method: 'GET',
                success: function (data) {
                    $('#editLeadForm').attr('action', `/leads/${leadId}?tab=${currentTab}`);
                    // Fill form fields using correct names from DB and modal
                    $('#editLeadModal input[name="buyer_name"]').val(data.buyer_name);
                    $('#editLeadModal input[name="buyer_contact"]').val(data.buyer_contact);
                    $('#editLeadModal input[name="lead_date"]').val(data.lead_date);
                    $('#editLeadModal input[name="buyer_location"]').val(data.buyer_location);
                    $('#editLeadModal select[name="platform"]').val(data.platform);
                    $('#editLeadModal input[name="platform_keyword"]').val(data.platform_keyword);
                    $('#editLeadModal textarea[name="product_detail"]').val(data.product_detail);
                    $('#editLeadModal input[name="delivery_location"]').val(data.delivery_location);
                    $('#editLeadModal input[name="expected_delivery_date"]').val(data.expected_delivery_date);
                    $('#editLeadModal input[name="follow_up_date"]').val(data.follow_up_date);
                    $('#editLeadModal select[name="status"]').val(data.status);
                    $('#editLeadModal select[name="assigned_to"]').val(data.assigned_to);
                    $('#editLeadModal textarea[name="past_remarks"]').val(data.past_remarks.join('\n'));
                    $('#editLeadModal input[name="current_remark"]').val('');
                    // Clear any previous selection
                    $('#editLeadModal select[name="tags[]"]').val([]);

                    // Set the selected tags from response (data.tags should be an array of strings)
                    $('#editLeadModal select[name="tags[]"]').val(data.tags);

                    // Rebuild multiselect with the selection
                    initTagMultiselect();

                    // Show modal
                    $('#editLeadModal').modal('show');
                },
                error: function () {
                    alert('Failed to load lead data.');
                }
            });
        });
        // When modal is fully shown, initialize the tag dropdown
        $('#editLeadModal').on('shown.bs.modal', function () {
            initTagMultiselect();
            initDaysCalculation(this);
        });

        $('#editLeadForm').validate({
            rules: {
                platform: { required: true },
                lead_date: { required: true, date: true },
                platform_keyword: { required: true },
                buyer_name: { required: true },
                buyer_contact: { required: true, digits: true, minlength: 10, maxlength: 15 },
                buyer_location: { required: true },
                product_detail: { required: true },
                delivery_location: { required: true },
                expected_delivery_date: { required: true, date: true },
                follow_up_date: { required: true, date: true },
                status: { required: true },
                assigned_to: { required: true },
                current_remark: { required: true },
                'tags[]': { required: true }
            },
            messages: {
                buyer_contact: {
                    digits: "Enter only numbers",
                    minlength: "Minimum 10 digits",
                    maxlength: "Maximum 15 digits"
                },
                'tags[]': {
                    required: "Please select at least one tag"
                }
            },
            errorElement: 'span',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).closest('.form-group, .mb-3').addClass('has-error');
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group, .mb-3').removeClass('has-error');
                $(element).removeClass('is-invalid');
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") === "tags[]") {
                    error.insertAfter($('#tags').closest('.form-group, .mb-3'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

    </script>
@stop
