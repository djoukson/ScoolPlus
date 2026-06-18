@extends('errors.layout')

@section('title', 'Page introuvable')

@section('content')
    <div class="error-code">404</div>
    <div class="error-icon mb-3">
        <i class="fas fa-search"></i>
    </div>
    <h4>Page introuvable</h4>
    <p class="text-muted">
        La page que vous cherchez n’existe pas ou a été déplacée.
    </p>
@endsection
