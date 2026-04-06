@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4"><i class="bi bi-bell me-2"></i>Collaboration Invites</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($invites as $invite)
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Invitation to collaborate</h6>
                            <p class="text-muted mb-0 small">Deck: <strong>{{ $invite->deck_name }}</strong></p>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('invites.respond', $invite->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="btn btn-success btn-sm px-3 rounded-pill">Accept</button>
                            </form>
                            
                            <form action="{{ route('invites.respond', $invite->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill">Decline</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-envelope-open text-muted display-1"></i>
                <p class="mt-3 text-muted">No pending invitations found.</p>
                <a href="{{ route('flashcards.index') }}" class="btn btn-primary rounded-pill">Back to Decks</a>
            </div>
        @endforelse
    </div>
</div>
@endsection