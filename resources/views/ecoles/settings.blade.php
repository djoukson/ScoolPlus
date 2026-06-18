@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">⚙️ Paramètres de l’école</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('ecole.update') }}" method="POST" enctype="multipart/form-data" class="card shadow p-4">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nom de l’école</label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $ecole->nom ?? '') }}" required>
                </div>
                <div class="col-md-4">
                    <label>Directeur Primaire</label>
                    <input type="text" name="directeur_primaire" class="form-control" value="{{ old('directeur_primaire', $ecole->directeur_primaire ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label>Directeur College</label>
                    <input type="text" name="directeur" class="form-control" value="{{ old('directeur', $ecole->directeur ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $ecole->adresse ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $ecole->telephone ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $ecole->email ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Année scolaire</label>
                    <select name="annee_id" class="form-control">
                        @php
                            $anneeSelectionnee = old('annee_id', $ecole->annee->id ?? \App\Models\AnneesScolaire::active()->value('id'));
                        @endphp

                        @foreach(\App\Models\AnneesScolaire::all() as $annee)
                            <option value="{{ $annee->id }}" {{ $annee->id == $anneeSelectionnee ? 'selected' : '' }}>
                                {{ $annee->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Site web</label>
                    <input type="text" name="site_web" class="form-control" value="{{ old('site_web', $ecole->site_web ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label>Logo actuel :</label><br>
                @if(!empty($ecole->logo))
                    <img src="{{ asset('storage/'.$ecole->logo) }}" width="100" class="mb-2">
                @else
                    <p class="text-muted">Aucun logo défini</p>
                @endif
                <input type="file" name="logo" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">
                💾 Sauvegarder les changements
            </button>
        </form>
    </div>
@endsection
