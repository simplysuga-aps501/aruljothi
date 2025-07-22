@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Products</h1>
@stop

@section('content')

    {{-- Top Action Buttons --}}
    <div class="mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#addProductModal">Add Product</button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">Add Unit</button>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#addHsnModal">Add HSN Code</button>
    </div>

    {{-- Product Table --}}
    <x-adminlte-datatable id="productsTable" :heads="['ID', 'SKU', 'Name', 'Category', 'Stock', 'Unit', 'HSN Code']" theme="light" striped hoverable>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->stock_count }}</td>
                <td>{{ optional($product->unit)->name }}</td>
                <td>{{ optional($product->hsncode)->name }}</td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

    {{-- Add Unit Modal --}}
    <x-adminlte-modal id="addUnitModal" title="Add Unit" theme="primary">
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            <x-adminlte-input name="name" label="Unit Name" required />
            <x-adminlte-button type="submit" label="Save" theme="primary" />
        </form>
    </x-adminlte-modal>

    {{-- Add HSN Modal --}}
    <x-adminlte-modal id="addHsnModal" title="Add HSN Code" theme="secondary">
        <form action="{{ route('hsncodes.store') }}" method="POST">
            @csrf
            <x-adminlte-input name="name" label="HSN Code" required />
            <x-adminlte-textarea name="description" label="Description" />
            <x-adminlte-button type="submit" label="Save" theme="secondary" />
        </form>
    </x-adminlte-modal>

    {{-- Add Product Modal --}}
    <x-adminlte-modal id="addProductModal" title="Add Product" theme="success" size="lg">
        <form action="{{ route('products.store') }}" method="POST" id="productForm">
            @csrf

            <x-adminlte-select name="product_template_id" label="Select Template" id="templateSelect" required>
                <option value="">-- Select Template --</option>
                @foreach($product_templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </x-adminlte-select>

            <div id="parameterFields"></div>

            <x-adminlte-input name="sku" label="SKU" id="skuField" required />
            <x-adminlte-input name="name" label="Product Name" id="productNameField" required />

            <x-adminlte-select name="unit_id" label="Unit">
                <option value="">-- Select --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </x-adminlte-select>

            <x-adminlte-select name="hsncode_id" label="HSN Code">
                <option value="">-- Select --</option>
                @foreach($hsncodes as $hsn)
                    <option value="{{ $hsn->id }}">{{ $hsn->name }}</option>
                @endforeach
            </x-adminlte-select>

            <x-adminlte-input name="stock_count" type="number" label="Stock Count" value="0" />

            <x-adminlte-button type="submit" label="Save Product" theme="success" />
        </form>
    </x-adminlte-modal>

@stop

@section('js')
    <script>
        $('#templateSelect').on('change', function () {
            let templateId = $(this).val();
            if (!templateId) return;

            $.get('/product-templates/' + templateId + '/parameters', function (data) {
                let fieldsHtml = '';
                let nameParts = [];

                data.parameters.forEach(param => {
                    fieldsHtml += `<div class="form-group">
                        <label>${param.name}</label>
                        <select name="parameters[${param.id}]" class="form-control param-input" data-unit="${param.unit_name}">
                            ${param.options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                        </select>
                    </div>`;
                });

                $('#parameterFields').html(fieldsHtml);

                $('.param-input').on('change', function () {
                    let nameString = '';
                    $('.param-input').each(function () {
                        let unit = $(this).data('unit') || '';
                        nameString += $(this).val() + (unit ? unit : '') + ' ';
                    });
                    nameString += $('#templateSelect option:selected').text();
                    $('#productNameField').val(nameString.trim());
                });
            });
        });
    </script>
@stop
