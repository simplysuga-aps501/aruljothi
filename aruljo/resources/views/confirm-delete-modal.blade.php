<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteLabel">
          <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Delete
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this record? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script>
    $(document).ready(function () {
        let formToSubmit = null;

        // 🗑️ Show confirmation modal on delete button click
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            formToSubmit = $(this).closest('form');
            $('#confirmDeleteModal').modal('show');
        });

        // ✅ Confirm delete
        $('#confirmDeleteBtn').on('click', function () {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            $('#confirmDeleteModal').modal('hide');
        });

        // 🕒 Auto-hide AdminLTE alerts after 3s
        setTimeout(() => {
            $('#successAlert, #errorAlert').alert('close');
        }, 3000);
    });

</script>
@endpush
