    @extends('layouts.app')

    {{-- Ensure Bootstrap JS is loaded before our custom scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @section('content')

    <div class="container">
        <div id="alert-container" class="mb-4">
            @if(!$hasActiveSubscription)
                <div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-info-circle-fill me-2"></i> 
                        You are currently on the <strong>Free Plan</strong>. 
                        Limit: 1 Upload/Day (Max 10 cards). Public Gallery and Collaboration are locked.
                    </span>
                    <a href="{{ route('settings.index') }}" class="btn btn-info btn-sm text-white rounded-pill px-3">Upgrade Now</a>
                </div>
            @endif

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
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>{{ request('view') == 'public' ? 'Public Decks' : 'My Decks' }}</h4>
            <div>
                @if($hasActiveSubscription)
                    @if(request('view') == 'public')
                        <a href="{{ route('flashcards.index') }}" class="btn btn-outline-primary me-2">Back to My Decks</a>
                    @else
                        <a href="{{ route('flashcards.index', ['view' => 'public']) }}" class="btn btn-info me-2 text-white">Browse Public Decks</a>
                    @endif
                @else
                    <button class="btn btn-secondary me-2" onclick="alert('Upgrade to Pro to browse the Public Gallery!')" disabled>
                        <i class="bi bi-lock-fill"></i> Browse Public Decks
                    </button>
                @endif
                <a href="{{ route('csv.form') }}" class="btn btn-primary">Upload Deck</a>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('flashcards.index') }}" method="GET" class="row g-3" id="filterForm">
                    <input type="hidden" name="view" value="{{ request('view') }}">
                    <div class="col-md-2">
                        <label class="small text-muted">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search decks..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Course</label>
                        <select name="course_id" id="course_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Subject</label>
                        <select name="subject_id" id="subject_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">Class</label>
                        <select name="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            @foreach($userClasses as $cls)
                                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">Topic</label>
                        <select name="topic" class="form-select" onchange="this.form.submit()">
                            <option value="">All Topics</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic }}" {{ request('topic') == $topic ? 'selected' : '' }}>
                                    {{ $topic }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-secondary">Filter</button>
                            <a href="{{ route('flashcards.index', ['view' => request('view')]) }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse($decks as $deck)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-soft-primary text-primary border border-primary px-2 py-1">
                                    {{ $deck->subject->course->title }}
                                </span>

                                @if($deck->topic)
                                    <span class="badge bg-info-subtle text-info border rounded-pill px-2 py-1">
                                    <i class="bi bi-tag-fill"></i> {{ $deck->topic }}
                                    </span> 
                                @endif
                                



                            
                                <div>
                                    @if($deck->has_progress)
                                        <span class="badge bg-warning text-dark rounded-pill me-1">In Progress</span>
                                    @endif
                                    
                                </div>
                                
                                <div>
                                    <span class="badge {{ $deck->type === 'quiz' ? 'bg-danger-subtle text-danger border-danger' : 'bg-success-subtle text-success border-success' }} border small me-1">
                                        {{ $deck->type === 'quiz' ? 'Timed Quiz' : 'Study Cards' }}
                                    </span>
                                    <span class="badge bg-light text-dark border small">
                                        {{ $deck->card_count }} Cards
                                    </span>
                                    
                                </div>
                            </div>
                                <!-- {{ $deck->name }} -->
                            <h5 class="fw-bold mb-1">
                                {{ $deck->name }}
                                @if($deck->class_name)
                                    <span class="badge bg-secondary text-white ms-1" style="font-size: 0.65rem;"><i class="bi bi-mortarboard-fill"></i> {{ $deck->class_name }}</span>
                                @endif
                            </h5>
                            <!-- <p class="text-muted small mb-2">
                                <i class="bi bi-book me-1"></i> {{ $deck->subject->title ?? 'General Subject' }}
                            </p>
                            <p class="text-muted small mb-2"><i class="bi bi-book me-1"></i> {{ $deck->subject->title }}</p> -->

                            @if($deck->end_at && \Carbon\Carbon::parse($deck->end_at)->isFuture())
                                <div class="alert alert-warning py-1 px-3 rounded-3 border-0 mb-3 small">
                                    <i class="bi bi-clock-history"></i> Deadline: {{ \Carbon\Carbon::parse($deck->end_at)->format('M d, h:i A') }}
                                </div>
                            @endif

                            
                            <!-- <h5 class="card-title fw-bold mb-1">
                                {{ $deck->deck_name ?? 'Untitled Deck' }}
                                @if((int)$deck->user_id !== (int)auth()->id())
                                    <span class="badge bg-info text-white ms-1" style="font-size: 0.6rem;">Shared</span>
                                @endif
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                <i class="bi bi-book me-1"></i>{{ $deck->subject->title }}
                            </p> -->

                            {{-- ACTION BUTTONS SECTION --}}
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            @php
                                    $isCollaborator = $deck->collaborators->where('invited_user_id', auth()->id())
                                                                        ->where('status', 'accepted')
                                                                        ->isNotEmpty();
                                @endphp

                                {{-- LEFT SIDE: STUDY/ANSWER --}} 
                                @if(((int)$deck->user_id === (int)auth()->id() || auth()->user()->role === 'student' || $isCollaborator)  && request('view') != 'public' )
                                    <div class="d-flex gap-1">
                                        <button class="btn {{ $deck->has_progress ? 'btn-warning' : 'btn-primary' }} btn-sm px-3 rounded-pill shadow-sm" 
                                            onclick="startStudyDeck('{{ $deck->id }}', '{{ $deck->type }}')">
                                            <i class="bi {{ $deck->has_progress ? 'bi-arrow-right-circle' : 'bi-play-fill' }}"></i> 
                                            {{ $deck->has_progress ? 'Resume' : ($deck->type === 'quiz' ? 'Answer' : 'Study') }}
                                        </button>

                                        {{-- NEW: Study Tag Button (Only for Study Mode) --}}
                                        @if($deck->type === 'study')
                                            <button class="btn btn-outline-info btn-sm px-3 rounded-pill shadow-sm" 
                                                onclick="openTagFilterModal('{{ $deck->id }}')">
                                                <i class="bi bi-filter-square"></i> Tag
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <div></div>
                                @endif

                                {{-- RIGHT SIDE: ICONS --}}
                                <div class="d-flex gap-1 align-items-center">
                                    @if($deck->collaborators->count() > 0)
                                        <button type="button" class="btn btn-sm btn-outline-info border-0 rounded-circle" 
                                                data-bs-toggle="tooltip" data-bs-html="true"
                                                title="<strong>Shared by:</strong><br>{{ $deck->user->email }}<br><br><strong>Collaborators:</strong><br>{!! $deck->collaborators->map(fn($c) => '• '.$c->invitedUser->email)->implode('<br>') !!}">
                                            <i class="bi bi-share-fill"></i>
                                        </button>
                                    @endif

                                    @if((int)$deck->user_id === (int)auth()->id())
                                        {{-- Collaboration --}}
                                        <button class="btn btn-sm btn-outline-info border-0 rounded-circle" onclick="{{ $hasActiveSubscription ? "openInviteModal('$deck->id')" : "alert('Pro feature!')" }}">
                                            <i class="bi bi-people"></i>
                                        </button>

                                        {{-- Manage --}}
                                        <a href="{{ route('decks.manage', $deck->id) }}" class="btn btn-sm btn-outline-secondary border-0 rounded-circle">
                                            <i class="bi bi-pencil-square"></i> 
                                        </a>

                                        {{-- Delete - FIXED POSITION --}}
                                        <form action="{{ route('decks.delete', $deck->id) }}" method="POST" class="d-inline-block m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="return confirm('Delete this deck?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Clone for Public Gallery --}}
                                    @if((int)$deck->user_id !== (int)auth()->id() && request('view') == 'public')
                                        <form action="{{ route('decks.clone') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="deck_id" value="{{ $deck->id }}">
                                            <button type="submit" class="btn btn-outline-success btn-sm rounded-pill">
                                                <i class="bi bi-plus-lg"></i> Add
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-folder-x display-1 text-muted"></i>
                    <p class="mt-3">No decks found.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            @if ($decks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $decks->appends(request()->query())->links() }}
            @endif
        </div>
    </div>

    {{-- MODAL: STUDY/QUIZ --}}
    <div class="modal fade" id="studyModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold" id="modalDeckTitle">Flashcards</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeStudyModal()"></button>
                </div>


                <div id="live-tag-container" class="mb-3 p-3 bg-light rounded-4 shadow-sm border">
                    <small class="fw-bold text-muted d-block mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="bi bi-tag-fill me-1"></i> Quick Tag Card:
                    </small>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(auth()->user()->custom_labels ?? ['Definition', 'Memorize', 'Cases'] as $label)
                            <div class="custom-tag-check">
                                <input type="checkbox" 
                                    class="btn-check tag-toggle-input" 
                                    id="tag_{{ $loop->index }}" 
                                    value="{{ $label }}" 
                                    onchange="liveUpdateLabel(this)">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3" for="tag_{{ $loop->index }}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3"> 
                        <div class="dropdown">
                            <button class="badge bg-secondary px-3 py-2 border-0 dropdown-toggle" type="button" id="card-counter" data-bs-toggle="dropdown" aria-expanded="false">
                                1 / 1
                            </button>
                            <ul class="dropdown-menu shadow border-0" id="card-jump-list" style="max-height: 300px; overflow-y: auto;">
                                </ul>
                        </div>
                        <div id="quiz-timer-wrapper" style="display: none;">
                            <span class="badge bg-danger px-3 py-2">
                                <i class="bi bi-clock-history me-1"></i> <span id="timer-display">0</span>s
                            </span>
                        </div>
                    </div>

                    <div id="study-mode-ui"> 

                        <!-- <div id="study-topic" class="badge bg-secondary justify-content-center text-center  text-white mb-2" style="display: none;"></div> -->
                        <div class="d-flex justify-content-center">
                            <div id="study-topic" class="badge bg-secondary text-white mb-4" style="display: none;"> </div>
                        </div>
                                
                        <div id="format-success-msg" class="text-center small fw-bold text-success mb-2" style="display: none; height: 20px;">
                            <i class="bi bi-check-circle-fill"></i> Changes saved
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3 p-2 bg-white rounded-pill shadow-sm border mx-auto" style="max-width: fit-content;">
                            <select class="form-select form-select-sm border-0 bg-light rounded-pill" id="font-family-select" style="width: 130px;">
                                <option value="'Georgia', serif" selected>Classic</option>
                                <option value="'Inter', sans-serif">Standard</option>
                                <option value="'Courier New', monospace">Monospace</option>
                            </select>
                            
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle mx-1" onclick="adjustFontSize(-2)"><i class="bi bi-dash"></i></button>
                                <span class="align-self-center small fw-bold" id="font-size-label">18px</span>
                                <button class="btn btn-sm btn-light rounded-circle mx-1" onclick="adjustFontSize(2)"><i class="bi bi-plus"></i></button>
                            </div>

                            <div class="vr mx-2"></div>

                            <div class="btn-group">
                                <button class="btn btn-sm btn-light" onclick="setCardAlignment('left')"><i class="bi bi-text-left"></i></button>
                                <button class="btn btn-sm btn-light" onclick="setCardAlignment('center')"><i class="bi bi-text-center"></i></button>
                                <button class="btn btn-sm btn-light" onclick="setCardAlignment('justify')"><i class="bi bi-justify"></i></button>
                            </div>
                        </div>

                    <div id="text-toolbar" class="position-absolute bg-dark text-white rounded shadow p-1 d-none" style="z-index: 2000;">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-dark border-0" onclick="formatSelectedText('bold')"><i class="bi bi-type-bold"></i></button>
                                <button class="btn btn-sm btn-dark border-0" onclick="formatSelectedText('underline')"><i class="bi bi-type-underline"></i></button>
                                
                            {{-- Dynamic Highlighter Colors from Settings --}}
                                @foreach(auth()->user()->highlight_colors ?? ['#fff3cd', '#d4edda', '#cff4fc', '#f8d7da', '#e2e3e5'] as $color)
                                    <button class="btn btn-sm btn-dark border-0" onclick="formatSelectedText('highlight', '{{ $color }}')">
                                        <i class="bi bi-circle-fill" style="color: {{ $color }}"></i>
                                    </button>
                                @endforeach
                                
                                <button class="btn btn-sm btn-dark border-0 text-danger" onclick="formatSelectedText('clear')"><i class="bi bi-eraser"></i></button>
                                <!-- <button class="btn btn-sm btn-primary border-0 ms-1 px-3" onclick="saveFormattedCard()"><i class="bi bi-check-square-fill"></i></button> -->
                            </div>
                        </div>
                        <!-- <div class="flashcard-container" onclick="this.classList.toggle('flipped')"> -->
                        <div class="flashcard-container" >
                            <div class="flashcard-inner">
                                <div class="flashcard-front bg-white d-flex flex-column align-items-center justify-content-center p-5 text-center shadow-sm">
                                    <small class="text-muted fw-bold mb-3 tracking-widest">QUESTION</small>
                                    <div id="study-question" class="fw-normal fs-5 scrollable-content"></div>
                                </div>
                                <div class="flashcard-back d-flex flex-column p-5 text-center shadow-sm">
                                    <div class="question-section mb-4 text-center">
                                        <small class="text-muted fw-bold mb-2 tracking-widest">QUESTION</small>
                                        <div id="study-question-back" class="fw-normal fs-5 scrollable-content"></div>
                                    </div>
                                    <div class="answer-section">
                                        <small class="text-light fw-bold mb-2 tracking-widest">ANSWER</small>
                                        <div id="study-answer" class="fw-normal fs-5 scrollable-content"></div>
                                    </div>
                                    <p id="study-reference" class="mt-3 small opacity-75"></p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-primary rounded-pill px-4" onclick="prevCard()"><i class="bi bi-chevron-left"></i> Previous</button>
                            <button id="shuffle-btn" class="btn btn-outline-warning rounded-pill px-4 fw-bold" onclick="shuffleDeck()">
                                <i class="bi bi-shuffle"></i> Shuffle
                            </button>
                            <button id="flip-btn" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" onclick="toggleFlip()">
                                <i class="bi bi-arrow-repeat"></i> Flip
                            </button>
                            <button class="btn btn-outline-primary rounded-pill px-4" onclick="nextCard()">Next <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                    

                    <div id="quiz-mode-ui" style="display: none;">
                        <div class="bg-light p-4 rounded-4 mb-3 text-center min-vh-25">
                            <div id="quiz-topic" class="badge bg-info text-white mb-2" style="display: none;"></div>
                            <small class="text-danger fw-bold d-block mb-2">IDENTIFY THE ANSWER</small>
                            <h4 id="quiz-question" class="mb-0"></h4>
                        </div>
                        
                        <div class="form-group mb-3">
                            <input type="text" id="quiz-input" class="form-control form-control-lg text-center" placeholder="Type your answer here..." autocomplete="off">
                        </div>
                        
                        <div id="quiz-feedback" class="text-center fw-bold mb-3 h5" style="min-height: 1.5em;"></div>
                        
                        <div class="d-flex gap-2">
                            <button id="quiz-skip-btn" class="btn btn-outline-secondary btn-lg w-50 rounded-pill" onclick="skipQuestion()">
                                Skip
                            </button>
                            <button id="quiz-submit-btn" class="btn btn-primary btn-lg w-50 rounded-pill" onclick="submitQuizAnswer()">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: INVITE COLLABORATOR --}}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-light">
                    <h6 class="modal-title fw-bold">Invite Collaborators</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('decks.invite') }}" method="POST" id="collabForm">
                    @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" name="deck_id" id="invite_deck_id">
                        
                        {{-- Existing Collaborators Section --}}
                        <div id="existing_collabs_section" class="mb-4" style="display: none;">
                            <label class="form-label small fw-bold text-muted">Current Collaborators</label>
                            <div id="collab_list" class="list-group list-group-flush border rounded"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Add New Collaborator (Email)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="collab_email_input" name="emails" class="form-control border-start-0" placeholder="enter email address..." autocomplete="off">
                            </div>
                            <div id="collab_feedback" class="small mt-1" style="min-height: 20px;"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" id="send_invite_btn" class="btn btn-primary w-100 rounded-pill" disabled>
                            Send Invite
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- MODAL: SELECT TAGS FOR STUDY --}}
    <div class="modal fade" id="tagFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-light">
                    <h6 class="modal-title fw-bold">Study by Tag</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Select one or more tags to filter your study session:</p>
                    <div id="tag-selection-list">
                        @foreach(auth()->user()->custom_labels ?? ['Definition', 'Memorize', 'Cases'] as $label)
                            <div class="form-check mb-2">
                                <input class="form-check-input study-tag-checkbox" type="checkbox" value="{{ $label }}" id="study_tag_{{ $loop->index }}">
                                <label class="form-check-label" for="study_tag_{{ $loop->index }}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="startTaggedStudy()">
                        Start Tagged Session
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    :root {
        --card-bg-color: {{ auth()->user()->card_color ?? '#CBDCEB' }};
    }
    .bg-soft-primary { background-color: #e7f1ff; }
    .hover-shadow:hover { transform: translateY(-5px); transition: 0.3s; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition { transition: 0.3s ease; }
    /* Enhanced scrolling styles */
    .scrollable-content {
        width: 100%;
        overflow-y: auto;
        word-wrap: break-word;
        white-space: normal;
        text-align: inherit; /* Respects the alignment buttons */
    }

    /* Visible scrollbar styling */
    .scrollable-content::-webkit-scrollbar {
        width: 6px;
    }

    .scrollable-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .scrollable-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .scrollable-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .flashcard-container { 
        perspective: 1500px; /* Increased for deeper 3D effect */
        width: 100%; 
        height: 650px;
        cursor: pointer; 
    }

    .flashcard-inner { 
        position: relative; 
        width: 100%; 
        height: 100%; 
        transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Snappy bounce effect */
        transform-style: preserve-3d; 
    }
    .flipped .flashcard-inner { transform: rotateY(180deg); }
    .flashcard-front, .flashcard-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border: 1px solid #dee2e6; border-radius: 20px; }
    .flashcard-back {
        /* Use !important to crush any leftover Bootstrap bg-classes */
        background-color: var(--card-bg-color) !important; 
        
        /* Contrast fix: Ensure text is readable */
        color: #333 !important; 
        
        transform: rotateY(180deg);
        display: flex;
        flex-direction: column;
        padding: 20px !important;
        overflow-y: auto;
        border-radius: 20px;
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
    }

    /* Adjust flashcard layout for back side */
    .question-section, .answer-section {
        width: 100%;
        text-align: left;
    }

    .flashcard-back .scrollable-content {
        text-align: justify;
    }

    /* Ensure text inside the back of the card inherits the dark color */
    .flashcard-back h4, 
    .flashcard-back small, 
    .flashcard-back p {
        color: inherit !important;
    }
    .flashcard-front img, 
    .flashcard-back img, 
    #quiz-question img {
        max-width: 100%;
        max-height: 250px;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        margin-top: 10px;
    }
    .flashcard-front {
        overflow-y: auto;
        padding: 20px !important;
    }

    .flashcard-front::-webkit-scrollbar {
        width: 6px;
    }

    .flashcard-front::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .flashcard-front::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .flashcard-front::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Apply Indentation (Tab) to the first paragraph or first line */
    .flashcard-front h4, 
    .flashcard-back h4 {
        text-indent: 50px; /* This creates the "Tab" effect */
        margin-bottom: 0;
        line-height: 1.6; /* Improves readability for justified text */
    }

    /* If CKEditor wraps your text in <p> tags, use this: */
    .flashcard-front h4 p, 
    .flashcard-back h4 p {
        text-indent: 50px;
        margin-bottom: 1rem;
    }

    /* Ensure the "QUESTION" and "ANSWER" labels remain centered and NOT indented */
    .flashcard-front small, 
    .flashcard-back small {
        display: block;
        text-align: center;
        text-indent: 0; 
        width: 100%;
    }

    .tag-toggle-input:checked + label {
        background-color: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd !important;
        box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
    }

    .tag-toggle-input + label {
        transition: all 0.2s ease;
        font-size: 0.8rem;
        font-weight: 500;
    }

    #text-toolbar::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 8px 8px 0;
        border-style: solid;
        border-color: #212529 transparent transparent;
    }

    .highlight-red { background-color: #ffcccc; color: #b30000; padding: 0 2px; border-radius: 3px; }
    .underline-red { text-decoration: underline; text-decoration-color: red; text-underline-offset: 4px; }

    /* Dynamic Font Class */
    #study-question, #study-answer {
        transition: font-size 0.2s ease, text-align 0.2s ease;
    }

    /* Remove any fixed font sizes in CSS that might be blocking JS */
    #study-question, #study-question-back, #study-answer {
        /* Don't set a font-size here, let JS handle it */
        min-height: 50px;
    }
    #format-success-msg {
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    #card-counter.dropdown-toggle::after {
        margin-left: 8px;
        vertical-align: middle;
    }

    #card-counter:hover {
        background-color: #495057 !important;
        cursor: pointer;
    }

    .dropdown-item.active {
        background-color: #0d6efd;
    }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <script>
        // Wait for the DOM and Bootstrap to be ready
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($mode) && isset($targetDeck))
                // Automatically trigger the quiz/study modal
                // Using a slight timeout ensures the Modal library is fully initialized
                setTimeout(() => {
                    startStudyDeck('{{ $targetSubject }}', '{{ $targetDeck }}', '{{ $mode }}');
                }, 500);
            @endif
        });
    </script>
    <script>
    // Initialize Global Variables
    let originalDeckLength = 0; 
    let currentDeck = [];
    let currentIndex = 0;
    let score = 0;
    let timer;
    let timeLeft = 0;
    let quizResults = [];
    let activeDeckId, activeDeckType;
    let currentFontSize = 18;  


    // Initialize all Bootstrap tooltips on the page
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });

    // Standard helper to get Bootstrap modal instance
    function getModal(id) {
        const el = document.getElementById(id);
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    }

    
    function skipQuestion() {
        clearInterval(timer);
        const feedback = document.getElementById('quiz-feedback');
        const input = document.getElementById('quiz-input');
        
        input.disabled = true;
        document.getElementById('quiz-submit-btn').disabled = true;
        document.getElementById('quiz-skip-btn').disabled = true;

        feedback.innerHTML = `<span class="text-warning"><i class="bi bi-arrow-repeat"></i> Question Skipped. It will reappear later.</span>`;

        setTimeout(() => {
            // Move current card to the end of the array
            const skippedCard = currentDeck.splice(currentIndex, 1)[0];
            currentDeck.push(skippedCard);
            
            currentIndex = 0; 
            renderQuizCard(false); 
        }, 1500);
    }
    
    // function startStudyDeck(deckId, deckType) {
    //     // Set active deck context
    //     activeSubjectId = subjectId; 
    //     activeDeckName = deckName; 
    //     activeDeckType = deckType;
    //     activeDeckId = deckId;
        
    //     // Reset global state for a clean start (will be overwritten if progress exists)
    //     quizResults = []; 
    //     score = 0; 
    //     currentIndex = 0;

    //     // 1. Fetch the deck and progress from the server
    //     // fetch(`/flashcards/fetch-deck?subject_id=${subjectId}&deck_name=${encodeURIComponent(deckName)}&deck_type=${deckType}`)
    //     fetch(`/flashcards/fetch-deck?deck_id=${deckId}`)
    //         .then(response => {
    //             if (!response.ok) return response.json().then(err => { throw err; });
    //             return response.json();
    //         })
    //         .then(res => {
    //             const data = res.cards;
    //             const progress = res.progress;

    //             if (!data || data.length === 0) {
    //                 alert("This deck has no cards.");
    //                 return;
    //             }

    //             // Load cards into memory
    //             currentDeck = [...data];
    //             originalDeckLength = data.length;
                
    //             // 2. RESTORE PROGRESS LOGIC (Get back to previous question)
    //             if (progress) {
    //                 currentIndex = parseInt(progress.current_index);
    //                 score = parseInt(progress.score);
    //                 timeLeft = parseInt(progress.remaining_seconds);
                    
    //                 // If the deck was shuffled in the previous session, restore that specific order
    //                 if (progress.deck_order) {
    //                     try {
    //                         const order = JSON.parse(progress.deck_order);
    //                         currentDeck = order.map(id => data.find(c => c.id == id)).filter(c => c);
    //                     } catch (e) {
    //                         console.error("Failed to parse deck order:", e);
    //                     }
    //                 }
    //                 console.log(`Resuming ${deckName} at Item ${currentIndex + 1}`);
    //             } else {
    //                 currentIndex = 0;
    //                 score = 0;
    //             }

    //             // Update Modal UI
    //             document.getElementById('modalDeckTitle').innerText = deckName;
                
    //             if(deckType === 'quiz') {
    //                 document.getElementById('study-mode-ui').style.display = 'none';
    //                 document.getElementById('quiz-mode-ui').style.display = 'block';
    //                 document.getElementById('quiz-timer-wrapper').style.display = 'block';
                    
    //                 // If they previously finished but the page didn't reload, reset to 0
    //                 if (currentIndex >= currentDeck.length) currentIndex = 0;
                    
    //                 renderQuizCard(!!progress); // Pass true if we are resuming a timer
    //             } else {
    //                 document.getElementById('study-mode-ui').style.display = 'block';
    //                 document.getElementById('quiz-mode-ui').style.display = 'none';
    //                 document.getElementById('quiz-timer-wrapper').style.display = 'none';
    //                 renderStudyCard();
    //             }

    //             // Show the Modal
    //             const modalElement = document.getElementById('studyModal');
    //             const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    //             modalInstance.show();
    //         })
    //         .catch(err => {
    //             // 3. ERROR HANDLING (Teacher Assigned Deck already answered)
    //             if (err.error === 'already_answered') {
    //                 alert("🚫 Access Denied: " + err.message);
    //                 // Optionally reload to trigger the controller's exclusion logic
    //                 window.location.reload();
    //             } else {
    //                 console.error("Fetch Error:", err);
    //                 alert("An unexpected error occurred while loading the deck.");
    //             }
    //         });
    // }

    function renderQuizCard(isResuming = false) {
        if (currentIndex >= currentDeck.length) return finishQuiz();
        
        const card = currentDeck[currentIndex];
        const quizTopicEl = document.getElementById('quiz-topic');
        const input = document.getElementById('quiz-input');
        const submitBtn = document.getElementById('quiz-submit-btn');
        const skipBtn = document.getElementById('quiz-skip-btn');

        // Reset UI for the new card
        input.value = "";
        input.disabled = false;
        submitBtn.disabled = false;
        skipBtn.disabled = false;
        document.getElementById('quiz-feedback').innerText = "";
        document.getElementById('card-counter').innerText = `Item ${currentIndex + 1} of ${currentDeck.length}`;
        
        if (card.topic) {
            quizTopicEl.innerText = card.topic;
            quizTopicEl.style.display = 'inline-block';
        } else {
            quizTopicEl.style.display = 'none';
        }

        document.getElementById('quiz-question').innerHTML = card.question;
        document.getElementById('quiz-input').value = "";
        document.getElementById('quiz-input').disabled = false;
        document.getElementById('quiz-feedback').innerText = "";
        document.getElementById('card-counter').innerText = `Item ${currentIndex + 1} of ${currentDeck.length}`;
        
        // Use saved time if resuming, otherwise use card's default
        if (!isResuming) {
            timeLeft = card.timer_seconds || 20;
        }
        
        document.getElementById('timer-display').innerText = timeLeft;
        timer = setInterval(() => {
            timeLeft--;
            document.getElementById('timer-display').innerText = timeLeft;
            if (timeLeft <= 0) submitQuizAnswer(true);
            // Auto-save progress every 5 seconds
            if (timeLeft % 5 === 0) saveCurrentProgress();
        }, 1000);
    }

    // function saveCurrentProgress() {
    //     const data = {
    //         subject_id: activeSubjectId,
    //         deck_name: activeDeckName,
    //         deck_type: activeDeckType,
    //         current_index: currentIndex,
    //         remaining_seconds: timeLeft,
    //         score: score,
    //         deck_order: currentDeck.map(c => c.id),
    //         _token: '{{ csrf_token() }}'
    //     };
    //     fetch("{{ route('decks.save_progress') }}", {
    //         method: "POST",
    //         headers: { "Content-Type": "application/json" },
    //         body: JSON.stringify(data)
    //     });
    // }
    function startStudyDeck(deckId, deckType) {
        // Set active deck context
        activeDeckId = deckId;
        activeDeckType = deckType;
        
        // Reset global state for a clean start
        quizResults = []; 
        score = 0; 
        currentIndex = 0;

        // 1. Fetch the deck using the unique ID
        fetch(`/flashcards/fetch-deck?deck_id=${deckId}`)
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(res => {
                const data = res.cards;
                const progress = res.progress;

                if (!data || data.length === 0) {
                    alert("This deck has no cards.");
                    return;
                }

                fullDeckCopy = [...res.cards]; // Save original
                currentDeck = [...res.cards];

                // currentDeck = [...data];
                originalDeckLength = data.length;
                
                // 2. RESTORE PROGRESS LOGIC
                if (progress) {
                    currentIndex = parseInt(progress.current_index);
                    score = parseInt(progress.score);
                    timeLeft = parseInt(progress.remaining_seconds);
                    currentFontSize = parseInt(progress.font_size) || 18;
                    // Set the dropdown and alignment UI to match database
                    if(progress.font_family) document.getElementById('font-family-select').value = progress.font_family;
                    if(progress.alignment) setCardAlignment(progress.alignment);
                    
                    // Update the font size label in toolbar
                    document.getElementById('font-size-label').innerText = currentFontSize + 'px';
                    
                    if (progress.deck_order) {
                        try {
                            const order = JSON.parse(progress.deck_order);
                            currentDeck = order.map(id => data.find(c => c.id == id)).filter(c => c);
                        } catch (e) {
                            console.error("Failed to parse deck order:", e);
                        }
                    }
                }

                // 3. Update Modal UI 
                // Note: Since we don't have deckName in the function params anymore, 
                // we can get it from the 'res' object if your controller sends it, 
                // or just use a generic title.
                document.getElementById('modalDeckTitle').innerText = res.deck_name || "Flashcards";
                
                if(deckType === 'quiz') {
                    document.getElementById('study-mode-ui').style.display = 'none';
                    document.getElementById('quiz-mode-ui').style.display = 'block';
                    document.getElementById('quiz-timer-wrapper').style.display = 'block';
                    
                    if (currentIndex >= currentDeck.length) currentIndex = 0;
                    renderQuizCard(!!progress); 
                } else {
                    document.getElementById('study-mode-ui').style.display = 'block';
                    document.getElementById('quiz-mode-ui').style.display = 'none';
                    document.getElementById('quiz-timer-wrapper').style.display = 'none';
                    renderStudyCard();
                }

                const modalElement = document.getElementById('studyModal');
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalInstance.show();
            })
            .catch(err => {
                if (err.error === 'already_answered') {
                    alert("🚫 Access Denied: " + err.message);
                    window.location.reload();
                } else {
                    console.error("Fetch Error:", err);
                    alert("An error occurred while loading the deck.");
                }
            });
    }

    // 4. Update saveCurrentProgress to use activeDeckId


    function submitQuizAnswer(isTimeout = false) {
        clearInterval(timer); // Stop the current timer
        
        const card = currentDeck[currentIndex];
        const input = document.getElementById('quiz-input');
        const feedback = document.getElementById('quiz-feedback');
        const submitBtn = document.getElementById('quiz-submit-btn');
        const skipBtn = document.getElementById('quiz-skip-btn');

        // Disable inputs to prevent double submission
        input.disabled = true;
        submitBtn.disabled = true;
        skipBtn.disabled = true;

        // Normalize comparison: strip HTML and trim whitespace
        const userAnswer = input.value.trim().toLowerCase();
        const correctAnswer = stripHtml(card.answer).trim().toLowerCase();
        const isCorrect = !isTimeout && userAnswer === correctAnswer;
        
        if(isCorrect) score++;
        
        // Track results for final summary
        quizResults.push({ 
            card_id: card.id, 
            is_correct: isCorrect, 
            user_answer: userAnswer, // Track the text
            time_spent: (card.timer_seconds || 20) - timeLeft // Track time
        });
        
        // Show feedback
        if (isCorrect) {
            feedback.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Correct!</span>';
        } else {
            feedback.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> ${isTimeout ? 'Time up!' : 'Wrong!'} Answer: ${stripHtml(card.answer)}</span>`;
        }

        // Wait 2 seconds, then proceed to next card
        setTimeout(() => {
            currentIndex++; // Move to next card
            
            if (currentIndex < currentDeck.length) {
                saveCurrentProgress(); // Save state to DB
                renderQuizCard();      // Render the next card (this clears feedback and re-enables inputs)
            } else {
                finishQuiz();          // End of deck
            }
        }, 2000);
    }

    // 1. Keep ONLY this version of saveCurrentProgress
    function saveCurrentProgress() {
        if (!activeDeckId) return; 

        const data = {
            deck_id: activeDeckId,
            current_index: currentIndex, 
            font_family: document.getElementById('font-family-select').value,
            font_size: currentFontSize,
            alignment: document.getElementById('study-question').style.textAlign || 'justify',
            remaining_seconds: timeLeft || 0,
            score: score || 0,
            deck_order: currentDeck.map(c => c.id),
            _token: '{{ csrf_token() }}'
        };
        
        fetch("{{ route('decks.save_progress') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "Accept": "application/json" // Force JSON response
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(d => {
            console.log("Progress saved successfully");
        })
        .catch(e => {
            console.error("Save Error:", e.message || e);
        });
    }


    // function saveCurrentProgress() {
    //     if (!activeDeckId) return; 
        
    //     const data = {
    //         deck_id: activeDeckId, // Send the ID
    //         current_index: currentIndex,
    //         remaining_seconds: timeLeft,
    //         score: score,
    //         deck_order: currentDeck.map(c => c.id),
    //         _token: '{{ csrf_token() }}'
    //     };
        
    //     fetch("{{ route('decks.save_progress') }}", {
    //         method: "POST",
    //         headers: { "Content-Type": "application/json" },
    //         body: JSON.stringify(data)
    //     });
    // }

    // 2. Update this to use activeDeckId as well
    function clearDeckProgressFromServer() {
        return fetch("{{ route('decks.save_progress') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                deck_id: activeDeckId, // Use ID here
                current_index: 0,
                remaining_seconds: 0,
                score: 0,
                deck_order: [],
                _token: '{{ csrf_token() }}'
            })
        });
    }

    function closeStudyModal() {
        clearInterval(timer);
        saveCurrentProgress(); // Final save on close
    }

    function finishQuiz() {
        const finalScore = quizResults.filter(r => r.is_correct).length;
        
        // Map the results to include full details for the summary page
        const detailedResults = quizResults.map(result => {
            const card = currentDeck.find(c => c.id == result.card_id);
            return {
                card_id: result.card_id,
                is_correct: result.is_correct,
                question: card ? card.question : 'Unknown Question',
                correct_answer: card ? card.answer : 'N/A',
                user_answer: result.user_answer || '(No Answer)', // Make sure you track this in submitQuizAnswer
                time_spent: result.time_spent || 0
            };
        });

        fetch("{{ route('quiz.save_score') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            },
            body: JSON.stringify({
                deck_name: document.getElementById('modalDeckTitle').innerText,
                score: finalScore, 
                total: originalDeckLength,
                details: detailedResults // Send the enriched data
            })
        })
        .then(() => clearDeckProgressFromServer())
        .then(() => {
            alert(`Quiz Finished! Your Score: ${finalScore} / ${originalDeckLength}`);
            window.location.reload(); 
        });
    }





    // function renderStudyCard() {
    //     const card = currentDeck[currentIndex];
        
    //     if (!card) return;
    //     // Remove flip animation for the new card
    //     // document.querySelector('.flashcard-container').classList.remove('flipped');
    //     const container = document.querySelector('.flashcard-container');
    //     if (container) {
    //         container.classList.remove('flipped');
    //     }

    //     // Reset all tag checkboxes first
    //     document.querySelectorAll('.tag-toggle-input').forEach(cb => cb.checked = false);

    //     // If the card has labels, check the corresponding boxes
    //     if (card.labels) {
    //         const labels = Array.isArray(card.labels) ? card.labels : JSON.parse(card.labels);
    //         labels.forEach(label => {
    //             const checkbox = document.querySelector(`.tag-toggle-input[value="${label}"]`);
    //             if (checkbox) checkbox.checked = true;
    //         });
    //     }
    //     // Handle Topic Display
    //     const topicEl = document.getElementById('study-topic');
    //     if (card.topic) {
    //         topicEl.innerText = card.topic;
    //         topicEl.style.display = 'inline-block';
    //     } else {
    //         topicEl.style.display = 'none';
    //     }
    //     // document.querySelector('.flashcard-container').classList.remove('flipped');
    //     // document.getElementById('study-question').innerHTML = card.question;
    //     // document.getElementById('study-answer').innerHTML = card.answer;
    //     // document.getElementById('study-reference').innerText = card.reference ? 'Ref: ' + card.reference : '';
    //     // document.getElementById('card-counter').innerText = `${currentIndex + 1} / ${currentDeck.length}`;
    //     const questionEl = document.getElementById('study-question');
    //     const answerEl = document.getElementById('study-answer');
    //     const referenceEl = document.getElementById('study-reference');
    //     const counterEl = document.getElementById('card-counter');

    //     if (questionEl) questionEl.innerHTML = card.question;
    //     if (answerEl) answerEl.innerHTML = card.answer;
    //     if (referenceEl) referenceEl.innerText = card.reference ? 'Ref: ' + card.reference : '';
    //     if (counterEl) counterEl.innerText = `${currentIndex + 1} / ${currentDeck.length}`;

    //     // Apply current font styles
    //     if (questionEl && answerEl) {
    //         questionEl.style.fontSize = currentFontSize + 'px';
    //         answerEl.style.fontSize = currentFontSize + 'px';
    //     }
    // }

    function renderStudyCard() {
        const card = currentDeck[currentIndex];
        if (!card) return;

        // 1. ALWAYS reset to Front side when loading a new card
        const container = document.querySelector('.flashcard-container');
        if (container) {
            container.classList.remove('flipped');
        }

        // 2. Reset and Apply Tag Checkboxes
        document.querySelectorAll('.tag-toggle-input').forEach(cb => cb.checked = false);
        if (card.labels) {
            const labels = Array.isArray(card.labels) ? card.labels : JSON.parse(card.labels);
            labels.forEach(label => {
                const checkbox = document.querySelector(`.tag-toggle-input[value="${label}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }

        // 3. Handle Topic Display
        const topicEl = document.getElementById('study-topic');
        if (topicEl) {
            if (card.topic) {
                topicEl.innerText = card.topic;
                topicEl.style.display = 'inline-block';
            } else {
                topicEl.style.display = 'none';
            }
        }

        // 4. Update Content - Populate both question elements
        const questionFront = document.getElementById('study-question');
        const questionBack = document.getElementById('study-question-back');
        const answerEl = document.getElementById('study-answer');
        const referenceEl = document.getElementById('study-reference');
        const counterEl = document.getElementById('card-counter');
        const jumpListEl = document.getElementById('card-jump-list');

        // Populate the Jump List dropdown (only need to do this if list is empty or deck changed)
        if (jumpListEl && jumpListEl.children.length !== currentDeck.length) {
            jumpListEl.innerHTML = '';
            currentDeck.forEach((_, index) => {
                const li = document.createElement('li');
                li.innerHTML = `<a class="dropdown-item ${index === currentIndex ? 'active' : ''}" href="#" onclick="jumpToCard(${index})">${index + 1}</a>`;
                jumpListEl.appendChild(li);
            });
        } else if (jumpListEl) {
            // Just update the active class if list already exists
            Array.from(jumpListEl.querySelectorAll('.dropdown-item')).forEach((el, idx) => {
                el.classList.toggle('active', idx === currentIndex);
            });
        }

        if (questionFront) questionFront.innerHTML = card.question;
        if (questionBack) questionBack.innerHTML = card.question;
        if (answerEl) answerEl.innerHTML = card.answer;
        if (referenceEl) referenceEl.innerText = card.reference ? 'Ref: ' + card.reference : '';
        if (counterEl) counterEl.innerText = `${currentIndex + 1} / ${currentDeck.length}`;

        // APPLY DEFAULT FONT STYLE
        if (questionFront && questionBack && answerEl) {
            // Use the value currently selected in the dropdown, or default to Georgia
            const selectedFont = document.getElementById('font-family-select').value || "'Georgia', serif";
            
            questionFront.style.fontFamily = selectedFont;
            questionBack.style.fontFamily = selectedFont;
            answerEl.style.fontFamily = selectedFont;
            
            // Ensure the font size variable is also applied
            questionFront.style.fontSize = currentFontSize + 'px';
            questionBack.style.fontSize = currentFontSize + 'px';
            answerEl.style.fontSize = currentFontSize + 'px';
        }

        // APPLY STYLES TO ALL ELEMENTS
        const targets = ['study-question', 'study-question-back', 'study-answer'];
        const selectedFont = document.getElementById('font-family-select').value || "'Georgia', serif";

        targets.forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.style.fontFamily = selectedFont;
                el.style.fontSize = currentFontSize + 'px';
            }
        });
    }

    function filterDeckByLabel() {
        // Get all checked labels
        const checkedLabels = Array.from(document.querySelectorAll('.label-filter-check:checked')).map(el => el.value);
        
        if (checkedLabels.length === 0) {
            currentDeck = [...fullDeckCopy];
        } else {
            // Filter: Only keep cards that have AT LEAST ONE of the selected labels
            currentDeck = fullDeckCopy.filter(card => {
                if (!card.labels) return false;
                const cardLabels = Array.isArray(card.labels) ? card.labels : JSON.parse(card.labels);
                return checkedLabels.some(l => cardLabels.includes(l));
            });
        }

        if (currentDeck.length === 0) {
            alert("No cards found with those labels!");
            resetLabelFilter();
        } else {
            currentIndex = 0;
            activeDeckType === 'quiz' ? renderQuizCard() : renderStudyCard();
        }
    }

    function resetLabelFilter() {
        document.querySelectorAll('.label-filter-check').forEach(c => c.checked = false);
        currentDeck = [...fullDeckCopy];
        currentIndex = 0;
        activeDeckType === 'quiz' ? renderQuizCard() : renderStudyCard();
    }

    function jumpToCard(index) {
        if (index >= 0 && index < currentDeck.length) {
            currentIndex = index;
            renderStudyCard();
            saveCurrentProgress(); // Autosave the new position
        }
    }

    function liveUpdateLabel(checkbox) {
        const cardId = currentDeck[currentIndex].id;
        const label = checkbox.value;

        fetch("{{ route('flashcards.toggle_label') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ card_id: cardId, label: label })
        })
        .then(res => res.json())
        .then(data => {
            // Update our local memory of the deck so the tags stay checked if we navigate back
            currentDeck[currentIndex].labels = data.current_labels;
            console.log("Card " + cardId + " updated with labels: " + data.current_labels);
        })
        .catch(err => alert("Error updating tag."));
    }

    function shuffleDeck() {
        if (currentDeck.length <= 1) return;
        for (let i = currentDeck.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [currentDeck[i], currentDeck[j]] = [currentDeck[j], currentDeck[i]];
        }
        currentIndex = 0;
        renderStudyCard();
    }

    function closeStudyModal() {
        clearInterval(timer);
        saveCurrentProgress(); 
    }

    function stripHtml(html) {
        const tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }

    function nextCard() {
        if (currentIndex < currentDeck.length - 1) { 
            currentIndex++; 
            saveCurrentProgress(); // CRITICAL: This saves the "where you left off"
            renderStudyCard(); 
        }
    }

    function prevCard() {
        if (currentIndex > 0) { 
            currentIndex--; 
            saveCurrentProgress(); // CRITICAL: This saves the "where you left off"
            renderStudyCard(); 
        }
    }

    function openInviteModal(deckId) {
        const modalEl = document.getElementById('inviteModal');
        const emailInput = document.getElementById('collab_email_input');
        const feedback = document.getElementById('collab_feedback');
        const submitBtn = document.getElementById('send_invite_btn');
        const collabList = document.getElementById('collab_list');
        const collabSection = document.getElementById('existing_collabs_section');

        // Reset Modal State
        document.getElementById('invite_deck_id').value = deckId;
        emailInput.value = '';
        feedback.innerText = '';
        submitBtn.disabled = true;
        collabList.innerHTML = '';
        collabSection.style.display = 'none';

        // 1. Find the deck data from the global $decks collection (already loaded via with('collaborators'))
        // Note: We'll use a fetch to get the freshest list of collaborators for this deck
        fetch(`/flashcards/fetch-deck?deck_id=${deckId}`)
        .then(res => res.json())
        .then(data => {
            // If your fetchDeck API doesn't return collaborators, you'll need to adjust your API
            // Assuming your 'index' query already has them, we can also pass them via data-attributes on the button
            // For simplicity here, let's assume we handle the "Existing" view via fresh fetch or JS filter
        });

        new bootstrap.Modal(modalEl).show();

        // 2. Real-time Email Validation
        emailInput.addEventListener('keyup', function() {
            const email = this.value.trim();
            if (email.length < 5 || !email.includes('@')) {
                feedback.innerText = '';
                submitBtn.disabled = true;
                return;
            }

            // Debounce or immediate check
            fetch(`/decks/check-collaborator?email=${encodeURIComponent(email)}&deck_id=${deckId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        feedback.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> Found: ${data.name}</span>`;
                        submitBtn.disabled = false;
                    } else {
                        feedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> ${data.message}</span>`;
                        submitBtn.disabled = true;
                    }
                });
        });
    }


    //// FOR FILTERED CARD MODAL
    let pendingTagDeckId = null;

    // 1. Open the Tag Modal
    function openTagFilterModal(deckId) {
        pendingTagDeckId = deckId;
        // Uncheck all boxes from previous usage
        document.querySelectorAll('.study-tag-checkbox').forEach(cb => cb.checked = false);
        new bootstrap.Modal(document.getElementById('tagFilterModal')).show();
    }

    // 2. Filter and Start Session
    function startTaggedStudy() {
        const selectedTags = Array.from(document.querySelectorAll('.study-tag-checkbox:checked')).map(cb => cb.value);
        
        if (selectedTags.length === 0) {
            alert("Please select at least one tag.");
            return;
        }

        // Hide selection modal
        bootstrap.Modal.getInstance(document.getElementById('tagFilterModal')).hide();

        // Fetch the deck and filter cards in memory
        fetch(`/flashcards/fetch-deck?deck_id=${pendingTagDeckId}`)
            .then(res => res.json())
            .then(res => {
                const allCards = res.cards;
                
                // Filter cards based on labels
                const filteredCards = allCards.filter(card => {
                    if (!card.labels) return false;
                    // Handle both array and JSON string formats
                    const cardLabels = Array.isArray(card.labels) ? card.labels : JSON.parse(card.labels);
                    return selectedTags.some(tag => cardLabels.includes(tag));
                });

                if (filteredCards.length === 0) {
                    alert("No cards found with the selected tags.");
                    return;
                }

                // Inject the filtered deck into the study session
                currentDeck = [...filteredCards];
                originalDeckLength = filteredCards.length;
                currentIndex = 0;
                activeDeckId = pendingTagDeckId;
                activeDeckType = 'study';

                // UI Setup for Study Mode
                document.getElementById('modalDeckTitle').innerText = (res.deck_name || "Flashcards") + " (Filtered)";
                document.getElementById('study-mode-ui').style.display = 'block';
                document.getElementById('quiz-mode-ui').style.display = 'none';
                document.getElementById('quiz-timer-wrapper').style.display = 'none';
                
                renderStudyCard();
                
                // Show the Study Modal
                bootstrap.Modal.getOrCreateInstance(document.getElementById('studyModal')).show();
            });
    }


    //let currentFontSize = 18;

    // 1. Detect selection to show toolbar
    document.addEventListener('mouseup', function() {
        const selection = window.getSelection();
        const toolbar = document.getElementById('text-toolbar');
        
        if (selection.toString().length > 0) {
            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();
            
            toolbar.style.top = `${window.scrollY + rect.top - 50}px`;
            toolbar.style.left = `${window.scrollX + rect.left + (rect.width / 2) - 60}px`;
            toolbar.classList.remove('d-none');
        } else {
            toolbar.classList.add('d-none');
        }
    });

    // 2. Format Selected Text
    function formatSelectedText(type, color = null) {
        const selection = window.getSelection();
        if (!selection.rangeCount || selection.toString().length === 0) return;
        
        const span = document.createElement('span');
        const range = selection.getRangeAt(0);
        if (type === 'clear') {
            const content = range.extractContents();
            range.insertNode(document.createTextNode(content.textContent));
            window.getSelection().removeAllRanges();
            
            // AUTO-SAVE after clearing
            saveFormattedCard(); 
            return;
        }
        
        if (type === 'highlight') {
            span.style.backgroundColor = color;
            span.style.padding = '0 2px';
            span.style.borderRadius = '3px';
            span.style.color = '#000'; // Ensure text is readable on light highlights
        } else if (type === 'bold') {
            span.style.fontWeight = 'bold';
        } else if (type === 'underline') {
            span.classList.add('underline-red');
        } else if (type === 'clear') {
            const content = range.extractContents();
            range.insertNode(document.createTextNode(content.textContent));
            return;
        }

        span.appendChild(range.extractContents());
        range.insertNode(span);
        window.getSelection().removeAllRanges();
        saveFormattedCard();
    }

    // 3. Persistent Card Styles (Alignment, Font, Size)
    function adjustFontSize(delta) {
        currentFontSize += delta;
        if(currentFontSize < 10) currentFontSize = 10; 
        if(currentFontSize > 50) currentFontSize = 50; 
        
        const label = document.getElementById('font-size-label');
        if(label) label.innerText = currentFontSize + 'px';
        
        // Target by class to ensure all versions (front/back) are updated
        const contents = document.querySelectorAll('.scrollable-content');
        contents.forEach(el => {
            el.style.setProperty('font-size', currentFontSize + 'px', 'important');
            el.style.setProperty('line-height', '1.6', 'important');
        });

        saveCurrentProgress();
    }

    document.getElementById('font-family-select').onchange = function() {
        const selectedFont = this.value;
        document.getElementById('study-question').style.fontFamily = selectedFont;
        document.getElementById('study-question-back').style.fontFamily = selectedFont;
        document.getElementById('study-answer').style.fontFamily = selectedFont;
        saveCurrentProgress();
    };

    function setCardAlignment(align) {
        document.getElementById('study-question').style.textAlign = align;
        document.getElementById('study-question-back').style.textAlign = align;
        document.getElementById('study-answer').style.textAlign = align;
        saveCurrentProgress();
    }

    // 4. Save the formatted text back to DB
    function saveFormattedCard() {
        // Check if we are actually in study mode
        if (activeDeckType !== 'study') {
            alert("Formatting can only be saved in Study Mode.");
            return;
        }
        const cardId = currentDeck[currentIndex].id;
        const isFlipped = document.querySelector('.flashcard-container').classList.contains('flipped');
        const updatedHtml = isFlipped ? 
            document.getElementById('study-answer').innerHTML : 
            document.getElementById('study-question').innerHTML;

        fetch("{{ route('flashcards.update') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id: cardId,
                [isFlipped ? 'answer' : 'question']: updatedHtml
            })
        }).then(() => {
            // Update local memory for both question elements
            if(isFlipped) {
                currentDeck[currentIndex].answer = updatedHtml;
            } else {
                currentDeck[currentIndex].question = updatedHtml;
                // Update both question elements to keep them in sync
                const questionFront = document.getElementById('study-question');
                const questionBack = document.getElementById('study-question-back');
                if (questionFront) questionFront.innerHTML = updatedHtml;
                if (questionBack) questionBack.innerHTML = updatedHtml;
            }
            
            // SHOW SUCCESS MESSAGE
                const msg = document.getElementById('format-success-msg');
                msg.style.display = 'block';
                msg.style.opacity = '1';

                // Hide it automatically after 2 seconds
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => { msg.style.display = 'none'; }, 500);
                }, 2000);
        });
    }
    function toggleFlip() {
        const container = document.querySelector('.flashcard-container');
        if (container) {
            container.classList.toggle('flipped');
        }
    }
    </script>
    @endsection