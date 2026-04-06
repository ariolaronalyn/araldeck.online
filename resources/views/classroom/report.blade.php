@extends('layouts.app')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Grade Report: {{ $deck->name }}</h4>
            <p class="text-muted mb-0">Class: {{ $class->name }} | Section: {{ $class->section }}</p>
        </div>
        <button onclick="downloadCSV()" class="btn btn-success rounded-pill px-4">
            <i class="bi bi-file-earmark-excel"></i> Download CSV
        </button>
    </div>

    <div class="card border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="reportTable">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Date Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $row)
                    <tr>
                        <td class="fw-bold">{{ $row->name }}</td>
                        <td>{{ $row->email }}</td>
                        <td>
                            @if($row->completed_at)
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-danger">Pending</span>
                            @endif
                        </td>
                        <td>{{ $row->score ?? '--' }} / {{ $row->total_questions ?? '--' }}</td>
                        <td>
                            @if($row->score !== null)
                                @php $pct = ($row->score / $row->total_questions) * 100; @endphp
                                {{ round($pct) }}%
                            @else
                                0%
                            @endif
                        </td>
                        <td class="small">
                            {{ $row->completed_at ? \Carbon\Carbon::parse($row->completed_at)->format('M d, Y h:i A') : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- DataTables Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "order": [[ 4, "desc" ]], // Sort by Percentage by default
        "language": {
            "search": "Filter Students:",
            "paginate": {
                "next": '<i class="bi bi-chevron-right"></i>',
                "previous": '<i class="bi bi-chevron-left"></i>'
            }
        }
    });
});

function downloadCSV() {
    let csv = [];
    // Target the table specifically to avoid grabbing DataTables UI elements
    let table = document.getElementById("reportTable");
    let rows = table.querySelectorAll("tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) {
            // Clean up the text (remove extra spaces/newlines from badges)
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").trim();
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }

    let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    let downloadLink = document.createElement("a");
    downloadLink.download = "Grade_Report_{{ Str::slug($class->name) }}_{{ Str::slug($deck->name) }}.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}
</script>
@endsection