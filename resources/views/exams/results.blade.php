@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4>Results for {{ $exam->name }}</h4>
    <hr>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    <tr>
                        <td>{{ $submission->user->name }}</td>
                        <td>{{ ucfirst($submission->status) }}</td>
                        <td>{{ $submission->total_score }}</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row mt-4">
    <div class="col-md-6">
        <h6><i class="bi bi-shield-exclamation text-danger"></i> Proctoring Activity</h6>
        <div class="bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">
            @foreach($submission->proctoring_logs ?? [] as $log)
                <div class="mb-1 border-bottom pb-1">
                    <span class="text-muted">[{{ $log['time'] }}]</span> {{ $log['event'] }}
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-md-6">
        <h6><i class="bi bi-stopwatch"></i> Time Spent Per Question</h6>
        <ul class="list-group list-group-flush small">
            @foreach($exam->questions as $index => $q)
                @php $seconds = $submission->time_per_question[$q->id] ?? 0; @endphp
                <li class="list-group-item d-flex justify-content-between bg-transparent">
                    <span>Question {{ $index + 1 }}</span>
                    <span class="fw-bold">{{ floor($seconds / 60) }}m {{ $seconds % 60 }}s</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
</div>
@endsection