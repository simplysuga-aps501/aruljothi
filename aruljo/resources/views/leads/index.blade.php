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

                                      <td data-order="{{ $lead->lead_date_order ?? '' }}">
                                          @if($lead->lead_date_formatted_short)
                                              <span title="{{ $lead->lead_date_formatted_full }}">
                                                  {{ $lead->lead_date_formatted_short }}
                                                  <small class="text-muted">
                                                      ({{ $lead->lead_date_daysago }})
                                                  </small>
                                              </span>
                                          @else
                                              <span class="text-muted">—</span>
                                          @endif
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
                                       <td @if($lead->followup_order) data-order="{{ $lead->followup_order }}" @endif>
                                           @if ($lead->followup_formatted)
                                               <span class="
                                                   {{ $lead->followup_is_today ? 'bg-warning text-dark px-2 py-1 rounded' : '' }}
                                                   {{ $lead->followup_is_past ? 'bg-danger text-white px-2 py-1 rounded' : '' }}
                                               ">
                                                   {{ $lead->followup_formatted }}
                                               </span>
                                           @else
                                               -
                                           @endif
                                       </td>

                                       <td data-order="{{ $lead->last_updated_order }}">
                                           <div class="d-flex align-items-center">
                                               <small style="display:inline-block; min-width:70px;">
                                                   {{ $lead->last_updated_text }}
                                               </small>
                                                <a href="{{ route('leads.audits', $lead->id) }}"
                                                    class="btn btn-xs btn-outline-info ml-1"
                                                    title="View Logs">
                                                    <i class="fas fa-sticky-note"></i>
                                                </a>
                                                @role('admin')
                                                    <i class="fas fa-trash text-danger" style="cursor:pointer; font-size:0.85rem; margin-left:8px;" 
                                                        data-toggle="modal"
                                                        data-target="#deleteModal"
                                                        onclick="setDeleteAction('{{ route('leads.destroy', $lead->id) }}')"></i>
                                                @endrole
                                           </div>

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
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.7/css/columnControl.dataTables.min.css">

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
    <script src="https://cdn.datatables.net/columncontrol/1.0.7/js/dataTables.columnControl.min.js"></script>

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
                // ordering: true, // allow sorting
                columnControl: [['orderAsc', 'orderDesc', 'search']],
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
