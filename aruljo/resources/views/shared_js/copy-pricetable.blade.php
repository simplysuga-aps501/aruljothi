<script>
    $(document).ready(function() {

        // ================== STATUS MESSAGES ==================
        const statusMessages = {
            'New Lead': (buyerName) => `Hello ${buyerName},\n\nThank you for contacting Aruljothi Pipeworks. We have received your request and will get back to you shortly.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
            'Follow-up': (buyerName) => `Hello ${buyerName},\n\nJust following up regarding your request with Aruljothi Pipeworks. Please let us know if you need any assistance.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
            'Quote': (buyerName, quoteSummary) => `Hello ${buyerName},\n\nThis is a gentle reminder from Aruljothi Pipeworks to review and approve the quotation at your convenience so we can proceed further.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
            'PO': (buyerName) => `Hello ${buyerName},\n\nThank you for your order. We have received your PO and will process it shortly at Aruljothi Pipeworks.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
            'Cancelled': (buyerName, reason = '') => `Hello ${buyerName},\n\nThank you for considering Aruljothi Pipeworks. We understand you won’t be proceeding at this time${reason ? ' due to ' + reason : ''}. For any future requirements, please feel free to reach out to us.\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`,
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
            $('.product-pills .pill').each(function () {
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

            plainText += `Subtotal: ${subtotal}\n`;
            plainText += `GST (18%): ${gst}\n`;
            plainText += `Net Total: ${total}\n\n`;
            plainText += `Thank you for your business!`;

            whatsappText += `*Subtotal:* ${subtotal}%0A`;
            whatsappText += `*GST (18%):* ${gst}%0A`;
            whatsappText += `*Net Total:* ${total}%0A%0A`;
            whatsappText += `_Thank you for your business!_`;

            return { text: whatsappText, plainText };
        }

        // ================== COPY QUOTE TO CLIPBOARD ==================
        $('#copy_whatsapp_text').on('click', function() {
            const summary = getSummaryText();
            const $alertDiv = $('.quote_alert');

            if (!summary) {
                $alertDiv.text('Please generate a quote first (Draft Quote).')
                    .removeClass('text-success').addClass('text-danger')
                    .stop(true, true).show();
                setTimeout(() => $alertDiv.fadeOut(), 3000);
                return;
            }

            navigator.clipboard.writeText(summary.plainText)
                .then(() => {
                    $alertDiv.text('Quotation summary copied to clipboard!')
                        .removeClass('text-danger').addClass('text-success')
                        .stop(true, true).show();
                    setTimeout(() => $alertDiv.fadeOut(), 2000);
                })
                .catch(() => {
                    const temp = document.createElement('textarea');
                    temp.value = summary.plainText;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);

                    $alertDiv.text('Copied using fallback method.')
                        .removeClass('text-danger').addClass('text-success')
                        .stop(true, true).show();
                    setTimeout(() => $alertDiv.fadeOut(), 2000);
                });
        });

        // ================== CANCEL STATUS ALERT ==================
        $(document).on('change', 'select[name="status"]', function() {
            const status = $(this).val();
            const $modal = $(this).closest('.modal');
            const $alertDiv = $modal.find('.quote_alert');

            if(status === 'Cancelled') {
                $alertDiv.html(`
                    Lead marked as <strong>Cancelled</strong>.
                    <button type="button" class="btn btn-success btn-sm" id="sendCancelWhatsappBtn">
                        <i class="fab fa-whatsapp"></i> Send WhatsApp
                    </button>
                `)
                .removeClass('text-danger text-success')
                .addClass('text-warning')
                .show();
            } else {
                $alertDiv.hide().html('');
            }
        });

        // ================== SEND STATUS WHATSAPP ==================
        $(document).on('click', '#sendStatusWhatsappBtn, #sendCancelWhatsappBtn', function() {
            const $modal = $(this).closest('.modal');
            const buyerName = $modal.find('input[name="buyer_name"]').val()?.trim();
            const buyerPhone = $modal.find('input[name="buyer_contact"]').val()?.trim();
            const status = $modal.find('select[name="status"]').val();

            if (!buyerPhone || buyerPhone.replace(/\D/g,'').length < 10) {
                alert('Please enter a valid buyer contact number.');
                return;
            }

            let message = '';
            if (status === 'Quote') {
                const summaryObj = getSummaryText();
                message = statusMessages[status](buyerName, summaryObj?.plainText);
            } else if (status === 'Cancelled') {
                const reason = prompt('Enter cancellation reason (optional):', '');
                message = statusMessages[status](buyerName, reason);
            } else {
                message = statusMessages[status]?.(buyerName) || `Hello ${buyerName},\n\nStatus: ${status}\n\n📞 Kavin: 7373738363\n📞 Office: 6381603739\n📞 Office: 7373233233\n🌐 https://aruljothipipes.in/`;
            }

            const cleanPhone = buyerPhone.replace(/\D/g,'');
            const url = `https://wa.me/91${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
        });

    });

</script>
