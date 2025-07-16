@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('auth_header', 'Register a New Account')

@section('auth_body')
    <form action="{{ route('register') }}" method="POST">
        @csrf

        {{-- Name --}}
        <div class="input-group mb-3">
            <input name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="Full name" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="input-group mb-3">
            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="input-group mb-3">
            <input name="password" type="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="input-group mb-3">
            <input name="password_confirmation" type="password" class="form-control"
                   placeholder="Retype password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <a href="{{ route('login') }}">Already have an account?</a>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-success btn-block">Register</button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    <p class="mb-0 mt-3">
        <a href="{{ route('login') }}" class="text-center">Back to login</a>
    </p>
@endsection
