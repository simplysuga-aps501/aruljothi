@extends('adminlte::page')

@section('title', 'Transport Offices')

@section('content_header')
    <h1>Transport Offices</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">All Transport Offices</h3>
        <div class="ml-auto">
            <button class="btn btn-primary " data-toggle="modal" data-target="#addOfficeModal">
                <i class="fas fa-plus"></i> Add New Office
            </button>
        </div>
    </div>


    <div class="card-body">
        <table id="officesTable" class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Rate/km</th>
                    <th>Preferred Districts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offices as $index => $office)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $office->name }}</td>
                        <td>{{ $office->contact_person }}</td>
                        <td>{{ $office->phone }}</td>
                        <td>{{ $office->email }}</td>
                        <td>{{ $office->default_per_km_rate ?? '-' }}</td>
                        <td>{{ $office->preferred_districts_list ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('tp_offices.destroy', $office->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this office?')">
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

{{-- ==================== INCLUDE CREATE MODAL ==================== --}}
@include('transport.offices.create')
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/css/bootstrap-multiselect.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
@stack('css');
@stop

@section('js')
<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#officesTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
    });

    // Load districts dynamically
    $('#state').change(function() {
        let state = $(this).val();
        let $districtSelect = $('#district');
        $districtSelect.html('<option>Loading...</option>');

        if (state) {
            $.get('/transport/get-districts/' + state, function(data) {
                let options = '';
                data.forEach(d => {
                    options += `<option value="${d.district}">${d.district}</option>`;
                });
                $districtSelect.html(options);
            });
        } else {
            $districtSelect.html('<option value="">-- Select District --</option>');
        }
    });
});

</script>
@stack('scripts');
@stop
