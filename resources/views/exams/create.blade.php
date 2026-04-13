@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<div class="container py-4">
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('exams.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            {{-- LEFT COLUMN: SETTINGS --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Exam Settings</h5>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Exam Name</label>
                            <input type="text" name="exam_name" class="form-control" placeholder="e.g. Criminal Law Mock Exam" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Exam Type</label>
                            <select name="type" class="search-select">
                                <option value="self">Self Study (Private)</option>
                                <option value="group">Group Study (Collaborative)</option>
                                <option value="class">Through Class (Teacher Graded)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Assessment Tag</label>
                            <select name="assessment_type_id" class="search-select">
                                @foreach($assessmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Course</label>
                            <select name="course_id" class="search-select" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Subject</label>
                            <select name="subject_id" class="search-select" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            {{-- Change this section in your LEFT COLUMN --}}
                            <div class="mb-3 d-none" id="collaborators-wrapper">
                                <label class="form-label small fw-bold">Collaborators (Enter Emails)</label>
                                <input type="text" name="collaborator_emails" id="collab-emails" class="form-control" placeholder="Type email and press Enter...">
                                <div class="form-text extra-small text-muted">Examinees will be able to see each other's answers in Group mode.</div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Timer Logic</label>
                            <select name="timer_type" class="search-select" id="timer_type">
                                <option value="overall">Overall Timer (e.g. 60 mins total)</option>
                                <option value="per_question">Per Question (e.g. 2 mins each)</option>
                                <option value="equal">Equally Divided (Total / Questions)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Duration (Minutes)</label>
                            <input type="number" name="total_time_minutes" class="form-control" value="60">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="allow_pause" id="allow_pause">
                            <label class="form-check-label small fw-bold" for="allow_pause">Allow Pause</label>
                        </div>

                        <div id="pause_limit_wrap" class="mb-4 d-none">
                            <label class="form-label small fw-bold">Pause Limit (Times)</label>
                            <input type="number" name="pause_limit" class="form-control" value="0">
                        </div>

                        <button type="submit" id="submit-exam-btn" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" disabled>
                            Create & Publish Exam
                        </button>
                        {{-- Add a small helper text below the button --}}
                        <div id="submit-helper-text" class="extra-small text-danger text-center mt-2">
                            Please complete all settings and select at least one question.
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: QUESTION SELECTION --}}
            <div class="col-lg-8 d-none" id="question-pool-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0">Question Pool <span class="text-danger small" id="selection-hint">*</span></h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('questions.upload_form') }}" class="btn btn-sm btn-outline-success rounded-pill">
                            <i class="bi bi-file-earmark-excel"></i> Upload Bulk
                        </a>
                        <a href="{{ route('questions.create') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-plus-circle"></i> Create New Question
                        </a>
                    </div>
                </div>
                    
                <div class="input-group mb-3 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="poolSearch" class="form-control border-start-0" placeholder="Search questions by content...">
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive" style="max-height: 800px;">
                        <table class="table table-hover align-middle mb-0" id="questionTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Question Preview</th>
                                    <th width="100">Points</th>
                                    <th width="120">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questionPool as $q)
                                <tr class="question-row" data-course="{{ $q->course_id }}" data-subject="{{ $q->subject_id }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="form-check-input q-checkbox">
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <strong>{!! Str::limit(strip_tags($q->question_text), 150) !!}</strong>
                                        </div>
                                        <div class="extra-small text-muted d-flex gap-2">
                                            <span><i class="bi bi-journal-bookmark"></i> {{ $q->course->title ?? 'N/A' }}</span>
                                            <span><i class="bi bi-tag"></i> {{ $q->subject->title ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $q->default_points }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $q->is_public ? 'bg-info-subtle text-info border-info' : 'bg-warning-subtle text-warning border-warning' }} border small">
                                            {{ $q->is_public ? 'Public' : 'Private' }}
                                        </span>
                                    </td>
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
        // We'll store the Exam Type control here
        let typeSelectInstance = null;
        let courseSelectInstance = null; 
        let subjectSelectInstance = null;
        const submitBtn = document.getElementById('submit-exam-btn');
        const helperText = document.getElementById('submit-helper-text');
        const examNameInput = document.querySelector('input[name="exam_name"]');

        function validateForm() {
        const nameFilled = examNameInput.value.trim() !== '';
        const courseSelected = courseSelectInstance.getValue() !== '';
        const subjectSelected = subjectSelectInstance.getValue() !== '';
        const questionsChecked = document.querySelectorAll('.q-checkbox:checked').length > 0;

        // Enable button only if all conditions met
        if (nameFilled && courseSelected && subjectSelected && questionsChecked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
            helperText.classList.add('d-none');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-secondary');
            helperText.classList.remove('d-none');
        }
    }

        // Attach listeners to standard inputs
        examNameInput.addEventListener('input', validateForm);
        
        // 1. Toggle Pause Limit logic
        const allowPauseToggle = document.getElementById('allow_pause');
        const pauseLimitWrap = document.getElementById('pause_limit_wrap');
        
        if (allowPauseToggle) {
            allowPauseToggle.addEventListener('change', function() {
                pauseLimitWrap.classList.toggle('d-none', !this.checked);
            });
        }

        // 2. Initialize Searchable Selects (Single Loop)
        document.querySelectorAll('.search-select').forEach(el => {
            const settings = {
                create: false,
                sortField: { field: "text", direction: "asc" }
            };

            const control = new TomSelect(el, settings);

            control.on('change', validateForm);

            // Capture the 'type' (Exam Type) field instance specifically
            if (el.name === 'type') {
                typeSelectInstance = control;
            }
            // Capture the 'course' field instance specifically
            if (el.name === 'course_id') {
                courseSelectInstance = control;
            }
            // Capture the 'subject' field instance specifically
            if (el.name === 'subject_id') {
                subjectSelectInstance = control;
            }
        });

        // Toggle Collaborators Logic using the captured instance
        const collabWrapper = document.getElementById('collaborators-wrapper');
        if (typeSelectInstance) {
            typeSelectInstance.on('change', function(value) {
                if (value === 'group') {
                    collabWrapper.classList.remove('d-none');
                } else {
                    collabWrapper.classList.add('d-none');
                    // Safely clear the collaborator emails if they exist
                    const collabInput = document.getElementById('collab-emails').tomselect;
                    if (collabInput) collabInput.clear();
                }
            });
        }

        // 3. Initialization for Collaborators with Validation
        new TomSelect('#collab-emails', {
            persist: false,
            createOnBlur: true,
            create: true,
            plugins: ['remove_button'],
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            },
            onOptionAdd: function(value, data) {
                const self = this;
                if (!value.includes('@')) {
                    alert('Please enter a valid email address.');
                    self.removeItem(value);
                    self.removeOption(value);
                    return false;
                }

                fetch(`{{ route('users.check_email') }}?email=${encodeURIComponent(value)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.exists) {
                            alert(`User with email "${value}" not found.`);
                            self.removeItem(value);
                            self.removeOption(value);
                        }
                    })
                    .catch(err => console.error('Error checking email:', err));
            },
            render: {
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            }
        });

        // 4. Handle Checkbox Logic
        const checkboxes = document.querySelectorAll('.q-checkbox');
        const selectedCount = document.getElementById('selected-count');
        const selectAll = document.getElementById('select-all');

        function updateCount() {
            let count = document.querySelectorAll('.q-checkbox:checked').length;
            // Visual indicator for the Question Pool header
            const hint = document.getElementById('selection-hint');
            if(count > 0) {
                hint.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            } else {
                hint.innerHTML = '*';
            }

            validateForm();
            selectedCount.innerText = `${count} Selected`;
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });
        }

        // 5. Question Pool Search
        const poolSearch = document.getElementById('poolSearch');
        if (poolSearch) {
            poolSearch.addEventListener('keyup', function() {
                let value = this.value.toLowerCase();
                let rows = document.querySelectorAll('#questionTable tbody tr');
                rows.forEach(row => {
                    let text = row.querySelector('td:nth-child(2)').innerText.toLowerCase();
                    row.style.display = text.includes(value) ? '' : 'none';
                });
            });
        }
        // --- NEW: Filter Question Pool Logic ---
    const poolContainer = document.getElementById('question-pool-container');
    const questionRows = document.querySelectorAll('.question-row');

    function filterQuestions() {
        const selectedCourse = courseSelectInstance.getValue();
        const selectedSubject = subjectSelectInstance.getValue();

        // Only show the pool if both are selected
        if (selectedCourse && selectedSubject) {
            poolContainer.classList.remove('d-none');
            
            let visibleCount = 0;
            questionRows.forEach(row => {
                const rowCourse = row.getAttribute('data-course');
                const rowSubject = row.getAttribute('data-subject');

                if (rowCourse == selectedCourse && rowSubject == selectedSubject) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                    // Uncheck hidden rows so they aren't accidentally submitted
                    row.querySelector('.q-checkbox').checked = false;
                }
            });
            updateCount(); // Reset the "X Selected" badge
        } else {
            poolContainer.classList.add('d-none');
        }
    }

        if (courseSelectInstance) courseSelectInstance.on('change', filterQuestions);
        if (subjectSelectInstance) subjectSelectInstance.on('change', filterQuestions);
        const noQuestionsMsg = document.getElementById('no-questions-alert');
        if (visibleCount === 0) {
            if (!noQuestionsMsg) {
                const msg = document.createElement('div');
                msg.id = 'no-questions-alert';
                msg.className = 'alert alert-warning border-0 small mt-2';
                msg.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>No questions found for this specific Course and Subject.';
                document.getElementById('questionTable').parentNode.appendChild(msg);
            }
        } else if (noQuestionsMsg) {
            noQuestionsMsg.remove();
        }
 
        filterQuestions(); 
        validateForm();
    });
</script>

<style>
    .ts-control { border-radius: 0.5rem !important; padding: 0.5rem !important; border: 1px solid #dee2e6 !important; }
    .extra-small { font-size: 0.75rem; }
    .sticky-top { z-index: 1020; }
</style>
@endsection