@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Centre de notifications')

@section('content')
    <div class="container py-4">

        <!-- 🔹 Carte principale -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bell text-primary me-2"></i> Notifications
                </h5>

                <!-- Bouton "Tout marquer comme lu" -->
                @if($notifications->where('is_read', 0)->count() > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-check-double me-1"></i> Tout marquer comme lu
                        </button>
                    </form>
                @endif
            </div>

            <div class="card-body p-0">
                @forelse($notifications as $notif)
                    <div class="list-group-item d-flex align-items-start px-3 py-3 border-bottom
                    {{ $notif->is_read ? 'bg-light' : 'bg-white' }}">
                        <div class="me-3">
                            @if($notif->is_read)
                                <i class="fas fa-circle text-muted" style="font-size: 8px;"></i>
                            @else
                                <i class="fas fa-circle text-danger" style="font-size: 8px;"></i>
                            @endif
                        </div>

                        <div class="flex-fill">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $notif->titre }}</strong>
                                <small class="text-muted">
                                    {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <p class="mb-0 text-muted">{{ $notif->message }}</p>
                        </div>

                        <!-- Bouton voir -->
                        <div class="ms-3">
                            <a href="{{ route('notifications.show', $notif->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <p>Aucune notification pour le moment.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="card-footer bg-white text-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
