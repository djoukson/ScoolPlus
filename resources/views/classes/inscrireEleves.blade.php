@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3>Inscrire des Élèves dans la Classe : {{ $classe->nom }}</h3>
        {{-- ✅ Messages flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                <strong>✅ Succès :</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                <strong>⚠️ Erreur :</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('classes.storeEleves', $classe->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="eleves">Sélectionner les élèves</label>
                        <select name="eleves[]" id="eleves" class="form-control" multiple required>
                            @foreach($eleves as $eleve)
                                <option value="{{ $eleve->id }}">
                                    {{ $eleve->nom }} {{ $eleve->prenom }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">Maintenez CTRL (ou CMD sur Mac) pour sélectionner plusieurs élèves.</small>
                    </div>

                    <button type="submit" class="btn btn-success mt-3">✅ Inscrire</button>
                    <a href="{{ route('classes.show', $classe->id) }}" class="btn btn-secondary mt-3">⬅ Retour</a>
                </form>
            </div>
        </div>
    </div>
@endsection
