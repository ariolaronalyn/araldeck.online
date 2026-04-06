@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Create New Deck (Manual)</h4>
        <a href="{{ route('flashcards.index') }}" class="btn btn-outline-secondary rounded-pill">Cancel</a>
    </div>

    <form action="{{ route('flashcards.store_manual') }}" method="POST" id="manualForm">
        @csrf
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="text-primary fw-bold mb-3">1. Deck Configuration</h6>
            <div class="row g-3">
                {{-- Course Selection --}}
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Course</label>
                    <select name="course_selector" id="course_selector" class="form-select" onchange="toggleField('course')" required>
                        <option value="" selected disabled>-- Choose --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->title }}">{{ $course->title }}</option>
                        @endforeach
                        <option value="others" class="text-primary fw-bold">+ Others (New Course)</option>
                    </select>
                    <div id="new_course_wrapper" class="mt-2" style="display: none;">
                        <input type="text" name="new_course_name" id="new_course_name" class="form-control" placeholder="Enter New Course Title">
                    </div>
                </div>

                {{-- Subject Selection --}}
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Subject</label>
                    <select name="subject_selector" id="subject_selector" class="form-select" onchange="toggleField('subject')" required>
                        <option value="" selected disabled>-- Choose --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->title }}">{{ $subject->title }}</option>
                        @endforeach
                        <option value="others" class="text-primary fw-bold">+ Others (New Subject)</option>
                    </select>
                    <div id="new_subject_wrapper" class="mt-2" style="display: none;">
                        <input type="text" name="new_subject_name" id="new_subject_name" class="form-control" placeholder="Enter New Subject Title">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Deck Name</label>
                    <input type="text" name="deck_name" class="form-control" placeholder="e.g. Midterm Review" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Visibility</label>
                    <select name="is_public" class="form-select">
                        <option value="0">Private</option>
                        <option value="1">Public</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Deck Mode</label>
                    <select name="deck_type" class="form-select">
                        <option value="study">Study Mode</option>
                        <option value="quiz">Quiz Mode</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Difficulty</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy">Easy</option>
                        <option value="average" selected>Average</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>
        </div>

        <h6 class="text-primary fw-bold mb-3">2. Flashcards Content</h6>
        <div id="card-rows-container">
            {{-- Initial Row 0 --}}
            <div class="card border-0 shadow-sm p-4 mb-3 card-row position-relative border-start border-primary border-4">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Question (Images allowed)</label>
                        <textarea name="cards[0][question]" id="q_0" class="editor"></textarea>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Answer (Images allowed)</label>
                        <textarea name="cards[0][answer]" id="a_0" class="editor"></textarea>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Topic</label>
                            <input type="text" name="cards[0][topic]" class="form-control" placeholder="Topic">
                        </div>
                        <div>
                            <label class="form-label small fw-bold">Reference</label>
                            <input type="text" name="cards[0][reference]" class="form-control" placeholder="Page / Link">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-outline-primary w-100 py-3 border-dashed mb-4" onclick="addNewRow()">
            <i class="bi bi-plus-circle me-1"></i> Add Another Card
        </button>

        <div class="text-end">
            @if(!$canUpload)
                <div class="alert alert-danger d-inline-block me-3 py-2 small">
                    <i class="bi bi-exclamation-octagon"></i> {{ $uploadMessage }}
                </div>
            @endif

            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow" {{ !$canUpload ? 'disabled' : '' }}>
                Save Complete Deck
            </button>
        </div>
    </form>
</div>

{{-- CKEditor with Base64 Adapter Support --}}
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    let rowCount = 1;

    function initEditor(selector) {
        ClassicEditor.create(document.querySelector(selector), {
            toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'uploadImage'],
            ckfinder: { uploadUrl: null },
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        }).catch(error => console.error(error));
    }

    function MyCustomUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new MyBase64UploadAdapter(loader);
        };
    }

    class MyBase64UploadAdapter {
        constructor(loader) { this.loader = loader; }
        upload() {
            return this.loader.file.then(file => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve({ default: reader.result });
                reader.onerror = error => reject(error);
                reader.readAsDataURL(file);
            }));
        }
        abort() {}
    }

    function toggleField(type) {
        const selector = document.getElementById(`${type}_selector`);
        const wrapper = document.getElementById(`new_${type}_wrapper`);
        const input = document.getElementById(`new_${type}_name`);

        if (selector.value === 'others') {
            wrapper.style.display = 'block';
            input.setAttribute('required', 'required');
            input.focus();
        } else {
            wrapper.style.display = 'none';
            input.removeAttribute('required');
        }
    }

    initEditor('#q_0');
    initEditor('#a_0');

    function addNewRow() {
        const container = document.getElementById('card-rows-container');
        const newRow = document.createElement('div');
        newRow.className = 'card border-0 shadow-sm p-4 mb-3 card-row position-relative border-start border-primary border-4';
        newRow.innerHTML = `
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" onclick="this.parentElement.remove()"></button>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Question</label>
                    <textarea name="cards[${rowCount}][question]" id="q_${rowCount}"></textarea>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Answer</label>
                    <textarea name="cards[${rowCount}][answer]" id="a_${rowCount}"></textarea>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Topic</label>
                        <input type="text" name="cards[${rowCount}][topic]" class="form-control" placeholder="Topic">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Reference</label>
                        <input type="text" name="cards[${rowCount}][reference]" class="form-control" placeholder="Page / Link">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        
        initEditor(`#q_${rowCount}`);
        initEditor(`#a_${rowCount}`);
        rowCount++;
    }
</script>

<style>
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    .ck-editor__editable { min-height: 150px; }
    .card-row:hover { border-color: #0d6efd !important; }
</style>
@endsection