<script>
$(document).ready(function() {
    // ----------------------------
    // Manual PDF download button
    // ----------------------------
    $(document).on('click', '.download-pdf', function() {
        const quotationId = $(this).data('id');
        if (!quotationId) return;

        // AJAX fetch + blob download (stays on same page)
        fetch(`/quotations/${quotationId}/download`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(response => {
            const disposition = response.headers.get('Content-Disposition');
            let filename = `Quotation_${quotationId}.pdf`;

            if (disposition && disposition.includes('filename=')) {
                const matches = disposition.match(/filename="?([^"]+)"?/);
                if (matches && matches[1]) {
                    filename = matches[1];
                }
            }

            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(err => console.error('PDF download failed:', err));
    });

    // ----------------------------
    // Auto-download after create / create-version
    // ----------------------------
    @if (session('download_pdf'))
        const pdfUrl = "{{ session('download_pdf') }}";
        if (pdfUrl) {
            fetch(pdfUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => {
                    const disposition = response.headers.get('Content-Disposition');
                    let filename = 'Quotation.pdf';

                    if (disposition && disposition.includes('filename=')) {
                        const matches = disposition.match(/filename="?([^"]+)"?/);
                        if (matches && matches[1]) {
                            filename = matches[1];
                        }
                    }

                    return response.blob().then(blob => ({ blob, filename }));
                })
                .then(({ blob, filename }) => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(err => console.error('PDF auto-download failed:', err));
        }
    @endif
});
</script>
