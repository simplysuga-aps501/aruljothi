    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="editProductForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_product_id">

                        <div class="mb-3">
                            <label for="product_name">Name</label>
                            <input type="text" class="form-control" name="name" id="edit_product_name" readonly>
                        </div>
                        <div class="row mb-3">
                            <!--Removing add unit because all the products should be viewed in NOS unit for inventory purposes
                            <div class="col-md-3">
                                 <label for="product_name">Unit</label>
                                 <input type="text" class="form-control" name="name" id="edit_unit_name" readonly>
                            </div>
                            -->
                            <div class="col-md-4">
                                <label for="product_name">HSN Code</label>
                                <input type="text" class="form-control" name="name" id="edit_hsncode_name" readonly>
                            </div>

                            {{-- Quote Price --}}
                           <div class="col-md-4">
                               <label for="quote_price" class="form-label">
                                   Quote Price
                                   <i class="fas fa-info-circle text-primary"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      title="Please enter the price for a single piece of the product"></i>
                               </label>
                               <div class="input-group">
                                   <input type="number" step="0.01" min="0" id="edit_quote_price" name="edit_quote_price"
                                          class="form-control" placeholder="Enter price" required>
                                   <div class="input-group-append">
                                       <span class="input-group-text bg-light">NOS</span>
                                   </div>
                               </div>
                           </div>
                            <div class="col-md-4">
                                <label for="weight">Weight (kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="edit_weight_kg" id="edit_weight_kg">
                            </div>
                        </div>

                        <div class="row" id="editTruckCapacities"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).on('click', '.edit-product', function (e) {
        e.preventDefault();

        let $el = $(this);

        // Read individual attributes
        let id = $el.attr('data-id');
        let name = $el.attr('data-name');
        let unit = $el.attr('data-unit');
        let hsncode = $el.attr('data-hsncode');
        let quote_price = $el.attr('data-quote_price');
        let manufacturing_cost = $el.attr('data-manufacturing_cost');
        let weight = $el.attr('data-weight');
        let truckCaps = JSON.parse($el.attr('data-truck_capacities') || '{}');

        // Populate modal fields
        $('#edit_product_id').val(id);
        $('#edit_product_name').val(name);
        $('#edit_unit_name').val(unit);
        $('#edit_hsncode_name').val(hsncode);
        $('#edit_quote_price').val(quote_price);
        $('#edit_weight_kg').val($(this).attr('data-weight'));

        let $container = $('#editTruckCapacities');
        $container.empty();

        @foreach($truck_types as $truck)
           $container.append(`
             <div class="col-md-4 mb-3">
                 <div class="card p-2">
                     <div class="card-header p-1">
                         <strong>{{ $truck->name }} ({{ $truck->capacity_kg }} kg)</strong>
                     </div>
                     <div class="card-body p-2">
                         <div class="row">
                             <div class="col-6">
                                 <label class="small text-success">With Body</label>
                                 <input type="number" step="1" min="0"
                                     class="form-control form-control-sm truck-pipe-capacity"
                                     data-capacity="{{ $truck->capacity_kg }}"
                                     name="edit_truck_capacities[{{ $truck->id }}][with_body]"
                                     value="${truckCaps['{{ $truck->id }}_with_body'] || 0}">
                             </div>
                             <div class="col-6">
                                 <label class="small text-danger">Without Body</label>
                                 <input type="number" step="1" min="0"
                                     class="form-control form-control-sm truck-pipe-capacity"
                                     data-capacity="{{ $truck->capacity_kg }}"
                                     name="edit_truck_capacities[{{ $truck->id }}][without_body]"
                                     value="${truckCaps['{{ $truck->id }}_without_body'] || 0}">
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

           `);
        @endforeach

        $('#editProductForm').attr('action', '/products/' + id + '/edit');
        $('#editProductModal').modal('show');
    });
    /*
    // Auto-calc capacities on weight change
    $(document).on('input', '#edit_weight_kg', function () {
        let weight = parseFloat($(this).val());
        if (!weight || weight <= 0) return;

        $('.truck-pipe-capacity').each(function () {
            if (!$(this).data('userEdited')) {
                let truckCapacityKg = parseFloat($(this).data('capacity'));
                $(this).val(Math.floor(truckCapacityKg / weight));
            }
        });
    });
    */
    // AJAX submit for Edit Product Modal
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();

        let $form = $(this);
        let url = $form.attr('action'); // /products/{id}
        let data = $form.serialize();   // includes selling price, weight, truck capacities

        $.ajax({
            url: url,
            type: 'PUT',
            data: data,
            success: function(res) {
                if (res.success) {
                    // Hide modal
                    $('#editProductModal').modal('hide');

                    // Option 1: Reload the page
                    location.reload();

                    // Option 2: Update the table row dynamically (if you want)
                    // $('#productsTable').find('tr[data-id="' + res.product_id + '"]')
                    //     .find('.selling-price-cell').text($('#edit_quote_price').val());
                } else {
                    alert('Error: ' + JSON.stringify(res.message));
                }
            },
            error: function(err) {
                console.error(err);
                if (err.responseJSON && err.responseJSON.message) {
                    alert('Validation error: ' + JSON.stringify(err.responseJSON.message));
                } else {
                    alert('Server error occurred.');
                }
            }
        });
    });

    // Mark manual edits
    $(document).on('input', '.truck-pipe-capacity', function () {
        $(this).data('userEdited', true);
    });
    </script>
    @endpush
