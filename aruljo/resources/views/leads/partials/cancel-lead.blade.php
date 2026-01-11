<!-- Cancel Lead Modal -->
<div class="modal fade" id="cancelLeadModal" tabindex="-1" aria-labelledby="cancelLeadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Cancel Lead</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Do you want to notify the user about this cancellation?</p>
        <textarea id="cancelMessage" class="form-control" rows="3">
Dear {{ '{{buyer_name}}' }}, your inquiry has been marked as cancelled. Please contact us if you wish to reopen it.
        </textarea>
        <div class="mt-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" id="copyCancelText">
            <i class="fas fa-copy"></i> Copy Text
          </button>
          <a href="#" target="_blank" class="btn btn-success" id="sendCancelWhatsapp">
            <i class="fab fa-whatsapp"></i> Send WhatsApp
          </a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
