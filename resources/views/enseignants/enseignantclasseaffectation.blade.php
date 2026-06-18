{{-- enseignants/enseignantclasseaffectation.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
               Affectation : {{ $classe->nom }} /  <i class="fas fa-users text-success"></i>
                <strong>{{ $classe->inscriptions->count() }}</strong> élève(s)
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('enseignantclasse.index') }}">Enseignants</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Année scolaire : <strong>{{ $classe->annee?->nom ?? '-' }}
                    </li>
                </ol>
            </div>
        </div>

        {{-- Titre + bouton affectation --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Enseignants affectés
            </h4>

            {{-- Bouton dynamique --}}
            @if($classe->niveau->nom === 'Primaire')
                @if($classe->enseignant)
                    <button class="btn btn-warning shadow-sm" data-toggle="modal" data-target="#affectationModal">
                        <i class="fas fa-sync-alt"></i> Remplacer
                    </button>
                @else
                    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#affectationModal">
                        <i class="fas fa-user-plus"></i> Nouvelle affectation
                    </button>
                @endif
            @else
                <button class="btn btn-success shadow-sm" data-toggle="modal" data-target="#affectationModal">
                    <i class="fas fa-user-plus"></i> Ajouter des professeurs à la classe
                </button>
            @endif
        </div>

        {{-- Modal Bootstrap moderne --}}
        <div class="modal fade" id="affectationModal" tabindex="-1" aria-labelledby="affectationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header bg-gradient text-white" style="background: linear-gradient(90deg,#4facfe,#00f2fe);">
                        <h5 class="modal-title fw-bold" id="affectationModalLabel">
                            <i class="fas fa-user-plus"></i> Gérer l’affectation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">
                        <form id="affectationForm" method="POST" action="{{ route('affectationsdepuisenseignant.store') }}">
                            @csrf
                            <input type="hidden" name="classe_id" value="{{ $classe->id }}">

                            {{-- Sélection enseignant --}}
                            @if($classe->niveau->nom == 'Primaire')
                                <div class="mb-3">
                                    <label for="enseignant_id" class="form-label fw-bold">Enseignant</label>
                                    <select class="form-select shadow-sm" name="enseignant_id" id="enseignant_id" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($enseignants as $enseignant)
                                            <option value="{{ $enseignant->id }}">
                                                {{ $enseignant->nom }} {{ $enseignant->prenom }} ({{ $enseignant->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="mb-3">
                                    <label for="enseignant_id" class="form-label fw-bold">Professeur</label>
                                    <select class="form-select shadow-sm" name="enseignant_id" id="enseignant_id" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($professeurs as $professeur)
                                            <option value="{{ $professeur->id }}">
                                                {{ $professeur->nom }} {{ $professeur->prenom }} ({{ $professeur->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            @endif


                            {{-- Si secondaire / collège : Matière + Heures --}}
                            @if($classe->niveau->nom !== 'Primaire')
                                <div class="mb-3">
                                    <label for="matiere" class="form-label fw-bold">Matière</label>
                                    <select name="matiere_id" class="form-control" required>
                                        <option value="">-- Sélectionnez une matière --</option>
                                        @foreach($matieres as $matiere)
                                            <option value="{{ $matiere->id }}">{{ $matiere->nom }} - Coeff : {{$matiere->coefficient}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="heures_attribuees" class="form-label fw-bold">Heures attribuées</label>
                                    <input type="number" class="form-control shadow-sm" name="heures_attribuees" id="heures_attribuees" placeholder="Ex: 12" required>
                                </div>
                            @endif

                            {{-- Note facultative --}}
                            <div class="mb-3">
                                <label for="note" class="form-label fw-bold">Note (facultatif)</label>
                                <textarea class="form-control shadow-sm" name="note" id="note" rows="2"></textarea>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>




        {{-- Tableau des affectations --}}
        @if($classe->affectations->count() > 0 || $classe->enseignant)
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle">
                    <thead class="table-primary">
                    <tr>
                        @if($classe->niveau->nom !== 'Primaire' )
                        <th>#</th>
                        <th>Enseignant</th>
                        <th>Matière</th>
                        <th>Heures attribuées</th>
                        @else
                            <th>Enseignant</th>
                        @endif
                        <th>Note</th>
                        <th>Action</th>

                    </tr>
                    </thead>
                    <tbody>
                    {{-- Cas niveau primaire : enseignant lié directement à la classe --}}
                    @if($classe->niveau->nom === 'Primaire' && $classe->enseignant)
                        <tr>

                            <td>
                                <i class="fas fa-user"></i>
                                {{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}
                                <br>
                                <small class="text-muted">({{ $classe->enseignant->type }})</small>
                            </td>

                            <td>—</td>
                            <td>
                                {{-- Pour primaire --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-toggle="modal"
                                        data-target="#confirmRemoveModal"
                                        data-action="{{ route('primaryaffectations.remove', ['classe_id' => $classe->id]) }}"
                                        data-nom="{{ $classe->enseignant->nom }}"
                                        data-prenom="{{ $classe->enseignant->prenom }}">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </td>
                        </tr>
                    @endif

                    {{-- Autres niveaux : via table affectations --}}
                    @foreach($classe->affectations as $index => $affectation)
                        <tr class="fade-in">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <i class="fas fa-user"></i>
                                {{ $affectation->enseignant->nom }} {{ $affectation->enseignant->prenom }}
                                <br>
                                <small class="text-muted">({{ $affectation->enseignant->type }})</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $affectation->matiere->nom }}</span></td>
                            <td>{{ $affectation->heures_attribuees }} h</td>
                            <td>
                                @if($affectation->note)
                                    <span class="badge bg-success">{{ $affectation->note }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{-- Pour secondaire --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-toggle="modal"
                                        data-target="#confirmRemoveModal"
                                        data-action="{{ route('destroyaffectations.destroy', $affectation->id) }}"
                                        data-nom="{{ $affectation->enseignant->nom }}"
                                        data-prenom="{{ $affectation->enseignant->prenom }}">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning shadow-sm">
                <i class="fas fa-exclamation-circle"></i> Aucun enseignant affecté à cette classe.
            </div>
        @endif

    </div>
    <!-- Modal confirmation -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="confirmRemoveModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirmation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Êtes-vous sûr de vouloir <strong>retirer l’enseignant
                            <span id="enseignantName"></span></strong> de la classe ?
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <form id="confirmRemoveForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-user-minus"></i> Confirmer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#confirmRemoveModal').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                let action = button.data('action');
                let nom = button.data('nom');
                let prenom = button.data('prenom');

                let form = $(this).find('#confirmRemoveForm');
                form.attr('action', action);

                // Injecter le nom et prénom dans le span
                $(this).find('#enseignantName').text(nom + ' ' + prenom);
            });
        });
    </script>


    {{-- Petite animation CSS --}}
    <style>
        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection
