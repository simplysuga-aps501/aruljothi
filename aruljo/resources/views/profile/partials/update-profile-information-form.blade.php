@if (session('status') === 'profile-information-updated')
    <div class="alert alert-success">
        Profile updated successfully.
    </div>
@endif

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name">Name</label>
        <input id="name" name="name" type="text"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', auth()->user()->name) }}" readonly autofocus>

        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mt-3">
        <label for="email">Email</label>
        <input id="email" name="email" type="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', auth()->user()->email) }}" readonly>

        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            Save
        </button>
    </div>
</form>
