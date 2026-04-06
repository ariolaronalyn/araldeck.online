<!-- @extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Add Multiple Flashcards</h2>
        <a href="{{ route('flashcards.index') }}" class="btn btn-outline-secondary rounded-pill">Back to Decks</a>
    </div>

    <form action="{{ route('flashcards.store_bulk') }}" method="POST" id="bulkForm">
        @csrf
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Subject</label>
                    <select name="subject_id" class="form-select" required>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Deck Name</label>
                    <input type="text" name="deck_name" class="form-control" placeholder="e.g. Midterm Review" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Deck Type</label>
                    <select name="deck_type" class="form-select">
                        <option value="Multiple Choice">Multiple Choice</option>
                        <option value="Identification">Identification</option>
                    </select>
                </div>
            </div>

            <div id="card-rows">
                <div class="card-row border rounded p-3 mb-3 bg-light position-relative" data-row="0">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small fw-bold">Question</label>
                            <textarea name="cards[0][question]" class="editor-q"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Answer</label>
                            <textarea name="cards[0][answer]" class="editor-a"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary border-dashed w-100 py-3 mt-2" onclick="addRow()">
                <i class="bi bi-plus-circle"></i> Add Another Card
            </button>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">Save All Flashcards</button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

<script>
    let rowCount = 1;
    const editors = {};

    function initEditor(selector) {
        ClassicEditor.create(document.querySelector(selector), {
            toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']
        }).then(editor => {
            editors[selector] = editor;
        });
    }

    // Initialize first row
    initEditor('.editor-q');
    initEditor('.editor-a');

    function addRow() {
        const container = document.getElementById('card-rows');
        const newRow = document.createElement('div');
        newRow.className = 'card-row border rounded p-3 mb-3 bg-light position-relative';
        newRow.innerHTML = `
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
            <div class="row">
                <div class="col-md-6">
                    <label class="small fw-bold">Question</label>
                    <textarea name="cards[${rowCount}][question]" id="q_${rowCount}"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Answer</label>
                    <textarea name="cards[${rowCount}][answer]" id="a_${rowCount}"></textarea>
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
    .border-dashed { border-style: dashed !important; }
    .ck-editor__editable { min-height: 100px; }
</style>
@endsection -->