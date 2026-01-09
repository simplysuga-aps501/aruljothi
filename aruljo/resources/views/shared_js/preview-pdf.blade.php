<script>
    // ============ PDF PREVIEW HANDLER ============
    $('#preview_pdf_btn').on('click', function() {
        // Ensure quote table exists
        if ($('#priceTable').length === 0) {
            $('.quote_alert').text('Please draft the quote before submitting.').show();
            $('html, body').animate({ scrollTop: $('.quote_alert').offset().top - 100 }, 400);
            return;
        }

        // Run validations
        if (!validateQuantities()) return;

        const payload = buildQuotePayload();
        if (!payload) {
            Swal.fire('Warning', 'Please draft the quote before previewing PDF.', 'warning');
            return;
        }

        // Build quoteData to match controller expectations
        const quoteData = {
            customer_name: $('#customer_name').val(),
            customer_contact: $('#customer_contact').val(),
            customer_address_line1: $('#customer_address_line1').val(),
            customer_address_line2: $('#customer_address_line2').val(),
            customer_district: $('#customer_district').val(),
            customer_state: $('#customer_state').val(),
            customer_pincode: $('#customer_pincode').val(),
            customer_gst_number: $('#customer_gst_number').val(),
            pdf_subject: $('#pdf_subject').val(),
            pdf_terms: $('#terms_preview_box').val(),
            pdf_delivery: $('#pdf_delivery').val(),
            pdf_date: $('#pdf_date').val(),
            quote_edit_data: JSON.stringify(payload) // ✅ include products/trucks
        };
        $.ajax({
            url: "{{ route('quotations.preview.pdf') }}",
            method: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                ...quoteData
            },
            xhrFields: { responseType: 'blob' },
            success: function(blob) {
                const pdfUrl = URL.createObjectURL(blob);
                window.open(pdfUrl, '_blank');
            },
            error: function() {
                Swal.fire('Error', 'Failed to generate preview PDF.', 'error');
            }
        });
    });

    function buildQuotePayload() {
        // Ensure quote table exists
        if ($('#priceTable').length === 0) return null;

        const trucks = collectTruckData();
        const prices = collectPriceData();
        const transport = collectTransportData();
        const totalAmount = parseFloat($('#net_total').text().replace(/[₹,]/g, '')) || 0;
        const subtotal = parseFloat($('#subtotal').text().replace(/[₹,]/g, '')) || 0;
        const gst_rate = 18;
        const gstAmount = subtotal * gst_rate / 100;
        const net_total = subtotal + gstAmount;
        const totalWeight = parseFloat($('#truck_total_weight').text().replace(/[^\d.]/g, '')) || 0;
        const cost_per_kg = totalWeight ? totalAmount / totalWeight : 0;
        const remarks = $('input[name="remarks"]').val() || '';
        const distance_km = parseFloat($('#quote_distance_km').val()) || 0;

        return {
            trucks,
            prices,
            transport,
            subtotal,
            gst_rate,
            total_amount: totalAmount,
            net_total,
            cost_per_kg,
            distance_km,
            remarks
        };
    }
</script>
