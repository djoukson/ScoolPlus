@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <a href="{{ route('classes.show', $classe->id) }}" class="btn btn-warning mt-3">⬅ Retour</a>


        <h3>Affecter un Enseignant Responsable à la Classe : {{ $classe->nom }}</h3>

        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('classes.storeEnseignant', $classe->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="enseignant_id">Choisir un Enseignant</label>
                        <select name="enseignant_id" id="enseignant_id" class="form-control" required>
                            <option value="">-- Sélectionnez un enseignant --</option>
                            @foreach($enseignants as $enseignant)
                                <option value="{{ $enseignant->id }}">
                                    {{ $enseignant->nom }} {{ $enseignant->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        ✅ Affecter
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
