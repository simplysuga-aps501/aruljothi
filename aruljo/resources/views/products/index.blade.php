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
    <x-adminlte-alert theme="success" id="productSuccessAlert" title="Success" class="d-none" dismissable>
        Product created successfully!
    </x-adminlte-alert>

    {{-- Products Table --}}
    <table id="productsTable" class="table table-bordered table-striped">
        <thead>
            <tr class="text-nowrap">
                <!-- <th>SKU</th>-->
                <th>S. No.</th>
                <th>Name</th>
                <th>Product</th>
                <th>Unit</th>
                <th>HSN Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <!-- <td>{{ $product->sku }}</td>-->
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ strtoupper($product->productTemplate->name ?? '') }}</td>
                    <td>{{ $product->unit->name ?? '' }}</td>
                    <td>{{ $product->hsncode->name ?? '' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <x-adminlte-button theme="outline-danger" icon="fas fa-trash" data-toggle="modal"
                                data-target="#deleteModal"
                                onclick="setDeleteAction('{{ route('products.destroy', $product->id) }}')" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Delete Product Modal --}}
    <x-adminlte-modal id="deleteModal" title="Confirm Delete" theme="danger" icon="fas fa-exclamation-triangle"
        size="md">
        <p class="text-center">Are you sure you want to delete this product?</p>
        <x-slot name="footerSlot">
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <x-adminlte-button label="Yes, Delete" type="submit" theme="danger" icon="fas fa-trash" />
                <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancel</button>
            </form>
        </x-slot>
    </x-adminlte-modal>

    {{-- Add Product Modal --}}
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
                            <select id="product_template_id" class="form-control" required>
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
                            <select id="unit_id" class="form-control" required>
                                <option value="">-- Select Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- HSN Code --}}
                        <div class="mb-3">
                            <label for="hsncode_id" class="form-label">HSN Code</label>
                            <select id="hsncode_id" class="form-control" data-toggle="tooltip" title="" required>
                                <option value="">-- Select HSN Code --</option>
                                @foreach ($hsncodes as $hsn)
                                    <option value="{{ $hsn->id }}" data-description="{{ $hsn->description }}"
                                        title="{{ $hsn->description }}">
                                        {{ $hsn->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            {{-- Selling Price --}}
                            <div class="col-md-4 mb-3">
                                <label for="selling_price" class="form-label">Selling Price</label>
                                <input type="number" step="0.01" min="0" id="selling_price" class="form-control" required>
                            </div>

                            {{-- Manufacturing Cost --}}
                            <div class="col-md-4 mb-3">
                                <label for="manufacturing_cost" class="form-label">Manufacturing Cost</label>
                                <input type="number" step="0.01" min="0" id="manufacturing_cost" class="form-control" required>
                            </div>

                            {{-- Weight --}}
                            <div class="col-md-4 mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.01" min="0" id="weight" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            @foreach($truck_types->chunk(2) as $truckGroup)
                                @foreach($truckGroup as $truck)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-block">
                                            {{ $truck->name }} ({{ $truck->capacity_kg }} kg)
                                        </label>
                                        <div class="row">
                                            <!-- With Body -->
                                            <div class="col-md-6">
                                                <input type="number" step="1" min="0"
                                                       class="form-control truck-pipe-capacity"
                                                       name="truck_pipe_capacity[{{ $truck->id }}][with_body]"
                                                       placeholder="With Body Units" required>
                                            </div>

                                            <!-- Without Body -->
                                            <div class="col-md-6">
                                                <input type="number" step="1" min="0"
                                                       class="form-control truck-pipe-capacity"
                                                       name="truck_pipe_capacity[{{ $truck->id }}][without_body]"
                                                       placeholder="Without Body Units" required>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>

                    </div>

                    {{-- AdminLTE Styled Success/Error Alert --}}

                    <x-adminlte-alert theme="danger" id="productErrorAlert" title="Error" class="d-none" dismissable>
                        <span class="alert-body">Failed to save product.</span>
                    </x-adminlte-alert>


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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Unit</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    {{-- Add Unit Form --}}
                    <form id="addUnitForm" class="mb-3">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Enter Unit Name" required>
                        <button type="submit" class="btn btn-primary btn-block">Save Unit</button>
                    </form>

                    {{-- Existing Units List --}}
                    <h6>Available Units</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Unit Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td>{{ $unit->name }}</td>
                                    <td>
                                        @if($unit->products->count() == 0)
                                            <form method="POST" action="{{ route('units.destroy', $unit->id) }}" class="d-inline delete-unit-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="unitAlert"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add HSN Code Modal --}}
    <div class="modal fade" id="addHSNCodeModal" tabindex="-1" aria-labelledby="addHSNCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add HSN Code</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    {{-- Add HSN Form --}}
                    <form id="addHSNForm" class="mb-3">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Enter HSN Code" required>
                        <textarea name="description" class="form-control mb-2" placeholder="Enter Description (optional)"></textarea>
                        <button type="submit" class="btn btn-primary btn-block">Save HSN</button>
                    </form>

                    {{-- Existing HSN Codes List --}}
                    <h6>Available HSN Codes</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>HSN Code</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hsncodes as $hsn)
                                <tr>
                                    <td>{{ $hsn->name }}</td>
                                    <td>{{ $hsn->description }}</td>
                                    <td>
                                        @if($hsn->products->count() == 0)
                                            <form method="POST" action="{{ route('hsncodes.destroy', $hsn->id) }}" class="d-inline delete-hsn-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="hsnAlert"></div>
                </div>
            </div>
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
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>

    <script>
        let allConfigs = [];
        $(document).ready(function() {
            // Handle template change
            $('#product_template_id').on('change', function() {
                let templateId = $(this).val();
                $('#parameterFields').empty();
                $('#product_name').val('');
                if (!templateId) return;
                $.ajax({
                    url: `/products/template/${templateId}/parameters`,
                    method: 'GET',
                    success: function(response) {
                        // Save configs globally
                        allConfigs = response.configs;
                        console.log(allConfigs);
                        // Call render function
                        renderParameters();
                    },
                    error: function(xhr) {
                        console.error('Error loading parameters:', xhr.responseText);
                    }
                });
            });
            //hsn tooltip

            // Initialize tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // Update tooltip text when selection changes
            $('#hsncode_id').on('change', function() {
                const desc = $(this).find(':selected').data('description') || '';
                $(this).attr('title', desc).tooltip('dispose').tooltip(); // Refresh tooltip
            });

            // Update product name live based on parameter input
            $('#parameterFields').on('input change', '.param-input, .param-select, .param-unit', function() {
                let parts = [];

                $('#parameterFields .param-input').each(function() {
                    let val = $(this).val();
                    let unit = $(this).closest('.input-group').find('.param-unit').val();
                    let desc = $(this).attr('data-description');
                    if (val && unit && desc) {
                        parts.push(`${val} ${unit} ${desc.toUpperCase().split(' ')[0]}`);
                    }
                });

                $('#parameterFields .param-select').each(function() {
                    let val = $(this).val();
                    let desc = $(this).attr('data-description');
                    if (val && desc) {
                        parts.push(`${val} ${desc.toUpperCase().split(' ')[0]}`);
                    }
                    else if (val) parts.push(val);
                });
                // Add template name
                let templateName = $('#product_template_id option:selected').text();
                if (templateName) {
                    parts.push(`- ${templateName}`);
                }

                $('#product_name').val(parts.join(' '));
            });

            // Add Unit
            $('#addUnitForm').on('submit', function(e) {
                e.preventDefault();
                let name = $(this).find('input[name="name"]').val();

                $.post('/units', {
                    name,
                    _token: '{{ csrf_token() }}'
                }, function(unit) {
                    $('#unit_id').append(`<option value="${unit.id}">${unit.name}</option>`);
                    $('#unit_id').val(unit.id);
                    $('#addUnitModal').modal('hide');
                    $('#addUnitForm')[0].reset();

                    $('#unitAlert').html(`
                   <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                       Unit added successfully!
                       <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
               `);
                    setTimeout(() => location.reload(), 1000);
                }).fail(function(xhr) {
                    let msg = 'Failed to add unit.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors?.name?.length) {
                        msg = xhr.responseJSON.errors.name[0]; // "This unit already exists."
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#unitAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                            ${msg}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    `);
                });
            });
            //Delete Unit
            $('#addUnitModal').on('submit', '.delete-unit-form', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this unit?')) return;

                let form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function() {
                        form.closest('tr').remove();
                    },
                    error: function(xhr) {
                        alert('Cannot delete: unit attached to products.');
                        console.error(xhr.responseText);
                    }
                });
            });

            // Add HSN Code
            $('#addHSNForm').on('submit', function(e) {
                e.preventDefault();
                let name = $(this).find('input[name="name"]').val();
                let description = $(this).find('textarea[name="description"]').val();

                $.post('/hsncodes', {
                    name,
                    description,
                    _token: '{{ csrf_token() }}'
                }, function(hsn) {
                    $('#hsncode_id').append(`<option value="${hsn.id}">${hsn.name}</option>`);
                    $('#hsncode_id').val(hsn.id);
                    $('#addHSNCodeModal').modal('hide');
                    $('#addHSNForm')[0].reset();

                    $('#hsnAlert').html(`
                   <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                       HSN Code added successfully!
                       <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
               `);
                    setTimeout(() => location.reload(), 1000);
                }).fail(function(xhr) {
                    let msg = 'Failed to add HSN Code.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors?.name?.length) {
                        msg = xhr.responseJSON.errors.name[0]; // "This HSN code already exists."
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#hsnAlert').html(`
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                            ${msg}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    `);
                });
            });
            // Delete HSN Code
            $('#addHSNCodeModal').on('submit', '.delete-hsn-form', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this HSN code?')) return;

                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: 'DELETE', // use actual DELETE
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        form.closest('tr').remove();
                    },
                    error: function(xhr) {
                        alert('Cannot delete: HSN code attached to products.');
                        console.error(xhr.responseText);
                    }
                });
            });


            //Clear Modal
            $('#clearProductForm').on('click', function() {
                // Reset form fields
                $('#addProductModal').find('form')[0].reset();

                // Clear parameter fields
                $('#parameterFields').empty();

                // Clear auto-generated name
                $('#product_name').val('');
                $('#selling_price').val('');
                $('#manufacturing_cost').val('');
                $('#weight').val('');
                $('.truck-pipe-capacity').val('');
            });

          // Save Product
          $('#addProductForm').on('submit', function(e) {
              e.preventDefault();

              let productTemplateId = $('#product_template_id').val();
              let unitId = $('#unit_id').val();
              let hsncodeId = $('#hsncode_id').val();
              let name = $('#product_name').val();
              let sellingPrice = parseFloat($('#selling_price').val());
              let manufacturingCost = parseFloat($('#manufacturing_cost').val());
              let weight = parseFloat($('#weight').val());

              // Build parameters array
              let parameters = [];

              // Numeric input parameters
              $('#parameterFields .param-input').each(function() {
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
              $('#parameterFields .param-select').each(function() {
                  let value = $(this).val();
                  let paramId = $(this).data('parameter-id');

                  if (paramId && value !== '') {
                      parameters.push({
                          parameter_id: paramId,
                          value: value
                      });
                  }
              });

              // Build truck capacities object
              // Build truck capacities object
              let truckCapacities = {};

              $('.truck-pipe-capacity').each(function() {
                  let name = $(this).attr('name');
                  let match = name.match(/truck_pipe_capacity\[(\d+)\]\[(with_body|without_body)\]/);

                  if (match) {
                      let truckId = match[1];
                      let variant = match[2];
                      let capacity = parseFloat($(this).val()) || 0;

                      if (!truckCapacities[truckId]) {
                          truckCapacities[truckId] = {};
                      }

                      truckCapacities[truckId][variant] = capacity;
                  }
              });


              // Send data to backend
              $.ajax({
                  url: '/products',
                  method: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  },
                  contentType: 'application/json',
                  data: JSON.stringify({
                      name: name,
                      prod_template_id: productTemplateId,
                      unit_id: unitId,
                      hsncode_id: hsncodeId,
                      selling_price: sellingPrice,
                      manufacturing_cost: manufacturingCost,
                      weight_kg: weight,
                      parameters: parameters,
                      truck_capacities: truckCapacities
                  }),
                  success: function(res) {
                      $('#addProductModal').modal('hide');

                      // Show success alert
                      $('#productSuccessAlert')
                          .removeClass('d-none')
                          .find('.alert-body').html('Product created successfully!');

                      setTimeout(() => {
                          $('#productSuccessAlert').addClass('d-none');
                      }, 5000);

                      $('#addProductForm')[0].reset();

                      // Reload after short delay
                      setTimeout(() => location.reload(), 1000);
                  },

                  error: function(xhr) {
                      let msg = 'Failed to save product.';
                      if (xhr.status === 422 && xhr.responseJSON?.message) {
                          msg = xhr.responseJSON.message;
                      }

                      $('#productErrorAlert')
                          .removeClass('d-none')
                          .find('.alert-body').html(msg);

                      console.error(xhr.responseText);
                  }
              });
          });
            // Initialize DataTable (optional, if you want)
            $('#productsTable').DataTable();
        });


        // Listen for changes on any dynamically created select
        $('#parameterFields').on('change', '.param-select', function() {
            console.log("change function called");
            let paramId = $(this).data('parameter-id'); // Current parameter id
            let selectedOption = $(this).val(); // Option selected by user
            console.log("selectedOption.."+selectedOption);
            // Remove any previously added dependent parameters for this parameter
            $(`.dependent-of-${paramId}`).remove();
            // Find the config corresponding to this parameter
            let config = allConfigs.find(c => c.parameter.id == paramId);
            if (!config) return;
            console.log("config..",config);
            // Find the option object inside this parameter
            let optionObj = config.parameter.options.find(o => o.parameter_option === selectedOption);
            if (!optionObj) return;
            console.log("optionObj..",optionObj);
            // Clear any previously added dependent parameters for this option
            $(`#dependencies-of-${paramId}`).remove();

            // If this option has dependencies, append them
            if (optionObj.dependencies && optionObj.dependencies.length) {
                let $lastInserted = $(this).closest('.col-md-3');

                optionObj.dependencies.forEach(dep => {
                    let depParam = dep.parameter;
                    let html = generateParameterHTML(depParam);
                    let $element = $(html).addClass(`dependent-of-${paramId}`);

                    $element.insertAfter($lastInserted);  // insert after the last inserted element
                    $lastInserted = $element;             // update last inserted
                });

            }
        });

        // Function to generate HTML for a parameter
        function generateParameterHTML(param) {
            // Ensure options and units arrays exist
            let options = Array.isArray(param.options) ? param.options : [];
            let units = Array.isArray(param.units) ? param.units : [];

            let html = `<div class="col-md-3 col-12 mb-3">
                            <label class="form-label">${param.name}</label>`;

            if (param.input_type === 'number') {
                html += `<div class="input-group">
                            <input type="number" step="0.01" min="0" class="form-control param-input"
                                   data-parameter-id="${param.id}"
                                   name="parameters[${param.id}][value]"
                                   data-description="${param.description || ''}"
                                   placeholder="Enter ${param.name}" required>`;

                if (units.length) {
                    html += `<select class="form-control param-unit">
                                ${units.map(u => `<option value="${u.name}">${u.name}</option>`).join('')}
                             </select>`;
                } else {
                    html += `<select class="form-control param-unit"><option value="">-- No Units --</option></select>`;
                }

                html += `</div>`;
            }
            else if (param.input_type === 'select') {
                html += `<select class="form-control param-select"
                               data-parameter-id="${param.id}"
                               name="parameters[${param.id}][value]"
                               data-description="${param.description || ''}" required>
                            <option value="">-- Select --</option>
                            ${options.map(opt => `<option value="${opt.parameter_option}">${opt.parameter_option}</option>`).join('')}
                         </select>`;
            }

            html += `</div>`;
            return html;
        }



        // Render all parameters
        function renderParameters() {

            $('#parameterFields').empty();
            allConfigs.forEach(config => {
                let param = config.parameter;
                let html = generateParameterHTML(param); // call separate function
                $('#parameterFields').append(html);
            });
        }

        //Delete a product
        function setDeleteAction(actionUrl) {
            document.getElementById('deleteForm').setAttribute('action', actionUrl);
        }
    </script>
@stop
