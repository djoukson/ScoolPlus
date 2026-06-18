@extends('layouts.app')

@section('content')
    @php
        use App\Models\AnneesScolaire;
        $anneeActive = $anneeActive ?? (
            session('annee_id')
                ? AnneesScolaire::find(session('annee_id'))
                : AnneesScolaire::where('active', 1)->first()
        );
    @endphp

    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                📅 Gestion des Découpages
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#addDecoupageModal" @if(!$anneeActive) disabled @endif   class="btn btn-outline-info">
                    ➕  Nouveau service
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Ajouter des decoupages
                    </li>
                </ol>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Année scolaire</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($decoupages as $decoupage)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $decoupage->nom }}</td>
                        <td>{{ ucfirst($decoupage->type) }}</td>
                        <td>
                        <span class="badge bg-success">
                            {{ $decoupage->annee->nom }}
                        </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" data-toggle="modal"
                                    data-target="#editDecoupageModal{{ $decoupage->id }}">
                                ✏️
                            </button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal"
                                    data-target="#deleteDecoupageModal{{ $decoupage->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="editDecoupageModal{{ $decoupage->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <form method="POST" action="{{ route('decoupages.update', $decoupage) }}">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-white">
                                        <h5 class="modal-title">✏️ Modifier Découpage</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label>Type</label>
                                            <select name="type" id="type-{{ $decoupage->id }}" class="form-control filter-type" required>
                                                <option value="">-- Sélectionner le Type--</option>
                                                <option value="trimestre" {{ (old('type', $decoupage->type) == 'trimestre') ? 'selected' : '' }}>Trimestre</option>
                                                <option value="semestre" {{ (old('type', $decoupage->type) == 'semestre') ? 'selected' : '' }}>Semestre</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="nom">Découpage</label>
                                            <select name="nom" id="nom-{{ $decoupage->id }}" class="form-control filter-nom" required>
                                                <optgroup label="Trimestres" data-type="trimestre">
                                                    <option value="1er Trimestre" {{ old('nom', $decoupage->nom) == '1er Trimestre' ? 'selected' : '' }}>1er Trimestre</option>
                                                    <option value="2ème Trimestre" {{ old('nom', $decoupage->nom) == '2ème Trimestre' ? 'selected' : '' }}>2ème Trimestre</option>
                                                    <option value="3ème Trimestre" {{ old('nom', $decoupage->nom) == '3ème Trimestre' ? 'selected' : '' }}>3ème Trimestre</option>
                                                </optgroup>
                                                <optgroup label="Semestres" data-type="semestre">
                                                    <option value="1er Semestre" {{ old('nom', $decoupage->nom) == '1er Semestre' ? 'selected' : '' }}>1er Semestre</option>
                                                    <option value="2ème Semestre" {{ old('nom', $decoupage->nom) == '2ème Semestre' ? 'selected' : '' }}>2ème Semestre</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        <input type="hidden" name="annee_id" value="{{ $decoupage->annee_id }}">
                                        <p class="text-muted">Année scolaire : <strong>{{ $decoupage->annee?->nom ?? '—' }}</strong></p>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- DELETE MODAL -->
                    <div class="modal fade" id="deleteDecoupageModal{{ $decoupage->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('decoupages.destroy', $decoupage) }}" method="POST">
                                @csrf @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">🗑️ Confirmer la suppression</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p>Voulez-vous vraiment supprimer <strong>{{ $decoupage->nom }}</strong> ?</p>
                                        <p class="text-muted"><small>Cette action est irréversible.</small></p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="5" class="text-center">Aucun découpage défini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD MODAL -->
    <div class="modal fade" id="addDecoupageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('decoupages.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">➕ Nouveau Découpage</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Type</label>
                            <select id="typeSelect" name="type" class="form-control" required>
                                <option value="">-- Sélectionner le Type --</option>
                                <option value="trimestre" {{ old('type', $decoupage->type) == 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                                <option value="semestre" {{ old('type', $decoupage->type) == 'semestre' ? 'selected' : '' }}>Semestre</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Groupe</label>
                            <select id="groupeSelect" name="nom" class="form-control" required>
                                <option value="">-- Sélectionner le Groupe --</option>
                            </select>
                        </div>

                        @if($anneeActive)
                            <input type="hidden" name="annee_id" value="{{ $anneeActive->id }}">
                            <p class="text-muted">Année scolaire : <strong>{{ $anneeActive->nom }}</strong></p>
                        @else
                            <p class="text-danger">Aucune année active trouvée. Impossible de créer un découpage.</p>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" @if(!$anneeActive) disabled @endif>💾 Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            let typeSelect   = document.getElementById("typeSelect");
            let groupeSelect = document.getElementById("groupeSelect");

            // Valeurs existantes depuis la base
            let savedType  = "{{ old('type', $decoupage->type) }}";
            let savedGroupe = "{{ old('nom', $decoupage->nom) }}";

            // Groupes disponibles
            let groupes = {
            trimestre: [
        {value: "1er Trimestre", text: "1er Trimestre"},
        {value: "2eme Trimestre", text: "2ème Trimestre"},
        {value: "3eme Trimestre", text: "3ème Trimestre"}
            ],
            semestre: [
        {value: "1er Semestre", text: "1er Semestre"},
        {value: "2eme Semestre", text: "2ème Semestre"}
            ]
        };

            function chargerGroupes(type) {
            groupeSelect.innerHTML = '<option value="">-- Sélectionner le Groupe --</option>';

            if (groupes[type]) {
            groupes[type].forEach(opt => {
            let option = document.createElement("option");
            option.value = opt.value;
            option.text = opt.text;
            if (opt.value === savedGroupe) {
            option.selected = true; // garder la valeur déjà en DB
        }
            groupeSelect.appendChild(option);
        });
        }
        }

            // Chargement initial (mode édition)
            if (savedType) {
            chargerGroupes(savedType);
        }

            // Quand on change le type
            typeSelect.addEventListener("change", function () {
            chargerGroupes(this.value);
        });
        });
    </script>
