<div class="modal fade" id="chooseRateUpdateModal" tabindex="-1" role="dialog" aria-labelledby="chooseRateUpdateLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="chooseRateUpdateLabel">Missing Fixed Rate</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <!-- Show truck and location for reference -->
        <p class="mb-1" id="modalTruckName"></p>
        <p class="mb-1" id="modalLocationName"></p>

        <div class="form-group">
          <label>Enter Rate</label>
          <input type="number" id="miniRateValue" class="form-control form-control-sm text-center" placeholder="Enter new rate">
        </div>

        <div class="mt-3">
          <button class="btn btn-primary btn-block" id="confirmPincodeRate">
            <span class="btn-text">Update Rate</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script>
let currentTruckContext = {};
let currentRateCell = null;

// 🟡 Open modal on “Update” click
$(document).on('click', '.open-rate-choice-modal', function (e) {
    e.preventDefault();

    const truckId = $(this).data('truck-id');
    const truckName = $(this).data('truck-name');
    const delivery_location_id = $('#quote_delivery_location_id, .delivery_location_id').val();
    const locationName = $('#quote_delivery_location').val() || 'Current Location';

    currentTruckContext = {
        truckId,
        locationId: delivery_location_id
    };

    currentRateCell = $(this).closest('td');

    // Prefill existing rate
    const existingRate = parseFloat(currentRateCell.find('input.fixed-rate').val()) || '';
    $('#miniRateValue').val(existingRate);

    // Set labels
    $('#modalTruckName').text(`Truck: ${truckName}`);
    $('#modalLocationName').text(`Location: ${locationName}`);

    // Show modal
    $('#chooseRateUpdateModal').modal('show');
});


// 🟢 Confirm & update rate (DB + screen)
$('#confirmPincodeRate').on('click', function () {
    const rate = parseFloat($('#miniRateValue').val());
    if (isNaN(rate) || rate <= 0) return alert('Please enter a valid rate');

    const $button = $('#confirmPincodeRate');
    const $spinner = $button.find('.spinner-border');
    const $text = $button.find('.btn-text');
    const { truckId, locationId } = currentTruckContext;

    // Show loading
    $spinner.removeClass('d-none');
    $text.text('Updating...');
    $button.prop('disabled', true);

    // 🔵 1️⃣ Update in DB via AJAX
    $.ajax({
        url: "{{ route('rates.store',[],false) }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            truck_type_id: truckId,
            rate: rate,
            location_id: [locationId]
        },
        success: function () {
            // ✅ 2️⃣ Update all rows for same truck type in screen
            $('#transportTable tr').each(function () {
                const $row = $(this);
                const link = $row.find('.open-rate-choice-modal');
                const rowTruckId = link.data('truck-id');

                if (rowTruckId === truckId) {
                    $row.find('input.fixed-rate').val(rate.toFixed(2));
                    $row.find('.rate-message').html(`<div class="text-success small mt-1">✔ Rate updated</div>`);

                    const unloading = parseFloat($row.find('.unloading-cost').val()) || 0;
                    const total = rate + unloading;
                    $row.find('.transport-cost').text('₹' + total.toFixed(2));
                }
            });

            // ✅ 3️⃣ Update in-memory cache
            window.lastDistrictRates = window.lastDistrictRates || [];
            const match = window.lastDistrictRates.find(r =>
                r.truck_type_id == truckId && r.location_id == locationId
            );
            if (match) {
                match.rate = rate;
            } else {
                window.lastDistrictRates.push({
                    truck_type_id: truckId,
                    location_id: locationId,
                    rate: rate
                });
            }

            // ✅ 4️⃣ Close modal & refresh totals
            $('#chooseRateUpdateModal').modal('hide');
            updateTransportTotal();
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            currentRateCell.find('.rate-message').html(
                `<div class="text-danger small mt-1">✖ Failed to update rate</div>`
            );
        },
        complete: function () {
            $spinner.addClass('d-none');
            $text.text('Update Rate');
            $button.prop('disabled', false);
        }
    });
});
</script>

@endpush
