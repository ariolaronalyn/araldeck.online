@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<div class="container py-4">
    <form action="{{ route('exams.update', $exam->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- Critical for Update --}}
        
        <div class="row g-4">
            {{-- LEFT COLUMN: SETTINGS --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Edit Exam Settings</h5>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Exam Name</label>
                            <input type="text" name="exam_name" class="form-control" value="{{ $exam->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Exam Type</label>
                            <select name="type" class="search-select">
                                <option value="self" {{ $exam->type == 'self' ? 'selected' : '' }}>Self Study (Private)</option>
                                <option value="group" {{ $exam->type == 'group' ? 'selected' : '' }}>Group Study (Collaborative)</option>
                                <option value="class" {{ $exam->type == 'class' ? 'selected' : '' }}>Through Class (Teacher Graded)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Assessment Tag</label>
                            <select name="assessment_type_id" class="search-select">
                                @foreach($assessmentTypes as $type)
                                    <option value="{{ $type->id }}" {{ $exam->assessment_type_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Course</label>
                            <select name="course_id" class="search-select" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $exam->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Subject</label>
                            <select name="subject_id" class="search-select" required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $exam->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 {{ $exam->type == 'group' ? '' : 'd-none' }}" id="collaborators-wrapper">
                            <label class="form-label small fw-bold">Collaborators</label>
                            <input type="text" name="collaborator_emails" id="collab-emails" class="form-control" value="{{ $collaboratorEmails }}">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Timer Logic</label>
                            <select name="timer_type" class="search-select">
                                <option value="overall" {{ $exam->timer_type == 'overall' ? 'selected' : '' }}>Overall Timer</option>
                                <option value="per_question" {{ $exam->timer_type == 'per_question' ? 'selected' : '' }}>Per Question</option>
                                <option value="equal" {{ $exam->timer_type == 'equal' ? 'selected' : '' }}>Equally Divided</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Duration (Minutes)</label>
                            <input type="number" name="total_time_minutes" class="form-control" value="{{ $exam->total_time_minutes }}">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="allow_pause" id="allow_pause" {{ $exam->allow_pause ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold" for="allow_pause">Allow Pause</label>
                        </div>

                        <div id="pause_limit_wrap" class="mb-4 {{ $exam->allow_pause ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Pause Limit (Times)</label>
                            <input type="number" name="pause_limit" class="form-control" value="{{ $exam->pause_limit }}">
                        </div>

                        <button type="submit" id="submit-exam-btn" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                            Update & Save Changes
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: QUESTION SELECTION --}}
            <div class="col-lg-8" id="question-pool-container">
                <h5 class="fw-bold mb-3">Question Pool <span id="selection-hint"></span></h5>
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="questionTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                                    <th>Question Preview</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $selectedIds = $exam->questions->pluck('id')->toArray(); @endphp
                                @foreach($questionPool as $q)
                                <tr class="question-row" data-course="{{ $q->course_id }}" data-subject="{{ $q->subject_id }}">
                                    <td>
                                        <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" 
                                            class="form-check-input q-checkbox"
                                            {{ in_array($q->id, $selectedIds) ? 'checked' : '' }}>
                                    </td>
                                    <td>{!! Str::limit(strip_tags($q->question_text), 100) !!}</td>
                                    <td>{{ $q->default_points }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let typeSelectInstance, courseSelectInstance, subjectSelectInstance;

        // Initialize Selects
        document.querySelectorAll('.search-select').forEach(el => {
            const control = new TomSelect(el, { create: false });
            if (el.name === 'type') typeSelectInstance = control;
            if (el.name === 'course_id') courseSelectInstance = control;
            if (el.name === 'subject_id') subjectSelectInstance = control;
            control.on('change', () => { validateForm(); filterQuestions(); });
        });

        // Toggle Collaborators
        const collabWrapper = document.getElementById('collaborators-wrapper');
        typeSelectInstance.on('change', (val) => {
            collabWrapper.classList.toggle('d-none', val !== 'group');
        });

        // Filter Logic
        function filterQuestions() {
            const course = courseSelectInstance.getValue();
            const subject = subjectSelectInstance.getValue();
            document.querySelectorAll('.question-row').forEach(row => {
                const match = row.dataset.course == course && row.dataset.subject == subject;
                row.style.display = match ? '' : 'none';
                // Note: We DON'T uncheck questions here in Edit mode so users don't lose previous selections
            });
        }

        // Validation & Count
        function validateForm() {
            const questionsChecked = document.querySelectorAll('.q-checkbox:checked').length > 0;
            document.getElementById('submit-exam-btn').disabled = !questionsChecked;
        }

        document.querySelectorAll('.q-checkbox').forEach(cb => {
            cb.addEventListener('change', () => { validateForm(); });
        });

        // Run on load
        filterQuestions();
        validateForm();
    });
</script>
@endsection