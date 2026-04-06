@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-75">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        @if($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-4 small">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                Please correct the errors in the form below.
                            </div>
                        @endif
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Start your AralDeck journey today.</p>
                    </div>
                    @php
                        // Check if there are any validation errors from a previous attempt
                        $hasErrors = $errors->any();
                    @endphp
                    <div id="initial-options" style="{{ $hasErrors ? 'display: none;' : 'display: block;' }}">
                        <a href="{{ route('google.login') }}" class="btn btn-outline-dark w-100 py-2 rounded-pill mb-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-google me-2"></i>
                            Continue with Google
                        </a>

                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1">
                            <span class="mx-3 text-muted small">OR</span>
                            <hr class="flex-grow-1">
                        </div>

                        <button type="button" onclick="showRegisterForm()" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                            Register with Email
                        </button>
                    </div>

                    <div id="registration-form" style="{{ $hasErrors ? 'display: block;' : 'display: none;' }}">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="John Doe">
                                @error('name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" 
                                    class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" required>
                                
                                <div id="email-feedback" class="small mt-1 px-3" style="min-height: 20px;"></div>

                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Confirm</label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold mb-3">
                                {{ __('Register') }}
                            </button>

                            <div class="text-center">
                                <button type="button" onclick="hideRegisterForm()" class="btn btn-link text-muted text-decoration-none small">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="text-center mt-4">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="{{ route('login') }}" class="text-primary small fw-bold text-decoration-none">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script> 
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const feedback = document.getElementById('email-feedback');
        const submitBtn = document.querySelector('button[type="submit"]');
        let timeout = null;

        emailInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            const email = this.value.trim();

            // Don't check if it's too short or doesn't look like an email yet
            if (email.length < 5 || !email.includes('@')) {
                feedback.innerHTML = '';
                emailInput.classList.remove('is-invalid', 'is-valid');
                return;
            }

            // Wait 500ms after typing stops before checking the DB
            timeout = setTimeout(() => {
                fetch("{{ route('email.check') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        feedback.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> ${data.message}</span>`;
                        emailInput.classList.add('is-invalid');
                        emailInput.classList.remove('is-valid');
                        submitBtn.disabled = true;
                    } else {
                        feedback.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Email is available.</span>`;
                        emailInput.classList.remove('is-invalid');
                        emailInput.classList.add('is-valid');
                        submitBtn.disabled = false;
                    }
                });
            }, 500);
        });
    }); 
    function showRegisterForm() {
        document.getElementById('initial-options').style.display = 'none';
        document.getElementById('registration-form').style.display = 'block';
    }

    function hideRegisterForm() {
        document.getElementById('initial-options').style.display = 'block';
        document.getElementById('registration-form').style.display = 'none';
    }
</script>

<style>
    .min-vh-75 { min-height: 75vh; }
    .form-control:focus { box-shadow: none; border-color: #0d6efd; }
</style>
@endsection