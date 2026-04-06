@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Complete Your Profile</h2>
                <p class="text-muted">Tell us who you are and choose how you want to start.</p>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('onboarding.store') }}" method="POST">
                @csrf
                
                {{-- STEP 1: ROLE SELECTION --}}
                <h5 class="fw-bold mb-3"><i class="bi bi-1-circle-fill text-primary me-2"></i> I am a...</h5>
                <div class="row g-3 mb-5">
                    {{-- STUDENT COLUMN --}}
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="role" id="role_student" value="student" {{ old('role') == 'student' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary w-100 py-4 rounded-4 shadow-sm @error('role') border-danger @enderror" for="role_student">
                            <i class="bi bi-mortarboard display-4 d-block mb-2"></i>
                            <span class="fw-bold h5">Student</span>
                        </label>
                    </div>

                    {{-- TEACHER COLUMN (Add this!) --}}
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="role" id="role_teacher" value="teacher" {{ old('role') == 'teacher' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary w-100 py-4 rounded-4 shadow-sm @error('role') border-danger @enderror" for="role_teacher">
                            <i class="bi bi-person-workspace display-4 d-block mb-2"></i>
                            <span class="fw-bold h5">Teacher</span>
                        </label>
                    </div>
                </div>

                {{-- STEP 2: PLAN SELECTION --}}
                <h5 class="fw-bold mb-3"><i class="bi bi-2-circle-fill text-primary me-2"></i> Choose your plan</h5>
                <div class="row g-3">
                    {{-- 1. KEEP THE HARDCODED TRIAL (As requested) --}}
                    <div class="col-12">
                        <div class="card border-primary border-2 shadow-sm mb-3 rounded-4">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="plan_id" id="plan_trial" value="trial" checked>
                                    <label class="form-check-label fw-bold h5 mb-0 ms-2" for="plan_trial">
                                        1-Day Free Trial
                                    </label>
                                </div>
                                <span class="badge bg-primary rounded-pill px-3 py-2">FREE</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. LOOP THROUGH THE REMAINING 3 PLANS --}}
                    @foreach($plans as $plan)
                        {{-- SKIP the trial plan if it comes from the database to avoid duplicates --}}
                        @if($plan->promo_price <= 0 || $plan->name == '1-Day Free Trial')
                            @continue
                        @endif

                        <div class="col-md-4"> {{-- Changed to col-md-4 to fit all 3 in one row if screen allows --}}
                            <div class="card h-100 border-0 shadow-sm border-hover rounded-4 transition-hover">
                                <div class="card-body p-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="plan_id" id="plan_{{ $plan->id }}" value="{{ $plan->id }}">
                                        <label class="form-check-label fw-bold h6 mb-0 ms-1" for="plan_{{ $plan->id }}">
                                            {{ $plan->name }}
                                        </label>
                                    </div>
                                    <div class="mt-2 text-primary fw-bold fs-4">₱{{ number_format($plan->promo_price, 0) }}</div>
                                    <ul class="list-unstyled small text-muted mt-2 mb-0">
                                        <li><i class="bi bi-check2-circle text-success me-1"></i> {{ $plan->collaborator_limit }} Collabs</li>
                                        {{-- Optional: Add duration --}}
                                        <li><i class="bi bi-calendar3 text-primary me-1"></i> {{ $plan->duration_days }} Days</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow fw-bold">
                        Finish Setup & Start Studying
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Add this for a smoother feel */
    .transition-hover {
        transition: all 0.3s ease;
    }
    .border-hover:hover {
        border: 1px solid #0d6efd !important;
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
    }
    /* When the radio is checked, make the label pop */
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd !important;
        color: white !important;
        border-color: #0a58ca !important;
        transform: scale(1.02);
        transition: 0.2s;
    }

    /* Error state styling */
    .border-danger {
        border-width: 2px !important;
        background-color: #fff8f8 !important;
    }

    /* Helpful hover effect */
    .rounded-4:hover {
        background-color: #f8fbff;
        cursor: pointer;
    }
</style>
@endsection 