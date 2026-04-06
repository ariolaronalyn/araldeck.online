@extends('layouts.app')
{{-- Ensure Bootstrap JS is loaded before our custom scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Manage Deck: <span class="text-primary">{{ $deck->name }}</span></h4>
            <small class="text-muted">Edit your content with rich text formatting below.</small>
        </div>
        <a href="{{ route('flashcards.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Decks
        </a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editDeckModal">
            <i class="bi bi-gear-fill"></i> Edit Deck Settings
        </button>
    </div>

    <div class="card border-0 shadow-sm p-3">
        <div class="table-responsive">
            <table id="cardsTable" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Topic</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Difficulty</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $card)
                    <tr>
                        <td>  {{ $card->topic }}   </td>
                        {{-- Use strip_tags because content now contains HTML --}}
                        <td>{!! Str::limit(strip_tags($card->question), 60) !!}</td>
                        <td>{!! Str::limit(strip_tags($card->answer), 60) !!}</td>
                        <td>
                            @php
                                // Map difficulties to Bootstrap colors
                                $difficultyColor = match(strtolower($card->difficulty)) {
                                    'easy'    => 'success',  // Green
                                    'average' => 'warning',  // Yellow/Orange
                                    'hard'    => 'danger',   // Red
                                    default   => 'secondary' // Grey
                                };
                            @endphp
                            
                            <span class="badge bg-{{ $difficultyColor }}-subtle text-{{ $difficultyColor }} border border-{{ $difficultyColor }} px-3 rounded-pill">
                                {{ ucfirst($card->difficulty) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-primary" 
                                onclick="openEditModal(this)"
                                data-id="{{ $card->id }}"
                                data-question="{{ e($card->question) }}" {{-- 'e' helper escapes HTML --}}
                                data-answer="{{ e($card->answer) }}"
                                data-reference="{{ $card->reference }}"
                                data-topic="{{ $card->topic }}"
                                data-difficulty="{{ $card->difficulty }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> {{-- Changed to modal-lg for better editor space --}}
        <form action="{{ route('flashcards.update') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Edit Flashcard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Question</label>
                    <textarea name="question" id="editor_question" class="form-control"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Answer</label>
                    <textarea name="answer" id="editor_answer" class="form-control"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-bold">Topic</label>
                        <input type="text" name="topic" id="edit_topic" class="form-control" placeholder="e.g. Algebra">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label small fw-bold">Reference (Optional)</label>
                        <input type="text" name="reference" id="edit_reference" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-bold">Difficulty</label>
                        <select name="difficulty" id="edit_difficulty" class="form-select">
                            <option value="easy">Easy</option>
                            <option value="average">Average</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editDeckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('decks.update_settings', $deck->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Edit Deck Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Hidden fields --}}
                <input type="hidden" name="deck_id" value="{{ $deck->id }}">
                <input type="hidden" name="old_deck_name" value="{{ $deck->name }}">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Deck Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $deck->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Topic (Global)</label>
                    <input type="text" name="topic" class="form-control" value="{{ $deck->topic }}" placeholder="Update topic for all cards">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Visibility</label>
                    <select name="is_public" class="form-select">
                        <option value="0" {{ !$deck->is_public ? 'selected' : '' }}>Private</option>
                        <option value="1" {{ $deck->is_public ? 'selected' : '' }}>Public</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Deck Mode</label>
                        <select name="deck_type" class="form-select">
                            <option value="study" {{ $deck->type == 'study' ? 'selected' : '' }}>Study Mode</option>
                            <option value="quiz" {{ $deck->type == 'quiz' ? 'selected' : '' }}>Quiz Mode</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Default Difficulty</label>
                        <select name="difficulty" class="form-select">
                            <option value="easy" {{ $deck->difficulty == 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="average" {{ $deck->difficulty == 'average' ? 'selected' : '' }}>Average</option>
                            <option value="hard" {{ $deck->difficulty == 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                </div>
                
                <div class="alert alert-info py-2 mb-0 border-0 shadow-sm">
                    <small><i class="bi bi-info-circle-fill"></i> Updating these settings will apply changes to all <strong>{{ $cards->count() }}</strong> cards in this deck.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Update All Cards</button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script> -->

<script>
let qEditor, aEditor;
let questionEditor, answerEditor;

    $(document).ready(function() {
        $('#cardsTable').DataTable({ 
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                "search": "Search Flashcards:",
                "paginate": {
                    "next": '<i class="bi bi-chevron-right"></i>',
                    "previous": '<i class="bi bi-chevron-left"></i>'
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": 4 } // Disable sorting on Action column
            ]
        });
        // Initialize DataTable
        // $('#manageCardsTable').DataTable({ "pageLength": 10 });

        // Configuration with Base64 Upload Support
        const editorConfig = {
            ckfinder: {
                // This is the magic part that allows image pasting/uploading 
                // without needing a backend upload controller.
                uploadUrl: null 
            },
            // Add SimpleUpload logic if using a custom adapter, 
            // but for Base64 we just need to ensure the build supports it.
            // In the Standard Classic Build, Base64 is usually a separate plugin.
            // Since we are using the CDN, we will use the "Base64UploadAdapter"
        };

        // Initialize Question Editor
        ClassicEditor.create(document.querySelector('#editor_question'), {
            // This allows images to be saved as strings in your DB
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        })
        .then(editor => { qEditor = editor; })
        .catch(err => console.error(err));

        // Initialize Answer Editor
        ClassicEditor.create(document.querySelector('#editor_answer'), {
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        })
        .then(editor => { aEditor = editor; })
        .catch(err => console.error(err));
    });
// The Custom Adapter that converts images to Base64
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

    function openEditModal(btn) {
        const data = btn.dataset;
        
        // Populate standard inputs
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_reference').value = data.reference;
        document.getElementById('edit_difficulty').value = data.difficulty;
        document.getElementById('edit_topic').value = data.topic;

        // Populate CKEditors with unescaped HTML
        // We use a temporary textarea to decode the escaped HTML from the data-attribute
        const decodeHTML = (html) => {
            const txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        };

        if (qEditor) {
            qEditor.setData(decodeHTML(data.question));
        }
        if (aEditor) {
            aEditor.setData(decodeHTML(data.answer));
        }

        // Show Modal
        const modalEl = document.getElementById('editCardModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

<style>
    /* Ensure the editor looks good inside the modal */
    .ck-editor__editable_inline {
        min-height: 150px;
    }
</style>
@endsection