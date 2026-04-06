@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Breadcrumbs & Header --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('classroom.index') }}">My Classes</a></li>
            <li class="breadcrumb-item active">{{ $class->name }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">{{ $class->name }}</h2>
            <p class="text-muted">Section: {{ $class->section }} | SY: {{ $class->school_year }}</p>
        </div>
        @if(auth()->user()->role === 'teacher')
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#scheduleDeckModal">
        <i class="bi bi-calendar-plus me-1"></i> Schedule a Deck
    </button>
@endif
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- Left Column: Student Management --}}
        <div class="col-lg-4">
            @if(auth()->user()->role === 'teacher')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-plus me-2"></i>Enroll Student</h6>
                        <form action="{{ route('classroom.add_student', $class->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Student's Email" required>
                                <button class="btn btn-dark" type="submit">Add</button>
                            </div>
                            <small class="text-muted mt-2 d-block">Student must have an account first.</small>
                        </form>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Enrolled Students ({{ $students->count() }})</h6>
                        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            @forelse($students as $student)
                                <div class="list-group-item px-0 border-0 d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle p-2 me-3">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small">{{ $student->name }}</div>
                                        <div class="text-muted extra-small">{{ $student->email }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small text-center py-3">No students enrolled yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Scheduled Decks & Reports --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>Scheduled Learning Materials</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Deck Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scheduledDecks as $sd)
                                    <tr>
                                        <td class="fw-bold">{{ $sd->deck_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sd->start_at)->format('M d, g:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sd->end_at)->format('M d, g:i A') }}</td>
                                        <td>
                                            @php 
                                                $now = now();
                                                $startDate = \Carbon\Carbon::parse($sd->start_at);
                                                $endDate = \Carbon\Carbon::parse($sd->end_at);
                                            @endphp

                                            @if($endDate->isPast())
                                                {{-- If the end date has passed (or was set to now() by the Close button) --}}
                                                <span class="badge bg-secondary">Closed / Expired</span>
                                            @elseif($now->lt($startDate))
                                                {{-- If current time is before the start time --}}
                                                <span class="badge bg-warning text-dark">Scheduled</span>
                                            @else
                                                {{-- If current time is between start and end --}}
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(auth()->user()->role === 'teacher')
                                                {{-- Show 'Close' button only if the deck hasn't expired yet --}}
                                                @if(\Carbon\Carbon::parse($sd->end_at)->isFuture())
                                                        {{-- CASE: Deck is ACTIVE -> Show CLOSE button --}}
                                                        <form action="{{ route('classroom.close_schedule', [$class->id, $sd->id]) }}" 
                                                            method="POST" class="d-inline" 
                                                            onsubmit="return confirm('Close this deck for all students?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 me-1">
                                                                <i class="bi bi-x-circle"></i> Close
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- CASE: Deck is EXPIRED/CLOSED -> Show RE-OPEN button --}}
                                                        <form action="{{ route('classroom.reopen_schedule', [$class->id, $sd->id]) }}" 
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 me-1">
                                                                <i class="bi bi-unlock"></i> Re-open
                                                            </button>
                                                        </form>
                                                    @endif
                                                {{-- Edit Button --}}
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editScheduleModal{{ $sd->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <a href="{{ route('classroom.report', [$class->id, $sd->deck_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    Report
                                                </a>
                                                
                                                {{-- MODAL: Moved slightly out of the button flow for clarity --}}
                                                <div class="modal fade" id="editScheduleModal{{ $sd->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered text-start">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('classroom.update_schedule', [$class->id, $sd->id]) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header border-0 bg-light">
                                                                    <h5 class="modal-title fw-bold">Edit Schedule</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-4">
                                                                    <p class="mb-3 small">Updating: <strong>{{ $sd->deck_name }}</strong></p>
                                                                    <div class="row g-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-bold">Start Date</label>
                                                                            <input type="datetime-local" name="start_at" class="form-control" 
                                                                                value="{{ date('Y-m-d\TH:i', strtotime($sd->start_at)) }}" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-bold">End Date</label>
                                                                            <input type="datetime-local" name="end_at" class="form-control" 
                                                                                value="{{ date('Y-m-d\TH:i', strtotime($sd->end_at)) }}" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-0">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted small">
                                            No decks have been scheduled for this class yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Schedule Deck --}}
<div class="modal fade" id="scheduleDeckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('classroom.schedule_deck', $class->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Assign Deck to Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">1. Filter by Course</label>
                    <select id="course_filter" class="form-select mb-3">
                        <option value="" selected disabled>-- Select Course --</option>
                        {{-- FIX: Use the relationship to get course title --}}
                        @foreach(collect($myDecks)->pluck('course.title')->unique() as $courseTitle)
                            <option value="{{ $courseTitle }}">{{ $courseTitle }}</option>
                        @endforeach
                    </select>

                    <label class="form-label small fw-bold">2. Filter by Subject</label>
                    <select id="subject_filter" class="form-select mb-3" disabled>
                        <option value="" selected disabled>-- Choose Course First --</option>
                    </select>

                    <label class="form-label small fw-bold">3. Select Deck</label>
                    <select name="deck_info" id="deck_info_select" class="form-select" required disabled>
                        <option value="" disabled selected>-- Choose Subject First --</option>
                        @foreach($myDecks as $deck)
                            <option value="{{ $deck->id }}" 
                                    data-course="{{ $deck->course->title }}" 
                                    data-subject="{{ $deck->subject->title }}"
                                    style="display: none;">
                                {{ $deck->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Start Date</label>
                        <input type="datetime-local" name="start_at" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">End Date</label>
                        <input type="datetime-local" name="end_at" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Schedule Now</button>
            </div>
        </form>
    </div>
</div>
 
<style>
    .extra-small { font-size: 0.75rem; }
    .avatar-sm { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
</style>
<script>

    function startStudyDeck(subjectId, deckName, mode) {
        // This assumes your flashcard route looks like: /flashcards?subject_id=X&deck_name=Y&mode=quiz
        const url = `/flashcards?subject_id=${subjectId}&deck_name=${encodeURIComponent(deckName)}&mode=${mode}`;
        window.location.href = url;
    }
    
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_filter');
    const subjectSelect = document.getElementById('subject_filter');
    const deckSelect = document.getElementById('deck_info_select');
    const deckOptions = deckSelect.querySelectorAll('option');

    // 1. When Course Changes
    courseSelect.addEventListener('change', function() {
        let selectedCourse = this.value;
        
        // Reset Subject and Deck
        subjectSelect.disabled = false;
        subjectSelect.innerHTML = '<option value="" selected disabled>-- Select Subject --</option>';
        deckSelect.disabled = true;
        deckSelect.value = "";

        // Find unique subjects for this course
        let availableSubjects = new Set();
        deckOptions.forEach(opt => {
            if (opt.getAttribute('data-course') === selectedCourse) {
                availableSubjects.add(opt.getAttribute('data-subject'));
            }
        });

        // Populate Subject Dropdown
        availableSubjects.forEach(subj => {
            let option = document.createElement('option');
            option.value = subj;
            option.textContent = subj;
            subjectSelect.appendChild(option);
        });
    });

    // 2. When Subject Changes
    subjectSelect.addEventListener('change', function() {
        let selectedCourse = courseSelect.value;
        let selectedSubject = this.value;

        deckSelect.disabled = false;
        deckSelect.value = "";

        // Filter Deck list based on both Course and Subject
        deckOptions.forEach(option => {
            const match = option.getAttribute('data-course') === selectedCourse && 
                          option.getAttribute('data-subject') === selectedSubject;
            
            option.style.display = match ? 'block' : 'none';
        });
    });
});
</script>
@endsection