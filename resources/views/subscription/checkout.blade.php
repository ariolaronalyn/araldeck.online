@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg p-4 text-center rounded-4">
                <h4 class="fw-bold mb-4">Complete Your Request</h4>
                
                <div class="bg-light p-4 rounded-4 mb-4">
                    <span class="text-muted small text-uppercase fw-bold">Plan selected:</span>
                    <h5 class="fw-bold text-primary mt-1">{{ $plan->name }}</h5>
                    <div class="display-5 fw-bold">₱{{ number_format($plan->promo_price, 2) }}</div>
                </div>

                <p class="small text-muted mb-4 px-3">
                    @if($plan->promo_price <= 0)
                        Click the button below to activate your free trial immediately.
                    @else
                        This is a simulation. Click the button below to confirm your "payment" and activate your plan.
                    @endif
                </p>

                <form action="{{ route('subscription.paymongo') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold">
                        @if($plan->promo_price <= 0)
                            <i class="bi bi-lightning-charge-fill me-2"></i> Activate Free Trial
                        @else
                            Confirm & Pay ₱{{ number_format($plan->promo_price, 2) }}
                        @endif
                    </button>
                </form>
                
                <a href="{{ is_null(auth()->user()->role) ? route('onboarding.index') : route('settings.index', ['tab' => 'subscription']) }}" 
                   class="btn btn-link text-muted mt-3 text-decoration-none small fw-bold">
                    Cancel and go back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection