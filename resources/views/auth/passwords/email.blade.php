@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-75">
        <div class="col-md-5">
            {{-- BACK BUTTON --}}
            <div class="mb-4">
                <a href="{{ route('login') }}" class="text-muted text-decoration-none small fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Login
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-key-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold">Reset Password</h2>
                        <p class="text-muted small">Enter your email and we'll send you a link to get back into your account.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success border-0 shadow-sm rounded-3 small mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill px-3">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input id="email" type="email" class="form-control bg-light border-start-0 rounded-end-pill @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com">
                                <!-- @error('email')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>We can't find a user with that email address.</strong>
                                    </span>
                                @enderror -->
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="small text-muted">Still having trouble? <a href="mailto:support@araldeck.online" class="text-primary fw-bold text-decoration-none">Contact Support</a></p>
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
    .bg-gradient {
        background: linear-gradient(45deg, #0d6efd, #0dcaf0);
    }
</style>
@endsection