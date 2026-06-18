@extends('errors.layout')

@section('title', 'Session expirée')

@section('content')
    <div class="error-code">419</div>
    <div class="error-icon mb-3">
        <i class="fas fa-clock"></i>
    </div>
    <h4>Session expirée</h4>
    <p class="text-muted">
        Votre session a expiré pour des raisons de sécurité.
        Veuillez vous reconnecter.
    </p>

    <a href="{{ route('login') }}" class="btn btn-danger mt-3">
        <i class="fas fa-sign-in-alt me-1"></i> Se reconnecter
    </a>
@endsection
