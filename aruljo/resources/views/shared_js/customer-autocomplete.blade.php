<script>
/* ---------------- CUSTOMER AUTOCOMPLETE & PREFILL ---------------- */
function initCustomerAutocomplete(customersList) {
    const nameInput = $('#customer_name');
    if (!nameInput.length || !Array.isArray(customersList)) return;

    const pocInput = $('#customer_poc_name');
    const pocNumberInput = $('#customer_poc_number');
    const phoneInput = $('#customer_contact');
    const alternatePhoneInput = $('#alternate_phone');
    const emailInput = $('#customer_email');
    const addressInput = $('#customer_address');
    const pincodeInput = $('#customer_pincode');
    const gstInput = $('#customer_gst');
    const hiddenIdInput = $('#customer_id');

    nameInput.autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();
            const matches = customersList
                .filter(c => c.name.toLowerCase().includes(term))
                .map(c => c.name);
            response(matches);
        },
        minLength: 1,
        appendTo: "#editQuotationModal", // ensures dropdown stays within modal
        select: function (event, ui) {
            const selected = customersList.find(c => c.name === ui.item.value);
            if (!selected) return;

            // Fill all fields
            nameInput.val(selected.name);
            pocInput.val(selected.poc_name || '');
            pocNumberInput.val(selected.poc_number || '');
            phoneInput.val(selected.phone || '');
            alternatePhoneInput.val(selected.alternate_phone || '');
            emailInput.val(selected.email || '');
            addressInput.val(selected.address || '');
            pincodeInput.val(selected.pincode || '');
            gstInput.val(selected.gst_number || '');

            hiddenIdInput.val(selected.id);
        },
        change: function (event, ui) {
            // If name typed manually but not selected from list — clear ID
            if (!ui.item) {
                hiddenIdInput.val('');
            }
        }
    });
}
</script>
