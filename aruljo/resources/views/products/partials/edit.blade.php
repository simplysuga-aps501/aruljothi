<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_product_id">

                    <div class="mb-3">
                        <label for="edit_product_name">Name</label>
                        <input type="text" class="form-control" id="edit_product_name" name="name" readonly>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_hsncode_name">HSN Code</label>
                            <input type="text" class="form-control" id="edit_hsncode_name" readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_quote_price" class="form-label">
                                Quote Price
                                <i class="fas fa-info-circle text-primary"
                                   data-toggle="tooltip"
                                   title="Price for a single unit"></i>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       id="edit_quote_price" name="edit_quote_price"
                                       class="form-control" placeholder="Enter price" required>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light">NOS</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_weight_kg">Weight (kg)</label>
                            <input type="number" step="0.01" min="0"
                                   id="edit_weight_kg" name="edit_weight_kg"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Truck Capacities --}}
                    <div class="row" id="editTruckCapacities">
                        @foreach($truck_types as $truck)
                            <div class="col-md-4 mb-3">
                                <div class="card p-2">
                                    <div class="card-header p-1">
                                        <strong>{{ $truck->name }} ({{ $truck->capacity_kg }} kg)</strong>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row">
                                            @php
                                                $colors = ['text-success','text-danger', 'text-primary', 'text-warning', 'text-info', 'text-secondary'];
                                            @endphp

                                            @foreach($body_types as $index => $bodyType)
                                             <div class="col-6">
                                                <label class="small {{ $colors[$index % count($colors)] }}">
                                                    {{ ucwords(str_replace('_', ' ', $bodyType)) }}
                                                </label>

                                                <input type="number" step="1" min="0"
                                                       class="form-control form-control-sm truck-pipe-capacity"
                                                       data-capacity="{{ $truck->capacity_kg }}"
                                                       data-truck-id="{{ $truck->id }}"
                                                       data-body-type="{{ $bodyType }}"
                                                       name="edit_truck_capacities[{{ $truck->id }}][{{ $bodyType }}]"
                                                       value="0">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        Save Changes
                    </button>
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

    // Read attributes
    let id          = $el.data('id');
    let name        = $el.data('name');
    let hsncode     = $el.data('hsncode');
    let quotePrice  = $el.data('quote_price');
    let weight      = $el.data('weight');
    let truckCaps   = $el.data('truck_capacities');

    if (typeof truckCaps === "string") {
        try { truckCaps = JSON.parse(truckCaps); }
        catch { truckCaps = {}; }
    }

    // Populate modal fields
    $('#edit_product_id').val(id);
    $('#edit_product_name').val(name);
    $('#edit_hsncode_name').val(hsncode);
    $('#edit_quote_price').val(quotePrice);
    $('#edit_weight_kg').val(weight);

    // Fill in truck capacities
    for (let key in truckCaps) {
        // Keys are like "1_open_body_truck"
        let underscoreIndex = key.indexOf('_');
        if (underscoreIndex === -1) continue;

        let truckId   = key.substring(0, underscoreIndex);
        let bodyType  = key.substring(underscoreIndex + 1); // keeps "open_body_truck"

        $(`input[data-truck-id="${truckId}"][data-body-type="${bodyType}"]`)
            .val(truckCaps[key]);
    }


    $('#editProductForm').attr('action', '/products/' + id + '/edit');
    $('#editProductModal').modal('show');
});

// Handle form submit
$('#editProductForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: $(this).attr('action'),
        type: 'PUT',
        data: $(this).serialize(),
        success: function(res) {
            if (res.success) {
                $('#editProductModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(err) {
            console.error(err);
            let msg = err.responseJSON?.message || 'Server error occurred.';
            alert('Error: ' + msg);
        }
    });
});
</script>
@endpush
