<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Compact helper (fixed) -->
<script>
const SwalCompact = {
    alert: (title, text, icon = 'warning') => {
        return Swal.fire({
            title,
            html: text, // ✅ use html for <br> or formatting
            icon,
            width: '350px',
            padding: '0.5rem',
            confirmButtonColor: '#007bff',
            didOpen: () => {
                const titleEl = Swal.getTitle();
                const contentEl = Swal.getHtmlContainer(); // ✅ new method
                if (titleEl) titleEl.style.fontSize = '1rem';
                if (contentEl) contentEl.style.fontSize = '0.85rem';
            }
        });
    },

    confirm: (title, text, icon = 'warning', confirmText = 'Yes', cancelText = 'Cancel') => {
        return Swal.fire({
            title,
            html: text, // ✅ use html for better control
            icon,
            width: '350px',
            padding: '0.5rem',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            didOpen: () => {
                const titleEl = Swal.getTitle();
                const contentEl = Swal.getHtmlContainer(); // ✅ replaced getContent()
                const confirmBtn = Swal.getConfirmButton();
                const cancelBtn = Swal.getCancelButton();

                if (titleEl) titleEl.style.fontSize = '1rem';
                if (contentEl) contentEl.style.fontSize = '0.85rem';
                if (confirmBtn) confirmBtn.style.fontSize = '0.85rem';
                if (cancelBtn) cancelBtn.style.fontSize = '0.85rem';
            }
        });
    }
};
</script>
