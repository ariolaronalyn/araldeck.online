@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Quiz Summary: <span class="text-primary">{{ $attempt->deck_name }}</span></h4>
        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">Back to Logs</a>
    </div>

    @if($attempt->override_logs)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle-fill me-2"></i> <strong>Note:</strong> This quiz score has been modified by a Teacher.
    </div>
    @endif

    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="table-responsive">
            <table class="table align-middle" id="summaryTable">
                <thead class="table-light">
                    <tr>
                        <th>Time Spent</th>
                        <th>Question</th>
                        <th>Correct Answer</th>
                        <th>Your Answer</th>
                        <th>Status</th>
                        @if(auth()->user()->role === 'teacher') <th>Action</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @if(empty($details))
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-1"></i> No detailed breakdown available for this older quiz attempt.
                            </td>
                        </tr>
                    @else

                            @foreach($details as $index => $item)
                                <tr class="{{ ($item['is_correct'] ?? false) ? '' : 'table-danger-subtle' }}">
                                    <td>{{ $item['time_spent'] ?? 0 }}s</td>
                                    {{-- Use ?? to provide fallbacks for old logs --}}
                                    <td>{!! $item['question'] ?? 'Question data unavailable' !!}</td>
                                    <td class="text-success fw-bold">{!! $item['correct_answer'] ?? 'N/A' !!}</td>
                                    <td class="{{ ($item['is_correct'] ?? false) ? 'text-success' : 'text-danger' }}">
                                        {{ $item['user_answer'] ?? '(No Answer)' }}
                                    </td>
                                    <td>
                                        @if($item['is_correct'] ?? false)
                                            <span class="badge bg-success">Correct</span>
                                        @else
                                            <span class="badge bg-danger">Wrong</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->role === 'teacher')
                                    <td>
                                        <form action="{{ route('settings.logs.override', $attempt->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="index" value="{{ $index }}">
                                            <button type="submit" class="btn btn-sm btn-warning">Override</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Teacher Logs --}}
    @if($attempt->override_logs)
    <div class="mt-4">
        <h6 class="fw-bold">Audit Logs (Teacher Actions)</h6>
        <ul class="list-group list-group-flush small">
            @foreach(json_decode($attempt->override_logs) as $log)
                <li class="list-group-item bg-transparent px-0 text-muted">
                    {{ $log->date }} - {{ $log->teacher_name }}: {{ $log->action }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection