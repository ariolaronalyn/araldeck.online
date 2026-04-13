@extends('layouts.app')

@section('content')
<div class="container py-4">
    <form action="{{ route('questions.store_bulk') }}" method="POST">
        @csrf
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Create Questions</h4>
            <div class="d-flex gap-3">
                <select name="course_id" class="form-select border-0 shadow-sm" required style="width: 200px;">
                    <option value="">Select Course</option>
                    @foreach($courses as $course) <option value="{{ $course->id }}">{{ $course->title }}</option> @endforeach
                </select>
                <select name="subject_id" class="form-select border-0 shadow-sm" required style="width: 200px;">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject) <option value="{{ $subject->id }}">{{ $subject->title }}</option> @endforeach
                </select>
            </div>
        </div>

        {{-- Question Cards Container --}}
        <div id="cards-container">
            {{-- Initial Card --}}
            <div class="card border-0 shadow-sm mb-4 question-card">
                <div class="card-body p-4 border-start border-primary border-4 rounded-start">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small fw-bold mb-2">Question (Images allowed)</label>
                            <textarea name="questions[0][text]" class="form-control editor" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold mb-2">Answer / Grading Guide</label>
                            <textarea name="questions[0][answer]" class="form-control editor" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <div style="width: 100px;">
                            <label class="extra-small text-muted">Points</label>
                            <input type="number" name="questions[0][points]" class="form-control form-control-sm" value="5">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Another Card Button --}}
        <div class="card border-primary border-dashed mb-4" style="border: 2px dashed #0d6efd; cursor: pointer;" onclick="addAnotherCard()">
            <div class="card-body text-center py-3 text-primary fw-bold">
                <i class="bi bi-plus-circle-fill"></i> Add Another Card
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('questions.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Save All to Question Bank</button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    let cardCount = 1;

    function initEditor(selector) {
        ClassicEditor.create(document.querySelector(selector)).catch(err => console.error(err));
    }

    // Initialize the first one
    document.querySelectorAll('.editor').forEach((el, index) => {
        el.id = 'editor-' + index;
        initEditor('#' + el.id);
    });

    function addAnotherCard() {
        const container = document.getElementById('cards-container');
        const newCard = document.createElement('div');
        newCard.className = 'card border-0 shadow-sm mb-4 question-card';
        
        const qId = `q-text-${cardCount}`;
        const aId = `a-text-${cardCount}`;

        newCard.innerHTML = `
            <div class="card-body p-4 border-start border-primary border-4 rounded-start">
                <div class="row">
                    <div class="col-md-6">
                        <label class="small fw-bold mb-2">Question</label>
                        <textarea id="${qId}" name="questions[${cardCount}][text]" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-2">Answer</label>
                        <textarea id="${aId}" name="questions[${cardCount}][answer]" class="form-control"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('.question-card').remove()">
                        <i class="bi bi-trash"></i> Remove Card
                    </button>
                    <div style="width: 100px;">
                        <input type="number" name="questions[${cardCount}][points]" class="form-control form-control-sm" value="5">
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(newCard);
        initEditor(`#${qId}`);
        initEditor(`#${aId}`);
        cardCount++;
    }
</script>

<style>
    .extra-small { font-size: 0.7rem; }
    .border-dashed { background-color: rgba(13, 110, 253, 0.05); transition: 0.3s; }
    .border-dashed:hover { background-color: rgba(13, 110, 253, 0.1); }
    .ck-editor__editable_inline { min-height: 150px; }
</style>
@endsection