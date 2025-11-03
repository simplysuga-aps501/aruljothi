{{-- HSN Modal --}}
<div class="modal fade" id="addHSNCodeModal" tabindex="-1" aria-labelledby="addHSNCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add HSN Code</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                {{-- Add HSN Form --}}
                <form id="addHSNForm" class="mb-3">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Enter HSN Code" required>
                    <textarea name="description" class="form-control mb-2" placeholder="Enter Description (optional)"></textarea>
                    <button type="submit" class="btn btn-primary btn-block">Save HSN</button>
                </form>

                {{-- Existing HSN Codes --}}
                <h6>Available HSN Codes</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>HSN Code</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hsncodes as $hsn)
                            <tr>
                                <td>{{ $hsn->name }}</td>
                                <td>{{ $hsn->description }}</td>
                                <td>
                                    @if($hsn->products->count() == 0)
                                        <form method="POST" action="{{ route('hsncodes.destroy', $hsn->id) }}" class="d-inline delete-hsn-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="hsnAlert"></div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
       // Add HSN Code
       $('#addHSNForm').on('submit', function(e) {
           e.preventDefault();
           let name = $(this).find('input[name="name"]').val();
           let description = $(this).find('textarea[name="description"]').val();

           $.post('/hsncodes', {
               name,
               description,
               _token: '{{ csrf_token() }}'
           }, function(hsn) {
               $('#hsncode_id').append(`<option value="${hsn.id}">${hsn.name}</option>`);
               $('#hsncode_id').val(hsn.id);
               $('#addHSNCodeModal').modal('hide');
               $('#addHSNForm')[0].reset();

               $('#hsnAlert').html(`
              <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                  HSN Code added successfully!
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
          `);
               setTimeout(() => location.reload(), 1000);
           }).fail(function(xhr) {
               let msg = 'Failed to add HSN Code.';
               if (xhr.status === 422 && xhr.responseJSON?.errors?.name?.length) {
                   msg = xhr.responseJSON.errors.name[0]; // "This HSN code already exists."
               } else if (xhr.responseJSON?.message) {
                   msg = xhr.responseJSON.message;
               }
               $('#hsnAlert').html(`
                   <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                       ${msg}
                       <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                   </div>
               `);
           });
       });
       // Delete HSN Code
       $('#addHSNCodeModal').on('submit', '.delete-hsn-form', function(e) {
           e.preventDefault();
           if (!confirm('Are you sure you want to delete this HSN code?')) return;

           let form = $(this);

           $.ajax({
               url: form.attr('action'),
               method: 'DELETE', // use actual DELETE
               headers: {
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
               success: function() {
                   form.closest('tr').remove();
               },
               error: function(xhr) {
                   alert('Cannot delete: HSN code attached to products.');
                   console.error(xhr.responseText);
               }
           });
       });
    });
</script>
@endpush
