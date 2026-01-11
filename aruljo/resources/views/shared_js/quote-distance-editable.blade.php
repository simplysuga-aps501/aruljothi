<script>
    function initDistanceDurationEditable() {
            $('.editable_field').off('dblclick').on('dblclick', function() {
                const inputGroup = $(this).closest('.input-group');
                const modal = $(this).closest('.modal, body'); // works for modal or page
                const pincodeInput = modal.find('.pincode_input').first();
                const $alertDiv = inputGroup.siblings('.distance_alert');

                if (!$alertDiv.length) return;

                if (!pincodeInput.val().trim()) {
                    $alertDiv.text('Please enter a pincode first.').show();
                    setTimeout(() => $alertDiv.fadeOut(), 3000);
                    return;
                }

                $(this).prop('readonly', false).focus();
                $(this).css('background-color', '#ffffff'); // white while editing
            });

            $('.editable_field').off('blur').on('blur', function() {
                $(this).prop('readonly', true);
                $(this).css('background-color', '#d1ecf1'); // blue when readonly
            });
        }
</script>
