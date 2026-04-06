@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Automated PDF Extraction</div>
                <div class="card-body p-4">
                    <form action="{{ route('pdf.extract') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Select Course</label>
                                <select name="course" id="ext_course_select" class="form-select" required>
                                    <option value="">-- Choose --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->title }}">{{ $course->title }}</option>
                                    @endforeach
                                    <option value="other">+ New Course</option>
                                </select>
                                <input type="text" name="new_course" id="ext_new_course" class="form-control mt-2 d-none" placeholder="Enter Course Name">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Select Subject</label>
                                <select name="subject" id="ext_subject_select" class="form-select" required>
                                    <option value="">-- Choose --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->title }}">{{ $subject->title }}</option>
                                    @endforeach
                                    <option value="other">+ New Subject</option>
                                </select>
                                <input type="text" name="new_subject" id="ext_new_subject" class="form-control mt-2 d-none" placeholder="Enter Subject Name">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Row Limit</label>
                                <input type="number" name="row_limit" class="form-control" value="100" min="1">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload PDF File</label>
                            <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                        </div>

                        <input type="hidden" name="pdf_type" value="compendious">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Start Automated Extraction
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle logic for "Other" inputs
document.getElementById('ext_course_select').addEventListener('change', function() {
    document.getElementById('ext_new_course').classList.toggle('d-none', this.value !== 'other');
});
document.getElementById('ext_subject_select').addEventListener('change', function() {
    document.getElementById('ext_new_subject').classList.toggle('d-none', this.value !== 'other');
});
</script>
@endsection
