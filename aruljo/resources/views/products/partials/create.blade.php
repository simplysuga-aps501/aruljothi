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

                        <div class="row mb-4">
                           {{-- HSN Code --}}
                            <div class="col-md-3">
                                <label for="hsncode_id" class="form-label">HSN Code</label>
                                <select id="hsncode_id" name="hsncode_id" class="form-control" data-toggle="tooltip" required>
                                    <option value="">-- Select HSN Code --</option>
                                    @foreach ($hsncodes as $hsn)
                                        <option value="{{ $hsn->id }}" data-description="{{ $hsn->description }}" title="{{ $hsn->description }}">
                                            {{ $hsn->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quote Price --}}
                           <div class="col-md-4">
                               <label for="quote_price" class="form-label">
                                   Quote Price
                                   <i class="fas fa-info-circle text-primary"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      title="Please enter the price for a single piece"></i>
                               </label>
                               <div class="input-group">
                                   <input type="number" step="0.01" min="0" id="quote_price" name="quote_price"
                                          class="form-control" placeholder="Enter price" required>
                                   <div class="input-group-append">
                                       <span class="input-group-text bg-light">NOS</span>
                                   </div>
                               </div>
                           </div>


                            {{-- Weight --}}
                            <div class="col-md-4">
                                <label for="weight" class="form-label">Weight (kg)
                                     <i class="fas fa-info-circle text-primary"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      title="Please enter the price for a single piece"></i>
                                </label>
                                <input type="number" step="0.01" min="0" id="weight" name="weight" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5 class="mb-3">🚚 Truck Capacity</h5>
                            <div class="row mb-3">
                                @foreach($truck_types->chunk(2) as $truckGroup)
                                    @foreach($truckGroup as $truck)
                                        <div class="col-md-6 mb-3">
                                            <div class="card p-2">
                                                <div class="card-header p-1">
                                                    <strong>{{ $truck->name }} ({{ $truck->capacity_kg }} kg)</strong>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row">
                                                        <!-- With Body -->
                                                        <div class="col-6">
                                                            <label class="small text-success">With Body</label>
                                                            <input type="number" step="1" min="0"
                                                                   class="form-control form-control-sm truck-pipe-capacity"
                                                                   data-capacity="{{ $truck->capacity_kg }}"
                                                                   name="truck_pipe_capacity[{{ $truck->id }}][with_body]"
                                                                   placeholder="Units" required>
                                                        </div>

                                                        <!-- Without Body -->
                                                        <div class="col-6">
                                                            <label class="small text-danger">Without Body</label>
                                                            <input type="number" step="1" min="0"
                                                                   class="form-control form-control-sm truck-pipe-capacity"
                                                                   data-capacity="{{ $truck->capacity_kg }}"
                                                                   name="truck_pipe_capacity[{{ $truck->id }}][without_body]"
                                                                   placeholder="Units" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
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
@push('scripts')
<script>
    // ==================================================
    // GLOBAL STATE
    // ==================================================
    let allConfigs = [];

    $(document).ready(function () {
        // ==================================================
        // TEMPLATE CHANGE → Load Parameters
        // ==================================================
        $('#product_template_id').on('change', function () {
            let templateId = $(this).val();
            $('#parameterFields').empty();
            $('#product_name').val('');
            if (!templateId) return;

            $.ajax({
                url: `/products/template/${templateId}/parameters`,
                method: 'GET',
                success: function (response) {
                    allConfigs = response.configs; // save configs globally
                    console.log(allConfigs);
                    renderParameters(); // render fields
                },
                error: function (xhr) {
                    console.error('Error loading parameters:', xhr.responseText);
                }
            });
        });

        // ==================================================
        // HSN TOOLTIP HANDLING
        // ==================================================
        $('[data-toggle="tooltip"]').tooltip(); // init tooltips

        $('#hsncode_id').on('change', function () {
            const desc = $(this).find(':selected').data('description') || '';
            $(this).attr('title', desc).tooltip('dispose').tooltip(); // refresh tooltip
        });

       // ==================================================
       // AUTO-GENERATE PRODUCT NAME
       // ==================================================
       $('#parameterFields').on('input change', '.param-input, .param-select', function () {
           let parts = [];

           // From number inputs
           $('#parameterFields .param-input[type="number"]').each(function () {
               let val = $(this).val();
               let unit = $(this).closest('.input-group').find('.param-unit').text().trim();
               let desc = $(this).data('description');
               if (val && desc) {
                   if (unit) {
                       parts.push(`${val} ${unit} ${desc.toUpperCase()}`);
                   } else {
                       parts.push(`${val} ${desc.toUpperCase()}`);
                   }
               }
           });

           // From dropdowns
           $('#parameterFields select.param-input').each(function () {
               let val = $(this).val();
               let desc = $(this).data('description');
               if (val && desc) {
                   parts.push(`${val} ${desc.toUpperCase()}`);
               } else if (val) {
                   parts.push(val);
               }
           });

           // Add template name at end
           let templateName = $('#product_template_id option:selected').text();
           if (templateName) {
               parts.push(`- ${templateName}`);
           }

           $('#product_name').val(parts.join(' '));
       });


        // ==================================================
        // CLEAR MODAL FORM
        // ==================================================
        $('#clearProductForm').on('click', function () {
            $('#addProductModal').find('form')[0].reset(); // reset form
            $('#parameterFields').empty(); // clear dynamic fields
            $('#product_name, #quote_price, #weight').val('');
            $('.truck-pipe-capacity').val('');
        });

        // ==================================================
        // SAVE PRODUCT
        // ==================================================
        $('#addProductForm').on('submit', function (e) {
            e.preventDefault();

            let productTemplateId = $('#product_template_id').val();
            let unitId = $('#unit_id').val();
            let hsncodeId = $('#hsncode_id').val();
            let name = $('#product_name').val();
            let quotePrice = parseFloat($('#quote_price').val());
            let weight = parseFloat($('#weight').val());


            // Deduplicate parameters by parameter_id
            let parametersMap = {};

            // Numeric parameters
            $('#parameterFields .param-input[type="number"]').each(function() {
                let paramId = $(this).data('parameter-id');
                let value = $(this).val();
                let unit = $(this).closest('.input-group').find('.param-unit').text().trim(); // optional
                if (paramId && value !== '') {
                    parametersMap[paramId] = { parameter_id: paramId, value: value, unit: unit };
                }
            });

            // Select parameters
            $('#parameterFields .param-select').each(function() {
                let paramId = $(this).data('parameter-id');
                let value = $(this).val();
                if (paramId && value !== '') {
                    parametersMap[paramId] = { parameter_id: paramId, value: value };
                }
            });

            // Convert map to array
            let parameters = Object.values(parametersMap);



            // Truck capacities
            let truckCapacities = {};
            $('.truck-pipe-capacity').each(function () {
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
            console.log(quotePrice);
            // Send data
            $.ajax({
                url: '/products',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                contentType: 'application/json',
                data: JSON.stringify({
                    name,
                    prod_template_id: productTemplateId,
                    unit_id: unitId,
                    hsncode_id: hsncodeId,
                    quote_price: quotePrice,
                    weight_kg: weight,
                    parameters,
                    truck_capacities: truckCapacities
                }),
                success: function () {
                    $('#addProductModal').modal('hide');
                    $('#productSuccessAlert').removeClass('d-none')
                        .find('.alert-body').html('Product created successfully!');
                    setTimeout(() => $('#productSuccessAlert').addClass('d-none'), 5000);
                    $('#addProductForm')[0].reset();
                    setTimeout(() => location.reload(), 1000);
                },
                error: function (xhr) {
                    let msg = 'Failed to save product.';
                    if (xhr.status === 422 && xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#productErrorAlert').removeClass('d-none')
                        .find('.alert-body').html(msg);
                    console.error(xhr.responseText);
                }
            });
        });

    });

    // ==================================================
    // PARAMETER DEPENDENCIES HANDLING
    // ==================================================
    $('#parameterFields').on('change', '.param-select', function () {
        let paramId = $(this).data('parameter-id');
        let selectedOption = $(this).val();

        // Remove old dependencies
        $(`.dependent-of-${paramId}`).remove();
        $(`#dependencies-of-${paramId}`).remove();

        // Find config
        let config = allConfigs.find(c => c.parameter.id == paramId);
        if (!config) return;

        // Find option
        let optionObj = config.parameter.options.find(o => o.parameter_option === selectedOption);
        if (!optionObj) return;

        // Add dependencies
        if (optionObj.dependencies?.length) {
            let $lastInserted = $(this).closest('.col-md-3');
            optionObj.dependencies.forEach(dep => {
                // Check if this parameter already exists in the DOM
                let $existing = $(`#parameterFields [data-parameter-id="${dep.parameter.id}"]`);
                if ($existing.length) {
                    $existing.closest('.form-group').remove(); // remove duplicate
                }

                let html = generateParameterHTML(dep.parameter);
                let $element = $(html).addClass(`dependent-of-${paramId}`);
                $element.insertAfter($lastInserted);
                $lastInserted = $element;
            });
        }
    });

    // ==================================================
    // PARAMETER RENDER FUNCTIONS
    // ==================================================
    function generateParameterHTML(param) {
        let html = `<div class="form-group col-md-3">
                        <label>${param.name}</label>`;

            if (param.input_type === 'select') {
                let options = Array.isArray(param.options) ? param.options : [];
                html += `<select class="form-control param-input param-select"
                                 data-parameter-id="${param.id}"
                                 name="parameters[${param.id}][value]"
                                 data-description="${param.description || ''}">
                            <option value="">-- Select ${param.name} --</option>`;
                options.forEach(option => {
                    html += `<option value="${option.parameter_option}">${option.parameter_option}</option>`;
                });
                html += `</select>`;
            }
            else if (param.input_type === 'number') {
            html += `<div class="input-group">
                        <input type="number" step="0.01" min="0"
                               class="form-control param-input"
                               data-parameter-id="${param.id}"
                               name="parameters[${param.id}][value]"
                               data-description="${param.description || ''}"
                               placeholder="Enter ${param.name}" required>
                        <div class="input-group-append">
                            <span class="input-group-text bg-light param-unit">
                                ${param.unit ? param.unit : ''}
                            </span>
                        </div>
                     </div>`;
        }

        html += `</div>`;
        return html;
    }

    function renderParameters() {
        $('#parameterFields').empty();
        allConfigs.forEach(config => {
            $('#parameterFields').append(generateParameterHTML(config.parameter));
        });
    }
    /*
    // ==================================================
    // AUTO-CALCULATE TRUCK CAPACITY
    // ==================================================
    $('#weight').on('input', function () {
        let weight = parseFloat($(this).val());
        if (!weight || weight <= 0) return;

        $('.truck-pipe-capacity').each(function () {
            let truckCapacityKg = parseFloat($(this).data('capacity'));
            if (!truckCapacityKg) return;

            let autoValue = Math.floor(truckCapacityKg / weight);
            if (!$(this).data('userEdited')) {
                $(this).val(autoValue);
            }
        });
    });

    $('.truck-pipe-capacity').on('input', function () {
        $(this).data('userEdited', true);
    });
    */

</script>
@endpush


