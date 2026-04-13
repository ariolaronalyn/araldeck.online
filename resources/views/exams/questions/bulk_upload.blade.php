@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4 border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-cloud-upload me-2"></i>Bulk Question & Exam Upload</h5>
                </div>
                <div class="card-body p-4">
                    
                    {{-- Step 1: Template Preparation --}}
                    <div class="mb-5 border-bottom pb-4">
                        <h6 class="fw-bold text-primary mb-3">Step 1: Prepare your Template</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Course</label>
                                <select id="tpl_course" class="form-select rounded-pill" onchange="toggleNewInput(this, 'new_course_wrapper')">
                                    <option value="">-- Choose --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->title }}">{{ $course->title }}</option>
                                    @endforeach
                                    <option value="others">+ Others (New)</option>
                                </select>
                                <div id="new_course_wrapper" class="mt-2 d-none">
                                    <input type="text" id="tpl_new_course" class="form-control rounded-pill" placeholder="Enter course name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Subject</label>
                                <select id="tpl_subject" class="form-select rounded-pill" onchange="toggleNewInput(this, 'new_subject_wrapper')">
                                    <option value="">-- Choose --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->title }}">{{ $subject->title }}</option>
                                    @endforeach
                                    <option value="others">+ Others (New)</option>
                                </select>
                                <div id="new_subject_wrapper" class="mt-2 d-none">
                                    <input type="text" id="tpl_new_subject" class="form-control rounded-pill" placeholder="Enter subject name">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Rows</label>
                                <input type="number" id="tpl_rows" class="form-control rounded-pill" value="10">
                            </div>
                            <div class="col-md-2">
                                <button type="button" onclick="downloadDynamicTemplate()" class="btn btn-success w-100 rounded-pill shadow-sm">
                                    <i class="bi bi-download me-1"></i> Get
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Configure & Upload --}}
                    <form action="{{ route('questions.store_bulk') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h6 class="fw-bold text-primary mb-4">Step 2: Upload & Configure Exam Settings</h6>
                        
                        <div class="row g-4">
                            {{-- Exam Identity --}}
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Exam Name</label>
                                <input type="text" name="exam_name" class="form-control rounded-pill" placeholder="e.g., Political Law Final Mock" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Exam Type</label>
                                <select name="type" id="exam_type" class="form-select rounded-pill" onchange="toggleCollaborators(this)">
                                    <option value="self">Self Study (Private)</option>
                                    <option value="group">Group Study (Collaborative)</option>
                                    <option value="class">Through Class (Teacher Graded)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Assessment Tag</label>
                                <select name="assessment_type_id" class="form-select rounded-pill">
                                    <option value="mock">Mock Exam</option>
                                    <option value="prelim">Prelim</option>
                                </select>
                            </div>

                            {{-- Dynamic Collaborators Field --}}
                            <div class="col-md-12 d-none" id="collab_wrapper">
                                <label class="form-label small fw-bold">Collaborator Emails (Comma separated)</label>
                                <input type="text" name="collaborator_emails" class="form-control rounded-pill" placeholder="user@example.com, friend@example.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Target Course</label>
                                <select name="course_id" class="form-select rounded-pill" required>
                                    <option value="">-- Choose --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Target Subject</label>
                                <select name="subject_id" class="form-select rounded-pill" required>
                                    <option value="">-- Choose --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="my-4">

                            {{-- Timer Logic --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Timer Logic</label>
                                <select name="timer_type" class="form-select rounded-pill">
                                    <option value="overall">Overall Timer (e.g. 60 mins total)</option>
                                    <option value="per_question">Per Question (e.g. 2 mins each)</option>
                                    <option value="equal">Equally Divided (Total / Questions)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Duration (Mins)</label>
                                <input type="number" name="total_time_minutes" class="form-control rounded-pill" value="60">
                            </div>

                            {{-- Replace the current col-md-3 for allow_pause with this --}}
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_pause" id="allow_pause">
                                    <label class="form-check-label small fw-bold" for="allow_pause">Allow Pause</label>
                                </div>
                                {{-- Add this div --}}
                                <div id="pause_limit_wrap" class="d-none">
                                    <label class="form-label extra-small fw-bold mb-1">Pause Limit (Times)</label>
                                    <input type="number" name="pause_limit" class="form-control form-control-sm rounded-pill" value="0">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="bg-light p-4 rounded-4 border-2 border-dashed">
                                    <label class="form-label small fw-bold d-block mb-2">Upload Completed Template</label>
                                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                                <i class="bi bi-rocket-takeoff me-2"></i> Create Exam & Import Questions
                            </button>
                            <div class="text-center mt-3">
                                <a href="{{ route('exams.create') }}" class="text-muted small text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> Back to Manual Exam Builder
                                </a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleNewInput(select, wrapperId) {
    const wrapper = document.getElementById(wrapperId);
    wrapper.classList.toggle('d-none', select.value !== 'others');
}

function toggleCollaborators(select) {
    const wrapper = document.getElementById('collab_wrapper');
    wrapper.classList.toggle('d-none', select.value !== 'group');
}

function downloadDynamicTemplate() {
    let course = document.getElementById('tpl_course').value;
    if(course === 'others') course = document.getElementById('tpl_new_course').value;
    
    let subject = document.getElementById('tpl_subject').value;
    if(subject === 'others') subject = document.getElementById('tpl_new_subject').value;
    
    const rows = document.getElementById('tpl_rows').value;

    if(!course || !subject) {
        alert('Please select or enter a Course and Subject first.');
        return;
    }

    const url = `{{ route('questions.download_template') }}?course=${encodeURIComponent(course)}&subject=${encodeURIComponent(subject)}&rows=${rows}`;
    window.location.href = url;
}

// Add this inside your script tag
document.addEventListener('DOMContentLoaded', function() {
    const allowPauseToggle = document.getElementById('allow_pause');
    const pauseLimitWrap = document.getElementById('pause_limit_wrap');
    
    if (allowPauseToggle) {
        allowPauseToggle.addEventListener('change', function() {
            if (this.checked) {
                pauseLimitWrap.classList.remove('d-none');
            } else {
                pauseLimitWrap.classList.add('d-none');
                // Optional: Reset value to 0 when disabled
                pauseLimitWrap.querySelector('input').value = 0;
            }
        });
    }
});
</script>
@endsection