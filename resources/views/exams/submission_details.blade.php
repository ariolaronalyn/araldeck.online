@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Exam Review: {{ $submission->user->name }}</h4>
            <span class="text-muted">{{ $submission->exam->name }} ({{ $submission->exam->assessmentType->name ?? 'General' }})</span>
        </div>
        <a href="{{ route('exams.results', $submission->exam_id) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left"></i> Back to All Results
        </a>
    </div>

    {{-- Analytics Cards --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Total Score</div>
                    <h1 class="display-4 fw-bold text-primary mb-0">{{ $submission->total_score }}</h1>
                    <div class="text-muted mt-2">Status: <span class="badge bg-success">{{ ucfirst($submission->status) }}</span></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-stopwatch text-info me-2"></i>Time per Question</h6>
                    <div style="max-height: 150px; overflow-y: auto;">
                        <ul class="list-group list-group-flush small">
                            @foreach($submission->exam->questions as $index => $q)
                                @php $sec = $submission->time_per_question[$q->id] ?? 0; @endphp
                                <li class="list-group-item d-flex justify-content-between bg-transparent px-0">
                                    <span>Q{{ $index + 1 }}</span>
                                    <span class="fw-bold">{{ floor($sec / 60) }}m {{ $sec % 60 }}s</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-shield-exclamation text-danger me-2"></i>Proctoring Logs</h6>
                    <div class="bg-light p-2 rounded small" style="max-height: 150px; overflow-y: auto;">
                        @forelse($submission->proctoring_logs ?? [] as $log)
                            <div class="mb-1 border-bottom pb-1" style="font-size: 0.75rem;">
                                <span class="text-muted">[{{ $log['time'] }}]</span> {{ $log['event'] }}
                            </div>
                        @empty
                            <p class="text-muted italic mb-0">No incidents recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Question and Answer List --}}
    <h5 class="fw-bold mb-3">Detailed Response Review</h5>
    @foreach($submission->exam->questions as $index => $question)
        @php $answer = $answers->get($question->id); @endphp
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between">
                    <h6 class="fw-bold text-primary">Question {{ $index + 1 }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($answer)
                            <label class="small fw-bold text-muted">Score:</label>
                            <input type="number" 
                                class="form-control form-control-sm grading-input" 
                                style="width: 70px;" 
                                value="{{ $answer->points_given ?? 0 }}"
                                data-answer-id="{{ $answer->id }}"
                                {{-- Frontend check: Disable if it's class type and user isn't creator --}}
                                @if($submission->exam->type === 'class' && $submission->exam->user_id !== auth()->id()) disabled @endif
                            >
                            <span class="text-success save-spinner d-none"><i class="bi bi-check-circle-fill"></i></span>
                        @else
                            <span class="badge bg-light text-muted border">No Answer Recorded</span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-2 mb-4 p-3 bg-light rounded-3">
                    {!! $question->question_text !!}
                </div>

                <div class="mb-2 small fw-bold text-muted text-uppercase">Student's Essay Response:</div>
                <div class="p-4 border rounded-4 bg-white" style="min-height: 100px; line-height: 1.6;">
                    @if($answer && $answer->answer_text)
                        {!! $answer->answer_text !!}
                    @else
                        <em class="text-muted">No answer provided.</em>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
<script>
$('.grading-input').on('change', function() {
    const input = $(this);
    const answerId = input.data('answer-id');
    const points = input.val();
    const spinner = input.siblings('.save-spinner');

    fetch("{{ route('exams.grade') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ answer_id: answerId, points: points })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'Graded') {
            spinner.removeClass('d-none').fadeIn().delay(1000).fadeOut();
            // Update total score display on page if you have an ID for it
            $('#total-score-display').text(data.total);
        } else {
            alert(data.error || 'Permission denied');
        }
    });
});
</script>