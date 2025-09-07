@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Products</h1>
    @push('css')
        <style>
            input.form-control,
            select.form-control,
            .input-group-text,
            .form-control-plaintext {
                text-transform: uppercase;
            }

            label {
                text-transform: none;
            }
        </style>
    @endpush
@stop

@section('content')
    {{-- Action Buttons --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group">
            <!--Removing add unit because all the products should be viewed in NOS unit for inventory purposes
            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#addUnitModal">
                <i class="fas fa-balance-scale"></i> Add Unit
            </button>
            -->
            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#addHSNCodeModal">
                <i class="fas fa-barcode"></i> Add HSN Code
            </button>
        </div>
        <button class="btn btn-success" data-toggle="modal" data-target="#addProductModal">
            <i class="fas fa-box"></i> Add Product
        </button>
    </div>

    <x-adminlte-alert theme="success" id="productSuccessAlert" title="Success" class="d-none" dismissable>
        Product created successfully!
    </x-adminlte-alert>

    {{-- Products Table --}}
    <div class="table-responsive">
        <table id="productsTable" class="table table-bordered table-striped table-hover nowrap text-sm w-100">
            <thead>
                <tr>
                    <th>S. No.</th>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <a href="#"
                       class="edit-product"
                       title="{{ $product->name }}"
                       data-id="{{ $product->id }}"
                       data-name="{{ $product->name }}"
                       data-sku="{{ $product->sku }}"
                       data-unit="NOS"
                       data-hsncode="{{ $product->hsncode->name}}"
                       data-quote_price="{{ $product->quote_price }}"
                       data-manufacturing_cost="{{ $product->manufacturing_cost }}"
                       data-weight="{{ $product->weight_kg }}"
                       data-truck_capacities='@json($product->truckCapacities->mapWithKeys(function($tc) {
                           return [$tc->truck_type_id . "_" . $tc->body_type => $tc->max_units];
                       }))'>
                        {{ $product->sku }}
                    </a>
                </td>
            <td>{{ strtoupper($product->template->name ?? '') }}</td>

                <td>
                    <div class="btn-group btn-group-sm">
                        <x-adminlte-button theme="outline-danger" icon="fas fa-trash"
                            data-toggle="modal"
                            data-target="#deleteModal"
                            onclick="setDeleteAction('{{ route('products.destroy', $product->id) }}')" />
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@include('products.partials.create')
@include('products.partials.edit')
<!--Removing add unit because all the products should be viewed in NOS unit for inventory purposes-->
<!--@include('products.partials.unit')-->
@include('products.partials.hsn')
@include('products.partials.delete')
@stop
@section('css')
    <!--Datatable CSS-->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.7/css/columnControl.dataTables.min.css">
@stop

@section('js')
    <!--Datatable JS-->
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.7/js/dataTables.columnControl.min.js"></script>
    <script>
        $(document).ready(function () {
            new DataTable('#productsTable', {
                responsive: true,
                stateSave: true,
                order: [],
                language: { emptyTable: "No Products to display" },
                pageLength: 25, // default selection
                lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],

            });
      });
    </script>
    @stack('scripts');
@stop
