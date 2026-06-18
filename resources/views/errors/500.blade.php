@extends('errors.layout')

@section('title', 'Erreur serveur')

@section('content')
    <div class="error-code">500</div>
    <div class="error-icon mb-3">
        <i class="fas fa-bug"></i>
    </div>
    <h4>Erreur interne du serveur</h4>
    <p class="text-muted">
        Une erreur est survenue. Merci de réessayer plus tard.
    </p>
@endsection
