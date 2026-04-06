@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Flashcard Management (CSV/Excel)</h5>
                </div>

                <div class="card-body p-4">
                    <div class="bg-light p-4 rounded mb-4 border">
                        <h6 class="text-primary mb-3"><strong>Step 1: Prepare your Template</strong></h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Select Course</label>
                                <select id="temp_course_select" class="form-select">
                                    <option value="">-- Choose --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->title }}">{{ $course->title }}</option>
                                    @endforeach
                                    <option value="other" class="text-primary">+ Others (New)</option>
                                </select>
                                <input type="text" id="temp_new_course" class="form-control mt-2 d-none" placeholder="Enter New Course">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Select Subject</label>
                                <select id="temp_subject_select" class="form-select">
                                    <option value="">-- Choose --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->title }}">{{ $subject->title }}</option>
                                    @endforeach
                                    <option value="other" class="text-primary">+ Others (New)</option>
                                </select>
                                <input type="text" id="temp_new_subject" class="form-control mt-2 d-none" placeholder="Enter New Subject">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Rows</label>
                                <input type="number" id="temp_rows" class="form-control" value="10" min="1">
                            </div>
                            
                            <div class="col-md-4">
                                <button type="button" onclick="downloadCustomCSV()" class="btn btn-success w-100">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Get Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-primary mb-3"><strong>Step 2: Upload & Configure Deck</strong></h6>
                    <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Deck Name</label>
                                <select name="existing_deck" id="deck_selector" class="form-select" onchange="toggleNewDeckField()" required>
                                    <option value="" selected disabled>-- Select Existing or Create New --</option>
                                    @foreach($userDecks as $deck)
                                        <option value="{{ $deck->id }}">{{ $deck->name }}</option>
                                    @endforeach
                                    <option value="others" class="text-primary fw-bold">+ Create New Deck</option>
                                </select>
                                <div id="new_deck_wrapper" class="mt-2" style="display: none;">
                                    <input type="text" name="new_deck_name" id="new_deck_name" class="form-control" placeholder="Enter a unique name for your new deck">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Visibility</label>
                                <select name="is_public" id="visibility_selector" class="form-select" onchange="toggleCollabField()">
                                    <option value="0">Private</option>
                                    <option value="1">Public</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Deck Mode</label>
                                <select name="deck_type" class="form-select" required>
                                    <option value="study">Study Mode (Flashcard)</option>
                                    <option value="quiz">Quiz Mode (Timed)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Difficulty</label>
                                <select name="difficulty" class="form-select" required>
                                    <option value="easy">Easy (30s Timer)</option>
                                    <option value="average" selected>Average (20s Timer)</option>
                                    <option value="hard">Hard (10s Timer)</option>
                                </select>
                            </div>

                            <div class="col-md-8" id="collab_wrapper" style="display: none;">
                                <label class="form-label fw-bold">Invite Collaborators (Emails)</label>
                                <input type="text" name="collaborator_emails" class="form-control" placeholder="email1@test.com, email2@test.com">
                                <small class="text-muted">Separate multiple emails with commas.</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Completed Excel File (.xlsx)</label>
                                <input type="file" name="csv_file" class="form-control" accept=".xlsx" required>
                                <small class="text-muted d-block mt-1">Course and Subject will be detected automatically from Row 6 of the file.</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="text-end">
                                @if(!$canUpload)
                                    <div class="alert alert-danger d-inline-block me-3 py-2 small">
                                        <i class="bi bi-exclamation-octagon"></i> {{ $uploadMessage }}
                                    </div>
                                @endif
 
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow-sm"  {{ !$canUpload ? 'disabled' : '' }}>
                                Upload Deck
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Logic for Step 1: Handle Template Field Toggles
document.getElementById('temp_course_select').addEventListener('change', function() {
    const input = document.getElementById('temp_new_course');
    input.classList.toggle('d-none', this.value !== 'other');
    if(this.value === 'other') input.focus();
});

document.getElementById('temp_subject_select').addEventListener('change', function() {
    const input = document.getElementById('temp_new_subject');
    input.classList.toggle('d-none', this.value !== 'other');
    if(this.value === 'other') input.focus();
});

function downloadCustomCSV() {
    let course = document.getElementById('temp_course_select').value;
    if(course === 'other') course = document.getElementById('temp_new_course').value;
    
    let subject = document.getElementById('temp_subject_select').value;
    if(subject === 'other') subject = document.getElementById('temp_new_subject').value;
    
    const rows = document.getElementById('temp_rows').value || 10;

    if(!course || !subject) {
        alert('Please select or enter both a Course and a Subject.');
        return;
    }

    window.location.href = `/download-template?course=${encodeURIComponent(course)}&subject=${encodeURIComponent(subject)}&rows=${rows}`;
}
 
// Logic for Step 2: Form Field Toggles
function toggleNewDeckField() {
    const selector = document.getElementById('deck_selector');
    const wrapper = document.getElementById('new_deck_wrapper');
    const input = document.getElementById('new_deck_name');

    if (selector.value === 'others') {
        wrapper.style.display = 'block';
        input.setAttribute('required', 'required');
        input.focus();
    } else {
        wrapper.style.display = 'none';
        input.removeAttribute('required');
        input.value = '';
    }
} 

function toggleCollabField() {
    const selector = document.getElementById('visibility_selector');
    const wrapper = document.getElementById('collab_wrapper');
    wrapper.style.display = (selector.value === '1') ? 'block' : 'none';
}
</script>
@endsection