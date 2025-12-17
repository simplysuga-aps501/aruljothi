<script>
function showAdminLTEAlert(message) {
    $('#adminLTEAlertModalBody').text(message);

    // Initialize and show modal
    const modalEl = document.getElementById('adminLTEAlertModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Remove previous click handler to avoid duplicates
    $('#adminLTEAlertModalOk').off('click');

    // Close modal on OK click
    $('#adminLTEAlertModalOk').on('click', function() {
        modal.hide();
    });

    $('#adminLTEAlertModalOk').focus();
}
</script>

