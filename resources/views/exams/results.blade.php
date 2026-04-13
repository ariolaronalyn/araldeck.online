@extends('layouts.app')

@section('content')
{{-- Include DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Results for <span class="text-primary">{{ $exam->name }}</span></h4>
        <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Back to Exams
        </a>
    </div>
    
    <hr>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <table id="resultsTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    <tr>
                        <td class="fw-bold">{{ $submission->user->name }}</td>
                        <td>
                            <span class="badge {{ $submission->status === 'completed' ? 'bg-success' : 'bg-warning' }} rounded-pill">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </td>
                        <td><span class="h6 mb-0">{{ $submission->total_score }}</span></td>
                        <td class="text-end">
                            {{-- We store student data in data-attributes for the JS to pick up --}}
                            <a href="{{ route('exams.submissions.details', $submission->id) }}" class="btn btn-sm btn-primary rounded-pill px-3"> View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="details-section" class="row mt-4 d-none">
        <div class="col-12"><h5 class="mb-3">Detailed Analytics: <span id="selected-student-name" class="text-primary"></span></h5></div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-shield-exclamation text-danger"></i> Proctoring Activity</h6>
                    <div id="proctoring-logs-container" class="bg-light p-3 rounded-3 small mt-2" style="max-height: 250px; overflow-y: auto;">
                        {{-- Injected by JS --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-stopwatch"></i> Time Spent Per Question</h6>
                    <ul id="time-spent-list" class="list-group list-group-flush small mt-2">
                        @foreach($exam->questions as $index => $q)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0" data-q-id="{{ $q->id }}">
                                <span>Question {{ $index + 1 }}</span>
                                <span class="fw-bold time-val">0m 0s</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DataTables & jQuery Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // 1. Initialize DataTable
    $('#resultsTable').DataTable({
        "order": [[ 2, "desc" ]], // Sort by score descending by default
        "pageLength": 10,
        "language": {
            "search": "Filter students:"
        }
    });

    // 2. Handle "View Details" click
    $('.view-details-btn').on('click', function() {
        const btn = $(this);
        const name = btn.closest('tr').find('td:first').text();
        const logs = btn.data('logs');
        const times = btn.data('times');

        // Show section and update name
        $('#details-section').removeClass('d-none');
        $('#selected-student-name').text(name);

        // Update Proctoring Logs
        let logsHtml = '';
        if(logs.length > 0) {
            logs.forEach(log => {
                logsHtml += `<div class="mb-2 border-bottom pb-1">
                    <span class="text-muted small">[${log.time}]</span> ${log.event}
                </div>`;
            });
        } else {
            logsHtml = '<div class="text-muted italic">No proctoring incidents recorded.</div>';
        }
        $('#proctoring-logs-container').html(logsHtml);

        // Update Time Spent List
        $('#time-spent-list li').each(function() {
            const qId = $(this).data('q-id');
            const seconds = times[qId] || 0;
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            $(this).find('.time-val').text(`${m}m ${s}s`);
        });

        // Scroll to details
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });
});
</script>

<style>
    /* Styling for a cleaner look */
    .dataTables_filter { margin-bottom: 1rem; }
    .table th { border-top: none; }
    #proctoring-logs-container::-webkit-scrollbar { width: 4px; }
    #proctoring-logs-container::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
</style>
@endsection