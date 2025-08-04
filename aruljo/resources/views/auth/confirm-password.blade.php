@extends('adminlte::auth.auth-page', ['auth_type' => 'password'])

@section('auth_header', 'Confirm Your Password')

@section('auth_body')
    <p class="mb-3 text-muted">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        {{-- Password --}}
        <div class="input-group mb-3">
            <input name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password" required autocomplete="current-password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Confirm Password') }}
                </button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    <p class="mb-0">
        <a href="{{ route('password.request') }}">
            {{ __('Forgot your password?') }}
        </a>
    </p>
@endsection
