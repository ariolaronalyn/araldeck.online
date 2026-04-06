@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- 1. WELCOME HEADER --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="p-5 mb-4 bg-primary text-white rounded-4 shadow-sm position-relative overflow-hidden">
                <div class="position-relative z-index-1">
                    <h1 class="display-5 fw-bold">Kumusta, {{ Auth::user()->name }}! 👋</h1>
                    <p class="col-md-8 fs-5 opacity-75">Welcome back to AralDeck. What would you like to master today?</p>
                    <a href="{{ route('flashcards.index') }}" class="btn btn-light btn-lg px-4 rounded-pill fw-bold text-primary shadow-sm">
                        Go to My Decks
                    </a>
                </div>
                {{-- Decorative Icon Background --}}
                <i class="bi bi-rocket-takeoff position-absolute end-0 bottom-0 m-4 opacity-25" style="font-size: 10rem;"></i>
            </div>
        </div>
    </div>

    {{-- 2. QUICK STATS & ACTIONS --}}
    <div class="row g-4">
        {{-- Create New --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon bg-success bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-plus-circle fs-3"></i>
                    </div>
                    <h4 class="fw-bold">New Deck</h4>
                    <p class="text-muted small">Create a new study set manually or via bulk upload.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('flashcards.create_manual') }}" class="btn btn-outline-success btn-sm rounded-pill">Manual Entry</a>
                        <a href="{{ route('csv.form') }}" class="btn btn-outline-success btn-sm rounded-pill">Bulk Upload</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Browse Public --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon bg-info bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-search fs-3"></i>
                    </div>
                    <h4 class="fw-bold">Explore</h4>
                    <p class="text-muted small">Find public decks shared by other students and teachers.</p>
                    <a href="{{ route('flashcards.index', ['view' => 'public']) }}" class="btn btn-info text-white rounded-pill px-4 mt-2">Browse Gallery</a>
                </div>
            </div>
        </div>

        {{-- Subscription Status --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="feature-icon bg-warning bg-gradient text-white mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-star-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold">Your Plan</h4>
                    @php
                        $sub = DB::table('user_subscriptions')
                            ->where('user_id', Auth::id())
                            ->where('status', 'active')
                            ->orderBy('expires_at', 'desc')
                            ->first();
                    @endphp

                    @if($sub)
                        <p class="text-muted small mb-1">Active until:</p>
                        <span class="badge bg-light text-dark border mb-3">{{ \Carbon\Carbon::parse($sub->expires_at)->format('M d, Y') }}</span>
                    @else
                        <p class="text-muted small">You are currently on the Free Tier.</p>
                    @endif
                    <br>
                    <a href="{{ route('settings.index', ['tab' => 'subscription']) }}" class="btn btn-outline-warning text-dark btn-sm rounded-pill mt-auto">Manage Account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important;
    }
    .z-index-1 { z-index: 1; }
</style>
@endsection