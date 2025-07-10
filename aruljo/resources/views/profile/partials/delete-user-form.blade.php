<form method="POST" action="{{ route('profile.destroy') }}" class="mt-4">
    @csrf
    @method('delete')

    <div class="alert alert-danger">
        <strong>Warning!</strong> Once your account is deleted, all of its resources and data will be permanently deleted.
    </div>

    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
        <i class="fas fa-trash-alt me-1"></i> Delete Account
    </button>
</form>
