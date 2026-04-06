@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Settings</h4>

    {{-- Success/Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

   {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold px-4 border-0" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" type="button">General Settings</button>
        </li>
        @if(in_array(auth()->user()->role, ['student', 'teacher', 'admin', 'super_admin']))
        <li class="nav-item">
            <button class="nav-link fw-bold px-4 border-0" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription-pane" type="button">Subscription & Pricing</button>
        </li>
        @endif
        <li class="nav-item">
            <button class="nav-link fw-bold px-4 border-0" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button">Quiz Logs</button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="settingsTabsContent">
        <div class="tab-pane fade show active" id="general-pane" role="tabpanel" aria-labelledby="general-tab">
            <div class="card border-0 shadow-sm p-4">
                {{-- GENERAL SETTINGS --}}
                
                    <div class="card border-0 shadow-sm p-4">
                        <form action="{{ route('settings.update_timer') }}" method="POST">
                            @csrf
                            <h6 class="fw-bold mb-3 text-uppercase small text-muted tracking-wide">Quiz Timers (Seconds)</h6>
                            <div class="mb-3">
                                <label class="form-label small">Easy Difficulty</label>
                                <input type="number" name="easy" class="form-control" value="{{ $timers['easy'] ?? 30 }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Average Difficulty</label>
                                <input type="number" name="average" class="form-control" value="{{ $timers['average'] ?? 20 }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Hard Difficulty</label>
                                <input type="number" name="hard" class="form-control" value="{{ $timers['hard'] ?? 10 }}" required>
                            </div>
                            
                            <hr class="my-4 opacity-50">
                            
                            <h6 class="fw-bold mb-3 text-uppercase small text-muted tracking-wide">Appearance</h6>
                            <div class="mb-4">
                                <label class="form-label small">Flashcard Answer Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="card_color" class="form-control form-control-color border-0" value="{{ auth()->user()->card_color ?? '#CBDCEB' }}">
                                    <span class="text-muted small">Choose the background color for flipped cards.</span>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-3 text-uppercase small text-muted tracking-wide">Custom Review Labels</h6>
                            <div class="mb-3">
                                <label class="form-label small">Manage Labels (Comma Separated)</label>
                                <input type="text" name="custom_labels" class="form-control" 
                                    value="{{ is_array(auth()->user()->custom_labels) ? implode(', ', auth()->user()->custom_labels) : 'Definition, Memorize, Cases, Landmark Cases' }}" 
                                    placeholder="Definition, Memorize, Cases...">
                                <div class="form-text">These will appear as checkboxes when creating or managing cards.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                                Save General Settings
                            </button>
                        </form>
                    </div>
            </div>
        </div>

        <div class="tab-pane fade" id="subscription-pane" role="tabpanel" aria-labelledby="subscription-tab">
            <div class="card border-0 shadow-sm p-4">
                 {{-- SUBSCRIPTION SETTINGS --}}
                 {{-- Active Subscription Info & Cancel Button --}}
                   @php
                        // 1. Get the "Main" active subscription
                        $activeSub = \DB::table('user_subscriptions')
                            ->join('subscription_plans', 'user_subscriptions.plan_id', '=', 'subscription_plans.id')
                            ->where('user_id', auth()->id())
                            ->where('expires_at', '>', now())
                            ->select('user_subscriptions.*', 'subscription_plans.name as plan_name')
                            ->orderBy('expires_at', 'desc') 
                            ->first();

                        // 2. Get the full timeline queue
                        $subscriptionQueue = \DB::table('user_subscriptions')
                            ->join('subscription_plans', 'user_subscriptions.plan_id', '=', 'subscription_plans.id')
                            ->where('user_id', auth()->id())
                            ->where('expires_at', '>', now())
                            ->select('user_subscriptions.*', 'subscription_plans.name as plan_name')
                            ->orderBy('starts_at', 'asc')
                            ->get();

                        // 🟢 NEW: Check if the user has EVER used a 1-Day plan (Trial)
                        // Assuming your trial plan has a promo_price of 0 or a specific name
                        $hasUsedTrial = \DB::table('user_subscriptions')
                            ->join('subscription_plans', 'user_subscriptions.plan_id', '=', 'subscription_plans.id')
                            ->where('user_id', auth()->id())
                            ->where(function($query) {
                                $query->where('subscription_plans.promo_price', '<=', 0)
                                    ->orWhere('subscription_plans.name', 'like', '%Trial%')
                                    ->orWhere('subscription_plans.duration_days', 1);
                            })
                            ->exists();
                    @endphp
                    @if($activeSub)
                        <div class="card border-0 shadow-sm mb-4 bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            Current Status: 
                                            <span class="badge {{ $activeSub->status == 'cancelled' ? 'bg-danger' : 'bg-success' }} text-white">
                                                {{ ucfirst($activeSub->status) }}
                                            </span>
                                        </h6>
                                        <p class="small text-muted mb-0">
                                            Total Access ends on: <strong>{{ \Carbon\Carbon::parse($activeSub->expires_at)->format('M d, Y') }}</strong>
                                        </p>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        @if($activeSub->status == 'cancelled')
                                            <form action="{{ route('subscription.resume') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">Resume</button>
                                            </form>
                                        @else
                                            <form action="{{ route('subscription.cancel') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Cancel All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <hr class="my-3 opacity-25">

                                {{-- THE QUEUE LIST --}}
                                <h6 class="small fw-bold text-uppercase text-muted mb-3">Subscription Timeline</h6>
                                <div class="timeline-queue">
                                    @foreach($subscriptionQueue as $index => $qItem)
                                        <div class="d-flex align-items-center mb-2 p-2 bg-white rounded border-start border-4 {{ $index == 0 ? 'border-primary' : 'border-secondary opacity-75' }}">
                                            <div class="flex-grow-1 ms-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="small fw-bold">{{ $qItem->plan_name }}</span>
                                                    @if($index == 0 && \Carbon\Carbon::parse($qItem->starts_at)->isPast())
                                                        <span class="badge bg-soft-primary text-primary small" style="font-size: 0.6rem;">RUNNING NOW</span>
                                                    @else
                                                        <span class="badge bg-light text-muted border small" style="font-size: 0.6rem;">QUEUED</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ \Carbon\Carbon::parse($qItem->starts_at)->format('M d') }} 
                                                    <i class="bi bi-arrow-right mx-1"></i> 
                                                    {{ \Carbon\Carbon::parse($qItem->expires_at)->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                 <div class="row g-4">
                    @foreach($plans as $plan)
                    {{-- 🟢 NEW: Skip Trial if already used --}}
                        @php
                            $isTrialPlan = ($plan->promo_price <= 0 || str_contains(strtolower($plan->name), 'trial') || $plan->duration_days == 1);
                        @endphp
                        
                        @if($isTrialPlan && $hasUsedTrial)
                            @continue
                        @endif
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column text-center">
                                <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                                <div class="mb-3">
                                    <span class="display-6 fw-bold text-primary">₱{{ number_format($plan->promo_price, 2) }}</span>
                                    <div class="text-muted small">Original: <del>₱{{ number_format($plan->original_price, 2) }}</del></div>
                                </div>
                                
                                <div class="badge bg-primary-subtle text-primary border-primary border opacity-75 mb-3 py-2 rounded-pill">
                                    Up to {{ $plan->collaborator_limit }} Collaborators
                                </div>
                                
                                <div class="text-start flex-grow-1 mb-4">
                                    {!! $plan->details !!}
                                </div>

                                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick='openPlanEditor(@json($plan))'>
                                        <i class="bi bi-pencil"></i> Admin Edit Plan
                                    </button>
                                @elseif(in_array(auth()->user()->role, ['student', 'teacher']))
                                    {{-- Add an ID to the form using the plan ID --}}
                                    <form id="subscribe-form-{{ $plan->id }}" action="{{ route('subscription.checkout') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        
                                        {{-- Change type to "button" so it doesn't auto-submit without the confirmation --}}
                                        <a href="{{ route('subscription.show_checkout', $plan->id) }}" class="btn btn-primary rounded-pill w-100 fw-bold">
                                            Select Plan
                                        </a>
                                    </form>
                                @endif 
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="logs-pane" role="tabpanel">
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-3 text-uppercase small text-muted tracking-wide">Your Quiz History</h6>
            <div class="table-responsive">
                <table id="logsDataTable" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Deck Name</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td data-order="{{ $log->created_at }}">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                            </td>
                            <td class="fw-bold">{{ $log->deck_name }}</td>
                            <td>{{ $log->score }} / {{ $log->total_questions }}</td>
                            <td>
                                @php 
                                    $total = $log->total_questions > 0 ? $log->total_questions : 1;
                                    $pct = ($log->score / $total) * 100; 
                                @endphp
                                <div class="progress" style="height: 6px; width: 100px;">
                                    <div class="progress-bar {{ $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                        role="progressbar" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="{{ $pct == 0 ? 'text-danger fw-bold' : '' }}">{{ round($pct) }}%</small>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="viewSummary({{ $log->id }})">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <a href="{{ route('settings.logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
    <i class="bi bi-eye"></i> View Breakdown
</a>
                            </td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

{{-- ADMIN MODAL --}}
@if(auth()->user()->role === 'super_admin')
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.update_plan') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">Update Subscription Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="plan_id" id="modal_plan_id">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Original Price (₱)</label>
                        <input type="number" step="0.01" name="original_price" id="modal_original_price" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Promo Price (₱)</label>
                        <input type="number" step="0.01" name="promo_price" id="modal_promo_price" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Collaborator Limit</label>
                        <input type="number" name="collaborator_limit" id="modal_collab_limit" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Plan Features / Details</label>
                    <textarea name="details" id="admin_plan_editor"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Plan Changes</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- LOGS MODAL -->
 <div class="modal fade" id="summaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">Quiz Performance Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="display-4 fw-bold text-primary mb-2" id="sum_score"></div>
                <h5 class="fw-bold mb-1" id="sum_deck"></h5>
                <p class="text-muted small" id="sum_date"></p>
                <hr>
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="small text-muted text-uppercase">Total Items</div>
                        <div class="h5 fw-bold" id="sum_total"></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted text-uppercase">Correct</div>
                        <div class="h5 fw-bold text-success" id="sum_correct"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Styles --}}
<style>
    .nav-tabs .nav-link { color: #6c757d; border-radius: 0; margin-right: 1rem; }
    .nav-tabs .nav-link.active { 
        color: #0d6efd; 
        border-bottom: 3px solid #0d6efd !important; 
        background: transparent; 
    }
    .card-color-preview { width: 30px; height: 30px; border-radius: 4px; border: 1px solid #ddd; }
    .ck-editor__editable_inline { min-height: 200px; }
    .tracking-wide { letter-spacing: 0.05em; }
</style>

{{-- Scripts --}}
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

{{-- ADD BOOTSTRAP JS HERE --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    let editorInstance;
    $(document).ready(function() { 
        // 1. Initialize DataTable
        if ($.fn.DataTable.isDataTable('#logsDataTable')) {
             $('#logsDataTable').DataTable().destroy();
        }
        $('#logsDataTable').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 10,
            "language": { "search": "Filter logs:" }
        });

        // 2. Handle URL Parameters for Tab Switching
        const settingsUrlParams = new URLSearchParams(window.location.search);
        const activeTab = settingsUrlParams.get('tab');
        const viewMode = settingsUrlParams.get('view');

        // Logic to switch to Subscription Tab
        if (activeTab === 'subscription') {
            const subTabBtn = document.querySelector('#subscription-tab');
            if (subTabBtn) {
                bootstrap.Tab.getOrCreateInstance(subTabBtn).show();
            }
        } 
        // Logic to switch to Logs Tab
        else if (viewMode === 'logs' || activeTab === 'logs') {
            const logsTabBtn = document.querySelector('#logs-tab');
            if (logsTabBtn) {
                bootstrap.Tab.getOrCreateInstance(logsTabBtn).show();
            }
        }
    });
    function viewSummary(logId) {
        fetch(`/settings/logs/${logId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('sum_score').innerText = Math.round((data.score / data.total_questions) * 100) + '%';
                document.getElementById('sum_deck').innerText = data.deck_name;
                document.getElementById('sum_date').innerText = new Date(data.created_at).toLocaleString();
                document.getElementById('sum_total').innerText = data.total_questions;
                document.getElementById('sum_correct').innerText = data.score;
                
                new bootstrap.Modal(document.getElementById('summaryModal')).show();
            });
    }
    
    // Initialize Editor if user is admin
    if (document.querySelector('#admin_plan_editor')) {
        ClassicEditor.create(document.querySelector('#admin_plan_editor'))
            .then(editor => { editorInstance = editor; })
            .catch(err => console.error(err));
    }

    function openPlanEditor(plan) {
        document.getElementById('modal_plan_id').value = plan.id;
        document.getElementById('modal_original_price').value = plan.original_price; // ADD THIS LINE
        document.getElementById('modal_promo_price').value = plan.promo_price;
        document.getElementById('modal_collab_limit').value = plan.collaborator_limit;
        
        if (editorInstance) {
            editorInstance.setData(plan.details || '');
        }
        
        var myModal = new bootstrap.Modal(document.getElementById('editPlanModal'));
        myModal.show();
    }
function confirmSubscription(planId) {
    if (confirm("Are you sure you want to subscribe to this plan?")) {
        // Find the specific form for this plan and submit it
        document.getElementById('subscribe-form-' + planId).submit();
    }
}
</script>
@endsection