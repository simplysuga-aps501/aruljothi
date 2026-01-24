<div class="border rounded p-3 bg-white mb-3">
    <h5 class="text-primary mb-2"><i class="fas fa-file-contract"></i> Terms & Conditions</h5>

    {{-- ===== Checkbox Options ===== --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="font-weight-bold text-dark d-block mb-2">Pricing & Inclusions (choose one set)</label>

            <div class="form-check">
                <input class="form-check-input term-option" type="checkbox" value="The above price includes loading only. Transportation and unloading are under the client’s scope." id="terms_set_a">
                <label class="form-check-label" for="terms_set_a">
                    Set A: The above price includes loading only; transportation and unloading are under client’s scope.
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input term-option" type="checkbox" value="The above price includes loading and transportation. Unloading will fall under the client’s scope." id="terms_set_b">
                <label class="form-check-label" for="terms_set_b">
                    Set B: The above price includes loading and transportation; unloading is under client’s scope.
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input term-option" type="checkbox" value="The above price includes loading, transportation and unloading." id="terms_set_b">
                <label class="form-check-label" for="terms_set_b">
                    Set C: The above price includes loading, transportation and unloading.
                </label>
            </div>
        </div>

        <div class="col-md-6">
            <label class="font-weight-bold text-dark d-block mb-2">Additional Terms</label>

            <div class="form-check">
                <input class="form-check-input term-option" type="checkbox" value="For materials of 2.5 meters or longer, unloading will always be under the client’s scope." id="term_25m">
                <label class="form-check-label" for="term_25m">Unloading for 2.5m+ materials under client’s scope</label>
            </div>

            <div class="form-check">
                <input class="form-check-input term-option" type="checkbox" value="For 2-meter pipes, two scaffolding sticks or steel channels must be arranged by the client for unloading." id="term_2m_pipe">
                <label class="form-check-label" for="term_2m_pipe">2m pipe unloading support by client</label>
            </div>
        </div>
    </div>

    {{-- ===== Editable Preview Box ===== --}}
    <label class="font-weight-bold text-dark">Preview / Edit Text for PDF</label>
    <textarea id="terms_preview_box" class="form-control" rows="5" name="pdf_terms"></textarea>

    <small class="text-muted d-block mt-1">✓ You can edit the text above before generating the PDF.</small>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 🟢 Always-included default terms
    const defaultTerms = [
        "Goods once sold will not be accepted back under any circumstances.",
        "Payment shall be made in two installments: 50% advance and 50% upon delivery.",
        "All payments must be made within the agreed timelines."
    ];

    // 🟡 Prefill from saved version (Laravel injects existing terms here)
    const savedTermsRaw = @json($version->pdf_terms ?? '');
    const savedTerms = savedTermsRaw
        ? savedTermsRaw.replace(/^\d+\.\s*/gm, '').split(/\r?\n/).map(t => t.trim()).filter(t => t !== '')
        : [];

    // 🟠 Function to update textarea with numbering
    function updateTermsBox() {
        let allTerms = [...defaultTerms];

        $('.term-option:checked').each(function() {
            allTerms.push($(this).val());
        });

        const numberedText = allTerms.map((t, i) => `${i + 1}. ${t}`).join('\n');
        $('#terms_preview_box').val(numberedText);
    }

    // 🟢 Initialize checkboxes based on saved terms
    $('.term-option').each(function() {
        const termValue = $(this).val().trim();

        if (savedTerms.some(t => t.includes(termValue))) {
            $(this).prop('checked', true);
        }
    });

    // 🟣 Initialize textarea (with defaults + checked)
    updateTermsBox();

    // 🔵 Update on checkbox change
    $('.term-option').on('change', updateTermsBox);
});
</script>
@endpush



