@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-center text-primary fw-bold mb-4">Connexion</h3>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form action="{{ url('/login') }}" method="POST" class="card p-4 shadow-sm mx-auto" style="max-width: 400px;">
            @csrf
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
@endsection
