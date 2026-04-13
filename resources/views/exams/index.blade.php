@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Mock Exams</h4>
        <!-- @if(in_array(auth()->user()->role, ['teacher', 'admin', 'super_admin'])) -->
            <a href="{{ route('exams.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg"></i> Create New Exam
            </a>
        <!-- @endif -->
        {{-- REMOVE THE EDIT BUTTON FROM HERE --}}
    </div>

    <div class="row g-4">
        @forelse($exams as $exam)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-info-subtle text-info border border-info">
                                {{ $exam->assessmentType->name ?? 'General' }}
                            </span>
                            <span class="badge bg-light text-dark border">{{ ucfirst($exam->type) }}</span>
                        </div>
                        <h5 class="fw-bold">{{ $exam->name }}</h5>
                        <p class="text-muted small mb-3">Total Questions: {{ $exam->questions_count }}</p>
                        
                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                @php
                                    // Check if user has a completed submission for this exam
                                    $userSubmission = $exam->submissions->first();
                                    $hasCompleted = $userSubmission && $userSubmission->status === 'completed';
                                @endphp

                                {{-- Dynamic Button Text --}}
                                <button class="btn {{ $hasCompleted ? 'btn-outline-primary' : 'btn-primary' }} btn-sm rounded-pill px-3" 
                                    onclick="confirmStart('{{ $exam->id }}', '{{ $exam->name }}', {{ $exam->total_time_minutes }}, {{ $hasCompleted ? 'true' : 'false' }})">
                                    {{ $hasCompleted ? 'Re-take Exam' : 'Take Exam' }}
                                </button>

                                {{-- START EXAM MODAL (Keep one copy outside the loop or update IDs if inside) --}}
                                <div class="modal fade" id="startExamModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-body p-4 text-center">
                                                <i id="modal-icon" class="bi bi-exclamation-triangle text-warning display-4"></i>
                                                <h5 class="fw-bold mt-3" id="modal-exam-name">Ready to start?</h5>
                                                <p class="text-muted small" id="modal-description">
                                                    You have <span id="modal-exam-time" class="fw-bold"></span> minutes to complete this exam.
                                                </p>
                                                <div id="retake-warning" class="alert alert-info border-0 small text-start d-none">
                                                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Note:</strong> You have already completed this exam once. Re-taking it will create a new attempt record.
                                                </div>
                                                <div class="alert alert-warning border-0 small text-start">
                                                    <i class="bi bi-shield-lock-fill me-2"></i> <strong>Proctoring Enabled:</strong><br>
                                                    Your activity is logged. Leaving the browser tab or switching windows will be recorded.
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <a href="" id="confirm-start-link" class="btn btn-primary rounded-pill">Start My Exam</a>
                                                    <button class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Results visibility logic (unchanged) --}}
                                @php
                                    $isTeacherOrAdmin = in_array(auth()->user()->role, ['teacher', 'admin', 'super_admin']);
                                    $isCreator = ($exam->user_id === auth()->id());
                                    $canSeeResults = ($exam->type !== 'class') || $isTeacherOrAdmin || $isCreator;
                                @endphp

                                @if($canSeeResults)
                                    <a href="{{ route('exams.results', $exam->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill">Results</a>
                                @endif
                            </div>

                            {{-- Edit button logic (unchanged) --}}
                            @if($exam->user_id === auth()->id() || auth()->user()->role === 'super_admin')
                                <a href="{{ route('exams.edit', $exam->id) }}" class="btn btn-sm btn-light border rounded-circle" title="Edit Exam">
                                    <i class="bi bi-pencil-square text-secondary"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-file-earmark-text display-1 text-muted opacity-25"></i>
                <p class="mt-3 text-muted">No exams created yet. Click "Create New Exam" to get started.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
function confirmStart(id, name, time, isRetake) {
    const modalTitle = document.getElementById('modal-exam-name');
    const warningDiv = document.getElementById('retake-warning');
    const modalIcon = document.getElementById('modal-icon');

    if (isRetake) {
        modalTitle.innerText = `Re-take ${name}?`;
        warningDiv.classList.remove('d-none');
        modalIcon.className = "bi bi-arrow-repeat text-primary display-4";
    } else {
        modalTitle.innerText = `Start ${name}?`;
        warningDiv.classList.add('d-none');
        modalIcon.className = "bi bi-exclamation-triangle text-warning display-4";
    }

    document.getElementById('modal-exam-time').innerText = time;
    document.getElementById('confirm-start-link').href = `/exams/${id}/start`;
    
    new bootstrap.Modal(document.getElementById('startExamModal')).show();
}
</script>
@endsection
