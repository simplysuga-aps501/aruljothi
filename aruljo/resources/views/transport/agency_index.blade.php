@extends('adminlte::page')

@section('title', 'Truck Agencies')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>Truck Agencies</h3>
        <a href="#" class="btn btn-success mb-2" data-toggle="modal" data-target="#addAgencyModal">
            Add Agency
        </a>
    </div>

    {{-- Agencies Table --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Agency Name</th>
                <th>Rates</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agencies as $agency)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $agency->name }}</td>
                    <td>
                        <ul>
                            @foreach($agency->agencyRates as $rate)
                                <li>
                                    {{ $rate->truckType->name ?? '' }}:
                                    {{ $rate->location->place ?? '' }} -
                                    ₹{{ number_format($rate->rate,2) }}
                                </li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

{{-- Include the Add Agency Modal --}}
@include('transport.agency_create')

@endsection

@section('js')
    <!-- Multiselect JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.0/dist/js/bootstrap-multiselect.min.js"></script>
    @stack('scripts')
@stop
