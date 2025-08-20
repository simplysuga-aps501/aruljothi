@extends('adminlte::auth.auth-page', ['auth_type' => 'forgot'])

@section('auth_header', __('Forgot Your Password?'))

@section('auth_body')
    <p class="text-muted mb-3">
        Enter your email and we’ll send you a link to reset your password.
    </p>

    @if (session('status'))
        <x-adminlte-alert theme="success" dismissable>
            {{ session('status') }}
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email Address --}}
        <div class="input-group mb-3">
            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
@endsection

@section('auth_footer')
    <p class="my-0">
        <a href="{{ route('login') }}">Back to Login</a>
    </p>
@endsection
