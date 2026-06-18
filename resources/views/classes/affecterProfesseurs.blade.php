@extends('layouts.app')

@section('content')

    <div class="container py-4">
        <a href="{{ route('classes.show', $classe->id) }}" class="btn btn-secondary">⬅ Retour</a>

        <h3>Affecter des Professeurs aux Matières - Classe : {{ $classe->nom }}</h3>

        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('classes.storeProfesseursdansclasse', $classe->id) }}" method="POST">
                    @csrf
                    <div id="professeurs-container">
                        <div class="row mb-3 professeur-row">
                            <div class="col-md-5">
                                <label>Matière</label>
                                <select name="matieres[]" class="form-control" required>
                                    <option value="">-- Sélectionnez une matière --</option>
                                    @foreach($matieres as $matiere)
                                        <option value="{{ $matiere->id }}">{{ $matiere->nom }} - Coeff : {{$matiere->coefficient}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Professeur</label>
                                <select name="professeurs[]" class="form-control" required>
                                    <option value="">-- Sélectionnez un professeur --</option>
                                    @foreach($professeurs as $prof)
                                        <option value="{{ $prof->id }}">{{ $prof->nom }} {{ $prof->prenom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Heures</label>
                                <input type="number" name="heures[]" class="form-control" min="0" step="1" placeholder="ex: 3">
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Supprimer cette ligne">✖</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-success mb-3" onclick="ajouterLigne()">
                        ➕ Ajouter une autre matière/professeur
                    </button>

                    <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function ajouterLigne() {
            const container = document.getElementById('professeurs-container');
            const prototype = document.querySelector('.professeur-row');
            const newRow = prototype.cloneNode(true);

            // Réinitialiser selects et input dans la copie
            newRow.querySelectorAll('select').forEach(s => s.value = '');
            newRow.querySelectorAll('input').forEach(i => i.value = '');

            container.appendChild(newRow);
            attachRemoveHandlers(); // rattacher listeners au nouveau bouton remove
        }

        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-row').forEach(btn => {
                // éviter d'ajouter plusieurs fois le listener
                if (!btn.dataset.listenerAttached) {
                    btn.addEventListener('click', function () {
                        const row = this.closest('.professeur-row');
                        // on garde au moins une ligne
                        const rows = document.querySelectorAll('.professeur-row');
                        if (rows.length > 1) {
                            row.remove();
                        } else {
                            // reset si unique
                            row.querySelectorAll('select').forEach(s => s.value = '');
                            row.querySelectorAll('input').forEach(i => i.value = '');
                        }
                    });
                    btn.dataset.listenerAttached = '1';
                }
            });
        }

        // initial attach
        attachRemoveHandlers();
    </script>
@endsection
