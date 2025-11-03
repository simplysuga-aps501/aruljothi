 {{-- Add Unit Modal --}}
    <div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Unit</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    {{-- Add Unit Form --}}
                    <form id="addUnitForm" class="mb-3">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Enter Unit Name" required>
                        <button type="submit" class="btn btn-primary btn-block">Save Unit</button>
                    </form>

                    {{-- Existing Units List --}}
                    <h6>Available Units</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Unit Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td>{{ $unit->name }}</td>
                                    <td>
                                        @if($unit->products->count() == 0)
                                            <form method="POST" action="{{ route('units.destroy', $unit->id) }}" class="d-inline delete-unit-form">
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

                    <div id="unitAlert"></div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
     $(document).ready(function() {
        //Add Unit
        $('#addUnitForm').on('submit', function(e) {
            e.preventDefault();
            let name = $(this).find('input[name="name"]').val();

            $.post('/units', {
                name,
                _token: '{{ csrf_token() }}'
            }, function(unit) {
                $('#unit_id').append(`<option value="${unit.id}">${unit.name}</option>`);
                $('#unit_id').val(unit.id);
                $('#addUnitModal').modal('hide');
                $('#addUnitForm')[0].reset();

                $('#unitAlert').html(`
               <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                   Unit added successfully!
                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
           `);
                setTimeout(() => location.reload(), 1000);
            }).fail(function(xhr) {
                let msg = 'Failed to add unit.';
                if (xhr.status === 422 && xhr.responseJSON?.errors?.name?.length) {
                    msg = xhr.responseJSON.errors.name[0]; // "This unit already exists."
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#unitAlert').html(`
                    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                        ${msg}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                `);
            });
        });
        //Delete Unit
        $('#addUnitModal').on('submit', '.delete-unit-form', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this unit?')) return;

            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function() {
                    form.closest('tr').remove();
                },
                error: function(xhr) {
                    alert('Cannot delete: unit attached to products.');
                    console.error(xhr.responseText);
                }
            });
        });
     });
</script>
@endpush
