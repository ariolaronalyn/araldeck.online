@extends('layouts.app')

@section('content')
<div class="welcome-page">
    {{-- 1. HERO SECTION --}}
    <section class="hero-section py-5 mb-5 text-center bg-white">
        <div class="container py-5">
            <img src="{{ asset('images/araldeck_full_logo.png') }}" alt="AralDeck Logo" class="mb-4" style="height: 80px;">
            <h1 class="display-4 fw-bold text-dark">Master Your Studies with <span class="text-primary">AralDeck</span></h1>
            <p class="lead text-muted mb-4">The ultimate flashcard platform designed for efficient learning and collaborative success.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                @auth
                    <a href="{{ route('flashcards.index') }}" class="btn btn-primary btn-lg px-4 gap-3 rounded-pill">Go to My Decks</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 gap-3 rounded-pill">Try it now for FREE (1 Day)</a>
                    <a href="#pricing" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">View Plans</a>
                @endauth
            </div>
        </div>
    </section>

    {{-- 2. MISSION & VISION & WHO WE ARE --}}
    <section class="container mb-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-eye-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Our Vision</h3>
                    <p class="text-muted">To become the leading digital study companion in the Philippines, empowering students through innovative active recall technology.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="feature-icon bg-success bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-rocket-takeoff-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Our Mission</h3>
                    <p class="text-muted">To simplify complex learning by providing an accessible, collaborative, and gamified flashcard experience for every learner.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                    <div class="feature-icon bg-info bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Who We Are</h3>
                    <p class="text-muted">AralDeck is built by educators and tech enthusiasts dedicated to bridging the gap between traditional study methods and digital efficiency.</p>
                </div>
            </div>
        </div>
    </section>

    <hr class="container my-5 opacity-25">

    {{-- 3. PRICING SECTION --}}
    <section id="pricing" class="container mb-5 py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Choose Your Study Plan</h2>
            <p class="text-muted">Affordable pricing to help you achieve academic excellence.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @isset($plans)
                @foreach($plans as $plan)
                    <div class="col-lg-3">
                        <div class="card h-100 border-0 shadow-lg p-4 text-center position-relative overflow-hidden">
                            @if($plan->promo_price < $plan->original_price)
                                <div class="position-absolute top-0 end-0 bg-danger text-white px-3 py-1 small fw-bold" style="transform: rotate(45deg) translate(20px, -10px); width: 100px;">
                                    SALE
                                </div>
                            @endif
                            
                            <h4 class="fw-bold text-uppercase tracking-wider mb-3">{{ $plan->name }}</h4>
                            <div class="mb-4">
                                <span class="display-5 fw-bold text-primary">₱{{ number_format($plan->promo_price, 0) }}</span>
                                @if($plan->original_price > $plan->promo_price)
                                    <div class="text-muted small"><del>₱{{ number_format($plan->original_price, 0) }}</del></div>
                                @endif
                            </div>

                            <ul class="list-unstyled text-start mb-4 flex-grow-1">
                                <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> {{ $plan->collaborator_limit }} Collaborators</li>
                                <li class="mb-2">{!! $plan->details !!}</li>
                            </ul>

                            <a href="{{ route('register') }}" class="btn btn-outline-primary rounded-pill w-100 fw-bold py-2 mt-auto">Get Started</a>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted">Pricing information is currently unavailable.</p>
            @endisset
        </div>
    </section>

    {{-- 4. FOOTER CALL TO ACTION --}}
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container text-center">
            <h3 class="fw-bold mb-3">Ready to transform your grades?</h3>
            <p class="mb-4 opacity-75">Join thousands of students using AralDeck to study smarter, not harder.</p>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">Sign Up Now</a>
            <div class="mt-4 small opacity-50">
                &copy; {{ date('Y') }} AralDeck.online. All rights reserved.
            </div>
        </div>
    </footer>
</div>

<style>
    .hero-section {
        background-image: radial-gradient(circle at 10% 20%, rgba(231, 241, 255, 0.5) 0%, rgba(255, 255, 255, 1) 90%);
    }
    .tracking-wider { letter-spacing: 0.1em; }
    .btn-outline-primary:hover { color: #fff !important; }
    footer { border-top: 5px solid #0d6efd; }
</style>
@endsection