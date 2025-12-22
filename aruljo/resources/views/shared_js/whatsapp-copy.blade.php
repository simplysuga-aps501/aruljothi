<script>
$(document).ready(function() {

    // ================== STATUS MESSAGES ==================
    const statusMessages = {
        'New Lead': (buyerName) => `Hello ${buyerName},\n\nThank you for contacting Aruljothi Pipeworks. We have received your request and will get back to you shortly.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
        'Lead Followup': (buyerName) => `Hello ${buyerName},\n\nJust following up regarding your request with Aruljothi Pipeworks. Please let us know if you need any assistance.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
        'Quotation': (buyerName, quoteSummary) => `Hello ${buyerName},\n\nThis is a gentle reminder from Aruljothi Pipeworks to review and approve the quotation at your convenience so we can proceed further.\n\n${quoteSummary || ''}\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
        'PO': (buyerName) => `Hello ${buyerName},\n\nThank you for your order. We have received your PO and will process it shortly at Aruljothi Pipeworks.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
        'Cancelled': (buyerName) => `Hello ${buyerName},\n\nWe are sorry to see you go. Please contact us again if you have future requirements.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
        'Completed': (buyerName) => `Hello ${buyerName},\n\nYour order with Aruljothi Pipeworks has been successfully completed. Thank you for choosing us.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`
    };

    // ================== QUOTE SUMMARY FUNCTIONS ==================
    function getSummaryText() {
        let summaryTable = $('#calcDetailsBody .summary-table').get(0);
        if (!summaryTable) {
            const tables = $('#calcDetailsBody').find('table');
            if (tables.length) summaryTable = tables.last().get(0);
        }
        if (!summaryTable) return null;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = summaryTable.outerHTML;

        const rows = tempDiv.querySelectorAll('tr');
        let items = [];

        // Build SKU→Name map from pills
        let skuNameMap = {};
        $('.product-pills .pill').each(function() {
            const sku = $(this).data('sku');
            const name = $(this).data('name');
            if (sku && name) skuNameMap[sku.trim()] = name.trim();
        });

        rows.forEach((row, idx) => {
            const cells = [...row.querySelectorAll('th,td')];
            if (idx > 0 && cells.length >= 5) {
                const sku = cells[0].innerText.trim();
                const productName = skuNameMap[sku] || sku;
                const qty = cells[1].querySelector('input')
                    ? parseFloat(cells[1].querySelector('input').value.replace(/,/g, '')) || 0
                    : parseFloat(cells[1].innerText.replace(/,/g, '')) || 0;
                const rate = cells[4].querySelector('input')
                    ? parseFloat(cells[4].querySelector('input').value.replace(/,/g, '')) || 0
                    : parseFloat(cells[4].innerText.replace(/,/g, '')) || 0;
                const total = cells[5].innerText.trim();
                items.push(`. ${productName} : ${qty} × ₹${rate} = ₹${total}`);
            }
        });

        const tableText = tempDiv.innerText;
        const subtotalMatch = tableText.match(/Subtotal.*?([\d,]+\.\d{2})/i);
        const gstMatch = tableText.match(/GST[^\d]*(?:\(\d+%?\))?.*?([\d,]+\.\d{2})/i);
        const totalMatch = tableText.match(/Net\s*Total.*?([\d,]+\.\d{2})/i);

        const subtotal = subtotalMatch ? `₹${subtotalMatch[1]}` : '';
        const gst = gstMatch ? `₹${gstMatch[1]}` : '';
        const total = totalMatch ? `₹${totalMatch[1]}` : '';

        let plainText = `Quotation Summary\n\n`;
        let whatsappText = `*Quotation Summary*%0A%0A`;

        plainText += items.join('\n') + '\n\n';
        whatsappText += items.map(i => i.replace(/\n/g, '%0A')).join('%0A') + '%0A%0A';

        plainText += `Subtotal: ${subtotal}\nGST (18%): ${gst}\nNet Total: ${total}\n\nThank you for your business!`;
        whatsappText += `*Subtotal:* ${subtotal}%0A*GST (18%):* ${gst}%0A*Net Total:* ${total}%0A%0A_Thank you for your business!_`;

        return { text: whatsappText, plainText };
    }

    // Helper to get correct context (modal or page)
    function getContext($btn) {
        const $modal = $btn.closest('.modal');
        return {
            buyerName: $modal.length ? $modal.find('input[name="buyer_name"]').val()?.trim() : $('input[name="buyer_name"]').val()?.trim(),
            buyerPhone: $modal.length ? $modal.find('input[name="buyer_contact"]').val()?.trim() : $('input[name="buyer_contact"]').val()?.trim(),
            status: $modal.length ? $modal.find('select[name="status"]').val() : $('select[name="status"]').val(),
            $alertDiv: $modal.length ? $modal.find('.quote_alert') : $('.quote_alert')
        };
    }

    // ================== COPY STATUS MESSAGE TO CLIPBOARD ==================
    $('#copy_whatsapp_text').on('click', function() {
        const { buyerName, buyerPhone, status, $alertDiv } = getContext($(this));

        if (!buyerName) {
            alert('Please enter buyer name.');
            return;
        }

        let message = '';
        const isQuote = (status === 'Quotation' || status === 'Quote');

        if (isQuote) {
            const summaryObj = getSummaryText();
            if (!summaryObj || !summaryObj.plainText.trim()) {
                $alertDiv.text('❌ No quotation summary found to copy!')
                    .removeClass('text-success').addClass('text-danger')
                    .stop(true, true).show();
                setTimeout(() => $alertDiv.fadeOut(), 2500);
                return;
            }
            message = statusMessages['Quotation'](buyerName, summaryObj.plainText);
        } else if (status === 'Cancelled') {
            message = statusMessages['Cancelled'](buyerName);
        } else {
            message = statusMessages[status]?.(buyerName) || `Hello ${buyerName},\n\nStatus: ${status}`;
        }

        navigator.clipboard.writeText(message)
            .then(() => {
                $alertDiv.text('✅ Message copied to clipboard!')
                    .removeClass('text-danger').addClass('text-success')
                    .stop(true, true).show();
                setTimeout(() => $alertDiv.fadeOut(), 2000);
            });
    });

    // ================== SEND STATUS WHATSAPP ==================
    $(document).on('click', '#sendStatusWhatsappBtn, #sendCancelWhatsappBtn', function() {
        const { buyerName, buyerPhone, status, $alertDiv } = getContext($(this));

        if (!buyerPhone || buyerPhone.replace(/\D/g, '').length < 10) {
            alert('Please enter a valid buyer contact number.');
            return;
        }

        let message = '';
        const isQuote = (status === 'Quotation' || status === 'Quote');

        if (isQuote) {
            const summaryObj = getSummaryText();
            if (!summaryObj || !summaryObj.plainText.trim()) {
                $alertDiv.text('❌ No quotation available to send!')
                    .removeClass('text-success').addClass('text-danger')
                    .stop(true, true).show();
                setTimeout(() => $alertDiv.fadeOut(), 2500);
                return;
            }
            message = statusMessages['Quotation'](buyerName, summaryObj.plainText);
        } else if (status === 'Cancelled') {
            message = statusMessages['Cancelled'](buyerName);
        } else {
            message = statusMessages[status]?.(buyerName) || `Hello ${buyerName},\n\nStatus: ${status}`;
        }

        const cleanPhone = buyerPhone.replace(/\D/g, '');
        const url = `https://wa.me/91${cleanPhone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');

        $alertDiv.text('✅ WhatsApp message opened!')
            .removeClass('text-danger').addClass('text-success')
            .stop(true, true).show();
        setTimeout(() => $alertDiv.fadeOut(), 2000);
    });

    // ================== For Quote Module ==================
    // ================== COPY QUOTATION MESSAGE ==================
    $(document).on('click', '#copy_quote_whatsapp', function() {
        const buyerName = $('input[name="buyer_name"]').val()?.trim() ||
                          $('#buyer_name_text').text()?.trim() || 'Customer';
        const $alertDiv = $('.quote_alert');

        const summaryObj = getSummaryText();
        if (!summaryObj || !summaryObj.plainText.trim()) {
            $alertDiv.text('❌ No quotation summary found to copy!')
                .removeClass('text-success').addClass('text-danger')
                .stop(true, true).show();
            setTimeout(() => $alertDiv.fadeOut(), 2500);
            return;
        }

        const message = statusMessages['Quotation'](buyerName, summaryObj.plainText);

        navigator.clipboard.writeText(message)
            .then(() => {
                $alertDiv.text('✅ Quotation message copied to clipboard!')
                    .removeClass('text-danger').addClass('text-success')
                    .stop(true, true).show();
                setTimeout(() => $alertDiv.fadeOut(), 2000);
            });
    });

    // ================== SEND QUOTATION MESSAGE VIA WHATSAPP ==================
    $(document).on('click', '#send_quote_whatsapp', function() {
        const buyerName = $('input[name="buyer_name"]').val()?.trim() ||
                          $('#buyer_name_text').text()?.trim() || 'Customer';
        const buyerPhone = $('input[name="buyer_contact"]').val()?.trim() ||
                           $('#buyer_contact_text').text()?.trim();

        const $alertDiv = $('.quote_alert');

        const summaryObj = getSummaryText();
        if (!summaryObj || !summaryObj.plainText.trim()) {
            $alertDiv.text('❌ No quotation available to send!')
                .removeClass('text-success').addClass('text-danger')
                .stop(true, true).show();
            setTimeout(() => $alertDiv.fadeOut(), 2500);
            return;
        }

        const message = statusMessages['Quotation'](buyerName, summaryObj.plainText);
        const cleanPhone = buyerPhone.replace(/\D/g, '');
        const url = `https://wa.me/91${cleanPhone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');

        $alertDiv.text('✅ WhatsApp quotation message opened!')
            .removeClass('text-danger').addClass('text-success')
            .stop(true, true).show();
        setTimeout(() => $alertDiv.fadeOut(), 2000);
    });

});
</script>
