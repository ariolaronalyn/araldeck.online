@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-75">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Login to continue your AralDeck journey.</p>
                    </div>

                    {{-- SOCIAL LOGIN --}}
                    <div class="mb-4">
                        <a href="{{ route('google.login') }}" class="btn btn-outline-dark w-100 py-2 rounded-pill d-flex align-items-center justify-content-center fw-bold">
                            <i class="bi bi-google me-2"></i>
                            Login with Google
                        </a>
                    </div>

                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1">
                        <span class="mx-3 text-muted small">OR EMAIL</span>
                        <hr class="flex-grow-1">
                    </div>

                    {{-- LOGIN FORM --}}
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill px-3">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input id="email" type="email" class="form-control bg-light border-start-0 rounded-end-pill @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="password" class="form-label small fw-bold">Password</label>
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none small fw-bold" href="{{ route('password.request') }}">
                                        Forgot?
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill px-3">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input id="password" type="password" class="form-control bg-light border-start-0 rounded-end-pill @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Keep me logged in
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
                            {{ __('Login') }}
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2">
                        <span class="text-muted small">New to AralDeck?</span>
                        <a href="{{ route('register') }}" class="text-primary small fw-bold text-decoration-none ms-1">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .min-vh-75 { min-height: 75vh; }
    .input-group-text { border-color: #dee2e6; }
    .form-control { border-color: #dee2e6; padding: 0.6rem 1rem; }
    .form-control:focus {
        box-shadow: none;
        border-color: #0d6efd;
        background-color: #fff !important;
    }
    .btn-primary:hover { transform: translateY(-1px); transition: 0.2s; }
</style>
@endsection