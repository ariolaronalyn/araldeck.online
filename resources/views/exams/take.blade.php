@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8f9fa; }
    .exam-sidebar { width: 80px; height: 100vh; background: #fff; border-right: 1px solid #dee2e6; position: fixed; top: 0; left: 0; overflow-y: auto; z-index: 1050; }
    .exam-workspace { margin-left: 80px; padding: 40px; min-height: 100vh; }
    .q-nav-circle { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-weight: bold; }
    .q-nav-circle.active { background: #0d6efd !important; color: white; border-color: #0d6efd; }
    .q-nav-circle.answered { background: #d1e7dd; border-color: #a3cfbb; }
    .q-nav-circle.flagged { border: 2px solid #dc3545 !important; color: #dc3545; }
    
    .exam-toolbar { background: #fff; border: 1px solid #dee2e6; border-radius: 50px; padding: 10px 20px; margin-bottom: 20px; }
    nav.navbar, footer { display: none !important; }
    body { padding-top: 0 !important; }

    /* Selection Toolbar */
    #text-toolbar { position: absolute; background: #212529; color: white; border-radius: 8px; padding: 5px; z-index: 3000; box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: none; }
    #text-toolbar .btn-group .btn { color: white; border: none; padding: 4px 8px; }

    /* Editor Styling */
    .ck-editor__editable_inline {
        min-height: 400px !important;
        background-color: #f8f9fa !important;
        border: none !important;
    }
    .ck-toolbar { display: none !important; } 

    #empty-state { height: 70vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
    .highlight-node { border-radius: 3px; padding: 0 2px; }
</style>

<div id="text-toolbar">
    <div class="btn-group">
        <button class="btn btn-sm" onclick="formatSelectedText('bold')"><i class="bi bi-type-bold"></i></button>
        <button class="btn btn-sm" onclick="formatSelectedText('underline')"><i class="bi bi-type-underline"></i></button>
        <div class="vr mx-1" style="background: #555;"></div>
        @foreach(auth()->user()->highlight_colors ?? ['#fff3cd', '#d4edda', '#cff4fc', '#f8d7da', '#e2e3e5'] as $color)
            <button class="btn btn-sm" onclick="formatSelectedText('highlight', '{{ $color }}')">
                <i class="bi bi-circle-fill" style="color: {{ $color }}"></i>
            </button>
        @endforeach
        <button class="btn btn-sm text-danger" onclick="formatSelectedText('clear')"><i class="bi bi-eraser"></i></button>
    </div>
</div>

<div class="exam-container">
    <div class="exam-sidebar d-flex flex-column align-items-center py-4">
        @foreach($exam->questions as $index => $q)
            <div class="q-nav-circle border rounded-circle mb-3 bg-white text-muted small q-btn" id="nav-{{ $index }}" onclick="loadQuestion({{ $index }})">
                {{ $index + 1 }}
            </div>
        @endforeach
    </div>

    <div class="exam-workspace">
        <div class="container-fluid" style="max-width: 1000px;">
            <div id="empty-state">
                <div class="card border-0 shadow-sm p-5 rounded-4">
                    <i class="bi bi-journal-check text-primary display-1 mb-3"></i>
                    <h4 class="fw-bold">Ready to Start?</h4>
                    <p class="text-muted mb-4">Click below to initialize your exam session.</p>
                    <button class="btn btn-primary btn-lg rounded-pill px-5" onclick="initializeExam()">Start Exam Now</button>
                </div>
            </div>

            <div id="exam-content-wrapper" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" id="current-q-label">Question</h5>
                    <div class="d-flex gap-3 align-items-center">
                        <span class="badge bg-danger p-2" id="exam-timer" style="font-size: 1rem;"><i class="bi bi-clock"></i> 00:00:00</span>
                        <button class="btn btn-sm btn-warning rounded-pill" onclick="pauseExam()">Pause (<span id="pause-limit">{{ $exam->pause_limit - $submission->pause_count }}</span>)</button>
                    </div>
                </div>

                <div class="exam-toolbar d-flex gap-3 align-items-center shadow-sm">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light border-0" onclick="skipQuestion()"><i class="bi bi-arrow-right-short"></i> Skip</button>
                        <button class="btn btn-sm btn-light border-0" onclick="toggleFlag()"><i class="bi bi-flag" id="flag-icon"></i> Flag</button>
                    </div>
                    <div class="vr"></div>
                    <select class="form-select form-select-sm border-0 bg-transparent w-auto" id="exam-font-select">
                        <option value="'Nunito', sans-serif">Standard</option>
                        <option value="'Georgia', serif">Classic</option>
                    </select>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light border-0" onclick="adjustExamFont(-2)"><i class="bi bi-dash"></i></button>
                        <span id="exam-font-size-label" class="small fw-bold px-2">18px</span>
                        <button class="btn btn-sm btn-light border-0" onclick="adjustExamFont(2)"><i class="bi bi-plus"></i></button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-5">
                        <div id="exam-question-text" class="mb-4" style="font-size: 18px; line-height: 1.8;"></div>
                        <hr>
                        <div class="mb-2 small fw-bold text-muted text-uppercase">Essay Answer</div>
                         <textarea id="exam-answer-box"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-5">
                    <button class="btn btn-outline-primary rounded-pill px-5" onclick="navigateQ(-1)">Previous</button>
                    <button class="btn btn-primary rounded-pill px-5" id="next-btn" onclick="navigateQ(1)">Next</button>
                    <button class="btn btn-success rounded-pill px-5 d-none" id="submit-btn" onclick="finishExam()">Submit Exam</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let questions = @json($exam->questions);
    let currentIndex = null;
    let submissionId = {{ $submission->id }};
    let timeLeft = {{ $submission->remaining_time_seconds ?? ($exam->total_time_minutes * 60) }};
    let timerInterval = null;
    let editorInstance = null;
    let currentFontSize = 18;
    let questionStartTime = null;
    let timeLogs = {};
    let isSystemChangingContent = false; // Flag to prevent autosave on question switch

    /**
     * INITIALIZE EXAM (Only runs once when button is clicked)
     */
    function initializeExam() {
        document.getElementById('empty-state').classList.add('d-none');
        document.getElementById('exam-content-wrapper').classList.remove('d-none');

        if (typeof ClassicEditor === 'undefined') {
            alert("CKEditor core not found. Please refresh.");
            return;
        }

        // Consolidated single initialization
        ClassicEditor.create(document.querySelector('#exam-answer-box'), {
            toolbar: [], 
            placeholder: 'Type your answer here...'
        }).then(editor => {
            editorInstance = editor;

            // Autosave listener with system-change check
            editor.model.document.on('change:data', () => {
                if (!isSystemChangingContent && currentIndex !== null) {
                    debouncedSave();
                }
            });
            
            // Set first question
            currentIndex = 0;
            loadQuestion(0);
            startTimer();
        }).catch(error => console.error("CKEditor Init Error:", error));
    }

    /**
     * LOAD QUESTION DATA
     */
    function loadQuestion(index) {
        if (!questions[index]) return;
        if (!editorInstance) { initializeExam(); return; }

        // Save progress for question we are leaving
        if (currentIndex !== null) {
            updateQuestionTime();
            questions[currentIndex].user_answer = editorInstance.getData();
        }

        // Lock autosave while we update editor programmatically
        isSystemChangingContent = true;

        currentIndex = index;
        const q = questions[index];

        document.getElementById('exam-question-text').innerHTML = q.question_text;
        document.getElementById('current-q-label').innerText = `Question ${index + 1}`;
        
        // This triggers the change event, but isSystemChangingContent prevents the save
        editorInstance.setData(q.user_answer || '');

        // Release lock
        setTimeout(() => { isSystemChangingContent = false; }, 150);

        document.querySelectorAll('.q-nav-circle').forEach(el => el.classList.remove('active'));
        const navItem = document.getElementById(`nav-${index}`);
        if(navItem) navItem.classList.add('active');

        const isFlagged = q.is_flagged || false;
        document.getElementById('flag-icon').className = isFlagged ? 'bi bi-flag-fill text-danger' : 'bi bi-flag';

        document.getElementById('next-btn').classList.toggle('d-none', index === questions.length - 1);
        document.getElementById('submit-btn').classList.toggle('d-none', index !== questions.length - 1);
        
        questionStartTime = Date.now();
    }

    /**
     * SELECTION TOOLBAR LOGIC
     */
    document.addEventListener('mouseup', function(e) {
        const selection = window.getSelection();
        const toolbar = document.getElementById('text-toolbar');
        
        if (selection.toString().length > 0 && editorInstance) {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();
            toolbar.style.top = `${window.scrollY + rect.top - 50}px`;
            toolbar.style.left = `${window.scrollX + rect.left + (rect.width / 2) - 60}px`;
            toolbar.style.display = 'block';
        } else {
            toolbar.style.display = 'none';
        }
    });

    function formatSelectedText(type, color = null) {
        if (type === 'bold') editorInstance.execute('bold');
        if (type === 'underline') {
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);
            const span = document.createElement('span');
            span.style.textDecoration = 'underline';
            range.surroundContents(span);
        }
        if (type === 'highlight') {
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);
            const span = document.createElement('span');
            span.className = 'highlight-node';
            span.style.backgroundColor = color;
            range.surroundContents(span);
        }
        if (type === 'clear') {
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);
            const content = range.extractContents();
            range.insertNode(document.createTextNode(content.textContent));
        }
        window.getSelection().removeAllRanges();
        debouncedSave();
    }

    /**
     * TIMER & LOGGING
     */
    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (timeLeft <= 0) { clearInterval(timerInterval); finishExam(true); return; }
            timeLeft--;
            const h = Math.floor(timeLeft / 3600).toString().padStart(2, '0');
            const m = Math.floor((timeLeft % 3600) / 60).toString().padStart(2, '0');
            const s = (timeLeft % 60).toString().padStart(2, '0');
            document.getElementById('exam-timer').innerHTML = `<i class="bi bi-clock"></i> ${h}:${m}:${s}`;
        }, 1000);
    }

    function updateQuestionTime() {
        // Guard: Prevent saving if time spent is 0 or question isn't loaded
        if (currentIndex === null || !questionStartTime || isSystemChangingContent) return;

        const now = Date.now();
        const secondsSpent = Math.floor((now - questionStartTime) / 1000);
        
        if (secondsSpent < 1) return; // Don't spam the server for sub-second changes

        const qId = questions[currentIndex].id;
        timeLogs[qId] = (timeLogs[qId] || 0) + secondsSpent;
        
        fetch("{{ route('exams.save_time_log') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ submission_id: submissionId, time_logs: timeLogs })
        }).catch(err => console.error("Time log failed:", err));
        
        questionStartTime = now;
    }

    function debouncedSave() {
        if (currentIndex === null || !editorInstance || isSystemChangingContent) return;

        clearTimeout(window.saveTimer);
        window.saveTimer = setTimeout(() => {
            const answer = editorInstance.getData();
            const currentQ = questions[currentIndex];
            
            // Safety: Ensure the question object and ID exist
            if (!currentQ || !currentQ.id) return;

            fetch("{{ route('exams.save_answer') }}", {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ 
                    submission_id: submissionId, 
                    question_id: currentQ.id, 
                    answer_text: answer 
                })
            });
        }, 2000);
    }

    /**
     * UTILITIES
     */
    function toggleFlag() {
        questions[currentIndex].is_flagged = !questions[currentIndex].is_flagged;
        const nav = document.getElementById(`nav-${currentIndex}`);
        nav.classList.toggle('flagged');
    }

    function skipQuestion() {
        if (currentIndex < questions.length - 1) navigateQ(1);
    }

    function adjustExamFont(amount) {
        currentFontSize = Math.max(12, Math.min(currentFontSize + amount, 36));
        document.getElementById('exam-font-size-label').innerText = currentFontSize + 'px';
        document.getElementById('exam-question-text').style.fontSize = currentFontSize + 'px';
        editorInstance.editing.view.change(writer => {
            writer.setStyle('font-size', currentFontSize + 'px', editorInstance.editing.view.document.getRoot());
        });
    }

    document.getElementById('exam-font-select').onchange = function() {
        const font = this.value;
        document.getElementById('exam-question-text').style.fontFamily = font;
        editorInstance.editing.view.change(writer => {
            writer.setStyle('font-family', font, editorInstance.editing.view.document.getRoot());
        });
    };

    function navigateQ(step) {
        const nextIndex = currentIndex + step;
        if (nextIndex >= 0 && nextIndex < questions.length) loadQuestion(nextIndex);
    }

    function finishExam(auto = false) {
        updateQuestionTime();
        if (!auto && !confirm("Submit your exam?")) return;
        fetch("{{ route('exams.submit') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ submission_id: submissionId })
        }).then(() => window.location.href = "{{ route('exams.index') }}");
    }

    function pauseExam() {
        fetch("{{ route('exams.pause') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ submission_id: submissionId })
        }).then(res => res.json()).then(data => {
            if (data.allowed) { alert("Exam Paused."); } else { alert(data.message); }
        });
    }
</script>
@endsection