@extends('errors.layout')

@section('title', 'Accès interdit')

@section('content')
    <div class="error-code">403</div>
    <div class="error-icon mb-3">
        <i class="fas fa-ban"></i>
    </div>
    <h4>Accès refusé</h4>
    <p class="text-muted">
        Vous n’avez pas l’autorisation d’accéder à cette page.
    </p>
@endsection
