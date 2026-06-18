@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Matières de la classe : {{ $classe->nom }}
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <a href="#" data-toggle="modal" data-target="#affecterMatiereModal"
                   class="btn btn-outline-info">
                    ➕ Affectation des Matières
                </a>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('matieres.matiereparclasse') }}" >
                            ⬅ Retour aux classes
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        Matières de la classe : {{ $classe->nom }}
                    </li>
                </ol>
            </div>
        </div>


        {{-- ✅ Liste des matières --}}
        <div class="row g-4">
            @if($classe->niveau->nom == 'Primaire')
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-2">
                                <i class="fas fa-user-tie text-primary me-2"></i>
                                Enseignant de la classe
                            </h5>

                            @if($classe->enseignant)
                                <p class="card-text text-muted mb-1">
                                    Nom & Prénom :
                                    <strong>{{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}</strong>
                                </p>
                                <p class="card-text text-muted mb-0">
                                    Type :
                                    <strong>{{ $classe->enseignant->type ?? '—' }}</strong>
                                </p>

                                <!-- Bouton retirer enseignant -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-circle position-absolute top-0 end-0 m-2"
                                        data-toggle="modal"
                                        data-target="#removeEnseignantModal">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Modal confirmation suppression enseignant -->
                                <div class="modal fade" id="removeEnseignantModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 shadow-lg border-0">
                                            <div class="modal-header bg-danger text-white rounded-top-4">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                                        aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p class="mb-3">Voulez-vous vraiment <strong>retirer</strong> l’enseignant de cette classe ?</p>
                                                <h6 class="fw-bold text-dark">{{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}</h6>
                                                <p class="text-muted small mb-0">Cette action est irréversible.</p>
                                            </div>
                                            <div class="modal-footer justify-content-center border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill px-4"
                                                        data-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <form action="{{ route('classes.removeEnseignant', $classe->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger rounded-pill px-4 shadow-sm">
                                                        Retirer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Aucun enseignant n’est encore affecté à cette classe.
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @else
                @forelse($classe->affectations as $affectation)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 hover-card position-relative">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark mb-2">
                                    <i class="fas fa-book text-success me-2"></i>
                                    {{ $affectation->matiere->nom ?? 'Matière inconnue' }}
                                </h5>

                                <p class="card-text text-muted mb-1">
                                    Professeur :
                                    <strong>{{ $affectation->enseignant->nom.' '.$affectation->enseignant->prenom ?? 'N/A' }}</strong>
                                </p>

                                <p class="card-text text-muted mb-1">
                                    Coefficient :
                                    <strong>{{ $affectation->matiere->coefficient ?? 'N/A' }}</strong>
                                </p>

                                <p class="card-text text-muted mb-1">
                                    Heures attribuées :
                                    <strong>{{ $affectation->heures_attribuees ?? '—' }}</strong>
                                </p>
                            </div>

                            <!-- Bouton supprimer -->
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger rounded-circle position-absolute top-0 end-0 m-2"
                                    data-toggle="modal"
                                    data-target="#deleteModal{{ $affectation->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal de confirmation -->
                    <div class="modal fade" id="deleteModal{{ $affectation->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 shadow-lg border-0">
                                <div class="modal-header bg-danger text-white rounded-top-4">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                            aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="mb-3">Voulez-vous vraiment <strong>retirer</strong> la matière :</p>
                                    <h6 class="fw-bold text-dark">{{ $affectation->matiere->nom }}</h6>
                                    <p class="text-muted small mb-0">Cette action est irréversible.</p>
                                </div>
                                <div class="modal-footer justify-content-center border-0">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                                            data-dismiss="modal">
                                        Annuler
                                    </button>
                                    <form action="{{ route('desaffectations.destroy', $affectation->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-danger rounded-pill px-4 shadow-sm">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-info-circle"></i>
                            Aucune matière n’est affectée à cette classe.
                        </div>
                    </div>
                @endforelse
            @endif
        </div>



    </div>

    <!-- ✅ Modal Affectation Matières -->
    <div class="modal fade" id="affecterMatiereModal" tabindex="-1" aria-labelledby="affecterMatiereModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <!-- Header -->
                <div class="modal-header bg-gradient text-white rounded-top-4"
                     style="background: linear-gradient(45deg, #0d6efd, #6610f2);">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="affecterMatiereModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nouvelle affectation – <span class="ms-1 text-warning">{{ $classe->nom }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('classes.affecterMatieres', $classe->id) }}">
                    @csrf
                    <div class="modal-body p-4">

                        <p class="text-muted mb-4">
                            Remplissez les champs ci-dessous pour affecter des matières à la classe <strong>{{ $classe->nom }}</strong>.
                        </p>

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
                            <!-- Conteneur pour les lignes dynamiques -->
                            <div id="affectation-container">

                                <div class="row g-3 align-items-end affectation-row">
                                    <!-- Matière -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-book text-primary me-2"></i> Matière
                                        </label>
                                        <select name="matieres[]" class="form-select matiere-select" required>
                                            <option value="" disabled selected>-- Choisissez la matière --</option>
                                            @foreach($toutesMatieres as $matiere)
                                                <option value="{{ $matiere->id }}">{{ $matiere->nom }} (Coef. {{ $matiere->coefficient }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Professeur -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Professeur</label>
                                        <select name="enseignant_id[]" class="form-select enseignant-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($professeurs as $professeur)
                                                <option value="{{ $professeur->id }}">{{ $professeur->nom }} {{ $professeur->prenom }} ({{ $professeur->type }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Heures -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-clock text-danger me-2"></i> Heures
                                        </label>
                                        <input type="number" name="heures_attribuees[]" class="form-control" min="1" placeholder="Ex: 20" required>
                                    </div>

                                    <!-- Bouton Ajouter -->
                                    <div class="col-md-1 d-grid">
                                        <button type="button" class="btn btn-success btn-add-row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        @endif

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer d-flex justify-content-between px-4 py-3 border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="fas fa-check-circle me-1"></i> Valider l’affectation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                function initSelect2(row) {
                    row.find('.matiere-select, .enseignant-select').select2({
                        width: '100%',
                        placeholder: 'Sélectionnez',
                        allowClear: true
                    });
                }

                // Initialiser Select2 sur la première ligne
                initSelect2($('#affectation-container .affectation-row'));

                // Ajouter une nouvelle ligne
                $(document).on('click', '.btn-add-row', function () {
                    let row = $(this).closest('.affectation-row');

                    // Détruire Select2 avant clonage pour éviter les doublons
                    row.find('.matiere-select, .enseignant-select').select2('destroy');

                    let newRow = row.clone(); // clone sans true
                    newRow.find('select, input').val(''); // vider les champs

                    row.after(newRow);

                    // Réinitialiser Select2 sur les deux lignes
                    initSelect2(row);
                    initSelect2(newRow);
                });
            });
        </script>

    @endpush


@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        /* Custom Select2 */
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border-radius: 0.75rem;
            border: 1px solid #ced4da;
            min-height: 55px;
            padding: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .select2-container--default .select2-selection__choice {
            background-color: #0d6efd;
            color: #fff;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .select2-container--default .select2-selection__choice__remove {
            margin-right: 6px;
            color: #ffc107;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#matieres').select2({
                placeholder: "Sélectionnez une ou plusieurs matières",
                allowClear: true,
                width: '100%',
            });
            $('#enseignant_id').select2({
                placeholder: "Choisissez un enseignant",
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endpush

