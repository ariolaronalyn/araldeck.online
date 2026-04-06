@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Success/Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">My Classrooms</h4>
        @if(auth()->user()->role === 'teacher')
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="bi bi-plus-lg"></i> Create New Class
            </button>
        @endif
    </div>

    <div class="row g-4">
        @forelse($classes as $class)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary border border-primary px-3 rounded-pill">{{ $class->school_year }}</span>
                            <span class="text-muted small">Section: {{ $class->section }}</span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $class->name }}</h5>
                        <p class="text-muted small mb-4">
                            @if(auth()->user()->role === 'teacher')
                                Managed by you
                            @else
                                Teacher: {{ $class->teacher->name ?? 'Unknown' }}
                            @endif
                        </p>
                        
                        <a href="{{ route('classroom.show', $class->id) }}" class="btn btn-outline-primary w-100 rounded-pill">
                            {{ auth()->user()->role === 'teacher' ? 'Manage Class' : 'Enter Classroom' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-journal-x display-1"></i>
                <p class="mt-3">No classes found.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal for Teachers Only --}}
@if(auth()->user()->role === 'teacher')
<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('classroom.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Create Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Class Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. BSIT 4-A" required>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">School Year</label>
                        <select name="school_year" class="form-select" required>
                            <option value="" disabled selected>-- Select --</option>
                            @php
                                $startYear = date('Y');
                                for($i = 0; $i < 5; $i++) {
                                    $yearRange = ($startYear + $i) . "-" . ($startYear + $i + 1);
                                    echo "<option value='{$yearRange}'>{$yearRange}</option>";
                                }
                            @endphp
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Section</label>
                        <input type="text" name="section" class="form-control" placeholder="A, B, C" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Create Class</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection