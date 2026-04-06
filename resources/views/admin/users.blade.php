@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">User Management</h4>
@if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role == 'super_admin' ? 'bg-danger' : ($user->role == 'encoder' ? 'bg-info' : 'bg-secondary') }}">
                            {{ strtoupper($user->role) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-primary me-1" 
                            onclick='openEditModal(@json($user), @json($user->subscriptions ?? []))'>
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.impersonate', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-person-check"></i> Login As
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

            <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <form action="{{ route('admin.user.update') }}" method="POST" class="modal-content border-0 shadow">
                        @csrf
                        <div class="modal-header border-0 bg-light">
                            <h5 class="modal-title fw-bold">Update User Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="user_id" id="edit_user_id">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email / Username</label>
                                    <input type="email" name="email" id="edit_email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Role</label>
                                    <select name="role" id="edit_role" class="form-select">
                                        <option value="student">Student</option>
                                        <option value="teacher">Teacher</option>
                                        <option value="encoder">Encoder</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Assign Plan</label>
                                    <select name="plan_id" id="edit_plan_id" class="form-select">
                                        <option value="">No Change / None</option>
                                        @foreach(\DB::table('subscription_plans')->get() as $plan)
                                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12" id="subscription_status_container" style="display: none;">
                                    <div class="alert alert-info border-0 mb-0 py-2">
                                        <h6 class="small fw-bold mb-1"><i class="bi bi-card-checklist me-1"></i> Subscription Timeline:</h6>
                                        <div id="subscription_timeline_list" class="small"></div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="small fw-bold text-danger mb-2">Security: Change Password</h6>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <input type="password" name="password" class="form-control" placeholder="New Password">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm New Password">
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Leave blank to keep current password.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Update Account</button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</div>
<script>
    {{-- ADD BOOTSTRAP JS HERE --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script> 
function openEditModal(user, subscriptions) {
    // Basic User Data
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    
    // Subscription Logic
    const timelineContainer = document.getElementById('subscription_status_container');
    const timelineList = document.getElementById('subscription_timeline_list');
    const planSelect = document.getElementById('edit_plan_id');
    
    // Reset
    timelineList.innerHTML = '';
    timelineContainer.style.display = 'none';
    planSelect.value = ""; 

    if (subscriptions && subscriptions.length > 0) {
        timelineContainer.style.display = 'block';
        const now = new Date();

        subscriptions.forEach(sub => {
            const start = new Date(sub.starts_at);
            const end = new Date(sub.expires_at);
            let statusBadge = '';

            // Check if this is the active plan (Currently running)
            if (now >= start && now <= end && sub.status === 'active') {
                statusBadge = '<span class="badge bg-success ms-1">ACTIVE</span>';
                // Auto-select the current plan in the dropdown
                planSelect.value = sub.plan_id;
            } else if (start > now) {
                statusBadge = '<span class="badge bg-warning text-dark ms-1">QUEUED</span>';
            } else if (sub.status === 'cancelled') {
                statusBadge = '<span class="badge bg-danger ms-1">CANCELLED</span>';
            }

            const item = document.createElement('div');
            item.className = 'd-flex justify-content-between border-bottom border-light py-1';
            item.innerHTML = `
                <span><strong>${sub.plan_name || 'Plan'}</strong> ${statusBadge}</span>
                <span class="text-muted">${start.toLocaleDateString()} - ${end.toLocaleDateString()}</span>
            `;
            timelineList.appendChild(item);
        });
    }

    var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    myModal.show();
}
</script>
@endsection