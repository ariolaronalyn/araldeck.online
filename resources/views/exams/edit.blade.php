@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="container py-4">
    <form action="{{ route('exams.update', $exam->id) }}" method="POST" id="edit-exam-form">
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
                                    <th width="50">Action</th>
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
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light border" 
                                            onclick="openEditModal({{ json_encode(['id' => $q->id, 'text' => $q->question_text, 'guide' => $q->correct_answer_guide, 'points' => $q->default_points]) }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
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
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Edit Question Pool Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="edit-q-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Question Text</label>
                    <textarea id="edit-q-text" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Correct Answer Guide</label>
                    <textarea id="edit-q-guide" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Default Points</label>
                    <input type="number" id="edit-q-points" class="form-control">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveQuestionChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script> 
    let editModal;
    let questionEditor;
    let guideEditor;
    let table; // Global table variable

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Initialize DataTable
        table = $('#questionTable').DataTable({
            "pageLength": 10,
            "order": [[1, 'asc']],
            "columnDefs": [
                { "orderable": false, "targets": [0, 3] }
            ]
        });

        // 2. Initialize Modal & Editors
        editModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
        ClassicEditor.create(document.querySelector('#edit-q-text')).then(editor => { questionEditor = editor; });
        ClassicEditor.create(document.querySelector('#edit-q-guide')).then(editor => { guideEditor = editor; });

        // 3. Initialize TomSelects and handle dynamic filtering
        let courseSelectInstance, subjectSelectInstance, typeSelectInstance;

        document.querySelectorAll('.search-select').forEach(el => {
            const control = new TomSelect(el, { create: false });
            
            if (el.name === 'course_id') courseSelectInstance = control;
            if (el.name === 'subject_id') subjectSelectInstance = control;
            if (el.name === 'type') typeSelectInstance = control;

            control.on('change', () => { 
                validateForm();
                // Trigger DataTable filter when Course or Subject changes
                if (courseSelectInstance && subjectSelectInstance) {
                    filterDataTable(courseSelectInstance.getValue(), subjectSelectInstance.getValue());
                }
            });
        });

        // Toggle Collaborators Wrapper
        if (typeSelectInstance) {
            const collabWrapper = document.getElementById('collaborators-wrapper');
            typeSelectInstance.on('change', (val) => {
                collabWrapper.classList.toggle('d-none', val !== 'group');
            });
        }

        // 4. Handle Form Submission (Inject multi-page selections)
        const form = document.getElementById('edit-exam-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const checkedCheckboxes = table.$('.q-checkbox:checked');
                $(this).find('input[name="question_ids[]"]').remove();
                checkedCheckboxes.each((index, el) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'question_ids[]';
                    hiddenInput.value = el.value;
                    this.appendChild(hiddenInput);
                });
            });
        }

        // 5. Checkbox Listeners
        $('#questionTable tbody').on('change', '.q-checkbox', () => validateForm());
        $('#select-all').on('change', function() {
            const rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
            validateForm();
        });

        // Run filter on page load to show currently selected course/subject questions
        if (courseSelectInstance && subjectSelectInstance) {
            filterDataTable(courseSelectInstance.getValue(), subjectSelectInstance.getValue());
        }
        validateForm();
    });

    function filterDataTable(courseId, subjectId) {
        if (!table) return;
        
        // Loop through all rows in the DataTable
        table.rows().every(function() {
            const rowNode = this.node();
            const rowCourse = $(rowNode).attr('data-course');
            const rowSubject = $(rowNode).attr('data-subject');

            // Show row only if it matches both selections
            if (rowCourse == courseId && rowSubject == subjectId) {
                $(rowNode).show();
            } else {
                $(rowNode).hide();
            }
        });
    }

    function validateForm() {
        if (!table) return;
        const checkedCount = table.$('.q-checkbox:checked').length;
        const submitBtn = document.getElementById('submit-exam-btn');
        const hint = document.getElementById('selection-hint');
        
        submitBtn.disabled = checkedCount === 0;
        if (hint) {
            hint.innerHTML = checkedCount > 0 ? `<span class="badge bg-success">${checkedCount} Selected</span>` : '';
        }
    }

    function openEditModal(data) {
        document.getElementById('edit-q-id').value = data.id;
        questionEditor.setData(data.text);
        guideEditor.setData(data.guide || '');
        document.getElementById('edit-q-points').value = data.points;
        editModal.show();
    }

    function saveQuestionChanges() {
        const id = document.getElementById('edit-q-id').value;
        const data = {
            question_text: questionEditor.getData(),
            correct_answer_guide: guideEditor.getData(),
            default_points: document.getElementById('edit-q-points').value,
        };

        fetch(`/questions/${id}/update-ajax`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                const btn = document.querySelector(`button[onclick*="'id': ${id}"]`);
                const row = table.row($(btn).closest('tr'));
                const plainText = data.question_text.replace(/<[^>]*>/g, '').substring(0, 100) + '...';
                table.cell(row.index(), 1).data(plainText).draw();
                table.cell(row.index(), 2).data(data.default_points).draw();
                editModal.hide();
            }
        });
    }
</script>
@endsection