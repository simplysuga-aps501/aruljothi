<div id="additional-fields-container">
    <div class="additional-field row mb-2">
        <div class="col-md-5">
            <x-adminlte-input name="additional_fields[0][heading]" label="Heading" placeholder="e.g., Delivery Site" />
        </div>
        <div class="col-md-5">
            <x-adminlte-input name="additional_fields[0][content]" label="Content" placeholder="e.g., Suryanelli Site" />
        </div>
        <div class="col-md-2">
            <label class="d-block invisible">Remove</label>
            <button type="button" class="btn btn-danger remove-field">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    </div>
</div>

<button type="button" id="add-field" class="btn btn-success mt-2">
    <i class="fas fa-plus"></i> Add Field
</button>

@push('scripts')
<script>
$(document).ready(function() {
    $('#add-field').on('click', function() {
        const container = $('#additional-fields-container');

        // Start index based on existing fields
        const fieldIndex = container.find('.additional-field').length;
        const html = `
            <div class="additional-field row mb-2">
                <div class="col-md-5">
                    <x-adminlte-input name="additional_fields[${fieldIndex}][heading]" label="Heading" placeholder="Heading"/>
                </div>
                <div class="col-md-5">
                    <x-adminlte-input name="additional_fields[${fieldIndex}][content]" label="Content" placeholder="Content"/>
                </div>
                <div class="col-md-2">
                    <label class="d-block invisible">Remove</label>
                    <button type="button" class="btn btn-danger remove-field w-100">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
            </div>
        `;
        container.append(html);
    });

    $(document).on('click', '.remove-field', function() {
        $(this).closest('.additional-field').remove();
    });
});
</script>

@endpush
