@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Question Bank</h4>
            <p class="text-muted small">Manage and clone questions for your exams</p>
        </div>
        <a href="{{ route('questions.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg"></i> Create New Questions
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('questions.index') }}" method="GET" class="row g-3">
                {{-- Search Input --}}
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                            placeholder="Search questions..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Course Filter --}}
                <div class="col-md-3">
                    <select name="course_id" class="form-select search-select">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Filter --}}
                <div class="col-md-3">
                    <select name="subject_id" class="form-select search-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Button --}}
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary px-4">Filter</button>
                        <a href="{{ route('questions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($questions as $q)
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-soft-primary text-primary border border-primary px-2 py-1 small me-2">
                                    {{ $q->course->title ?? 'N/A' }}
                                </span>
                                <span class="badge bg-light text-dark border small">
                                    {{ $q->subject->title ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $q->is_public ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} border small">
                                    {{ $q->is_public ? 'Public' : 'Private' }}
                                </span>
                                <div class="text-muted extra-small mt-1">{{ $q->default_points }} Points</div>
                            </div>
                        </div>

                        <div class="question-content mb-3" style="font-family: 'Georgia', serif;">
                            {!! Str::limit($q->question_text, 300) !!}
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted">Created by: {{ $q->user->name ?? 'Unknown' }}</small>
                            <div class="d-flex gap-2">
                                @if($q->user_id !== auth()->id() && $q->is_public)
                                    <form action="{{ route('questions.clone', $q->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="bi bi-copy"></i> Clone to Library
                                        </button>
                                    </form>
                                @endif
                                
                                @if($q->user_id === auth()->id() || auth()->user()->role === 'super_admin')
                                    <button class="btn btn-sm btn-outline-secondary rounded-circle border-0">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-inboxes display-1 text-muted"></i>
                <p class="mt-3">No questions found in the bank.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $questions->appends(request()->query())->links() }}
    </div>
</div>

<style>
    .bg-soft-primary { background-color: #eef6ff; }
    .extra-small { font-size: 0.7rem; }
</style>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    document.querySelectorAll('.search-select').forEach(el => {
        new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    });
</script>
@endsection