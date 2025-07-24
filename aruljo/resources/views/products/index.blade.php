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
    <div class="mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">Add Product</button>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#addUnitModal">Add Unit</button>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#addHSNCodeModal">Add HSN Code</button>
    </div>

    {{-- Products Table --}}
    <table id="productsTable" class="table table-bordered table-striped">
        <thead>
            <tr>
               <!-- <th>SKU</th>-->
                <th>Name</th>
                <th>Unit</th>
                <th>HSN Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                   <!-- <td>{{ $product->sku }}</td>-->
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->unit->name ?? '' }}</td>
                    <td>{{ $product->hsncode->name ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Add Product Modal --}}
    <div id="productAlert"></div>
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addProductForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        {{-- Product Template --}}
                        <div class="mb-3">
                            <label for="product_template_id" class="form-label">Product Template</label>
                            <select id="product_template_id" class="form-control">
                                <option value="">-- Select Template --</option>
                                @foreach ($product_templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dynamic Parameter Fields --}}
                        <div class="row" id="parameterFields"></div>

                        {{-- Final Product Name --}}
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Final Product Name</label>
                            <input type="text" id="product_name" name="name" class="form-control" readonly>
                        </div>

                        {{-- Unit --}}
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select id="unit_id" class="form-control">
                                <option value="">-- Select Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- HSN Code --}}
                        <div class="mb-3">
                            <label for="hsncode_id" class="form-label">HSN Code</label>
                            <select id="hsncode_id" class="form-control">
                                <option value="">-- Select HSN Code --</option>
                                @foreach ($hsncodes as $hsn)
                                    <option value="{{ $hsn->id }}">{{ $hsn->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="clearProductForm">
                            Clear
                        </button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Unit Modal --}}
    <div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addUnitForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Unit</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="name" class="form-control" placeholder="Enter Unit Name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Unit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add HSN Code Modal --}}
    <div class="modal fade" id="addHSNCodeModal" tabindex="-1" aria-labelledby="addHSNCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addHSNForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add HSN Code</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Enter HSN Code" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="description" class="form-control" placeholder="Enter Description (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save HSN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
    $(document).ready(function () {
        // Handle template change
        $('#product_template_id').on('change', function () {
            let templateId = $(this).val();
            $('#parameterFields').empty();

            if (!templateId) return;

            $.ajax({
                url: `/products/template/${templateId}/parameters`,
                method: 'GET',
                success: function (response) {
                    response.parameters.forEach(function (param) {
                        let html = `<div class="col-md-3 col-12 mb-3">
                            <label class="form-label">${param.name}</label>`;

                        if (param.input_type === 'number') {
                            html += `
                                <div class="input-group">
                                    <input type="number" class="form-control param-input" data-parameter-id="${param.id}" placeholder="Enter ${param.name}">
                                    <select class="form-control param-unit">
                                        ${param.units.map(unit => `<option value="${unit}">${unit}</option>`).join('')}
                                    </select>
                                </div>`;
                        } else {
                            html += `
                                <select class="form-control param-select" data-parameter-id="${param.id}">
                                    <option value="">-- Select --</option>
                                    ${param.options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                                </select>`;
                        }

                        html += `</div>`;
                        $('#parameterFields').append(html);
                    });
                },
                error: function (xhr) {
                    console.error('Error loading parameters:', xhr.responseText);
                }
            });
        });

        // Update product name live based on parameter input
        $('#parameterFields').on('input change', '.param-input, .param-select, .param-unit', function () {
            let parts = [];

            $('#parameterFields .param-input').each(function () {
                let val = $(this).val();
                let unit = $(this).closest('.input-group').find('.param-unit').val();
                let label = $(this).closest('.mb-3').find('label').text().trim();
                if (val && unit && label) {
                    parts.push(`${val} ${unit} ${label.toUpperCase().split(' ')[0]}`);
                }
            });

            $('#parameterFields .param-select').each(function () {
                let val = $(this).val();
                if (val) parts.push(val);
            });
        // Add template name
            let templateName = $('#product_template_id option:selected').text();
            if (templateName) {
                parts.push(`- ${templateName}`);
            }

            $('#product_name').val(parts.join(' '));
        });

        // Add Unit
        $('#addUnitForm').on('submit', function (e) {
            e.preventDefault();
            let name = $(this).find('input[name="name"]').val();

            $.post('/units', { name, _token: '{{ csrf_token() }}' }, function (unit) {
                $('#unit_id').append(`<option value="${unit.id}">${unit.name}</option>`);
                $('#unit_id').val(unit.id);
                $('#addUnitModal').modal('hide');
                $('#addUnitForm')[0].reset();
            }).fail(function (xhr) {
                alert('Failed to add unit. Make sure the name is filled.');
                console.log(xhr.responseText);
            });
        });

        // Add HSN Code
        $('#addHSNForm').on('submit', function (e) {
            e.preventDefault();
            let name = $(this).find('input[name="name"]').val();
            let description = $(this).find('textarea[name="description"]').val();

            $.post('/hsncodes', { name, description, _token: '{{ csrf_token() }}' }, function (hsn) {
                $('#hsncode_id').append(`<option value="${hsn.id}">${hsn.name}</option>`);
                $('#hsncode_id').val(hsn.id);
                $('#addHSNCodeModal').modal('hide');
                $('#addHSNForm')[0].reset();
            }).fail(function (xhr) {
                alert('Failed to add HSN Code. Ensure "name" is filled.');
                console.log(xhr.responseText);
            });
        });
        //Clear Modal
        $('#clearProductForm').on('click', function () {
            // Reset form fields
            $('#addProductModal').find('form')[0].reset();

            // Clear parameter fields
            $('#parameterFields').empty();

            // Clear auto-generated name
            $('#product_name').val('');
        });

        // Save Product
        $('#addProductForm').on('submit', function (e) {
            e.preventDefault();

            let productTemplateId = $('#product_template_id').val();
            let unitId = $('#unit_id').val();
            let hsncodeId = $('#hsncode_id').val();
            let name = $('#product_name').val();

            // Build parameters array
            let parameters = [];

            // Numeric input parameters
            $('#parameterFields .param-input').each(function () {
                let value = $(this).val();
                let unit = $(this).closest('.input-group').find('.param-unit').val();
                let paramId = $(this).data('parameter-id');

                if (paramId && value !== '') {
                    parameters.push({
                        parameter_id: paramId,
                        value: value,
                        unit: unit
                    });
                }
            });

            // Dropdown parameters
            $('#parameterFields .param-select').each(function () {
                let value = $(this).val();
                let paramId = $(this).data('parameter-id');

                if (paramId && value !== '') {
                    parameters.push({
                        parameter_id: paramId,
                        value: value
                    });
                }
            });

            $.ajax({
                url: '/products',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    name: name,
                    product_template_id: productTemplateId,
                    unit_id: unitId,
                    hsncode_id: hsncodeId,
                    parameters: parameters
                }),
                success: function (res) {
                    $('#addProductModal').modal('hide');
                    $('#productAlert').html(`
                        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                            Product created successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `);
                    $('#addProductForm')[0].reset();
                    setTimeout(() => location.reload(), 1000);
                },
                error: function (xhr) {
                    $('#productAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                            Failed to save product.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `);
                    console.error(xhr.responseText);
                }

            });
        });

        // Initialize DataTable (optional, if you want)
        $('#productsTable').DataTable();
    });
</script>
@stop
