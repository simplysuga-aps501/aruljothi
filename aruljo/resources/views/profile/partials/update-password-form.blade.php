@if (session('status') === 'password-updated')
    <div class="alert alert-success">
        Password updated successfully.
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="current_password">Current Password</label>
        <input id="current_password" name="current_password" type="password"
            class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">

        @error('current_password')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mt-3">
        <label for="password">New Password</label>
        <input id="password" name="password" type="password"
            class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">

        @error('password')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mt-3">
        <label for="password_confirmation">Confirm New Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password"
            class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">

        @error('password_confirmation')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            Update Password
        </button>
    </div>
</form>
