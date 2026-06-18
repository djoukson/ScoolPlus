@extends('layouts.app')

@section('title', 'Profil Élève')
@section('page-title', 'Profil de ' . $eleve->nom . ' ' . $eleve->prenom)

@section('content')
    <div class="container py-4">
        <!-- En-tête -->
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h4 class="text-primary mb-0">
                <i class="fas fa-user-graduate me-2"></i>
                Profil de {{ $eleve->nom }} {{ $eleve->prenom }}
            </h4>
            <div>
                <a href="{{ route('elevesparannee.index') }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
                <a href="{{ route('eleves.print', $eleve->id) }}" target="_blank" class="btn btn-sm btn-success shadow-sm">
                    <i class="fas fa-print me-1"></i> Imprimer le dossier
                </a>
            </div>
        </div>

        <!-- Section profil -->
        <div class="row g-4 align-items-center mb-4">
            <!-- Profil élève -->
            <div class="col-md-3 text-center">
                @php
                    $imagePath = $eleve->sexe == 'M'
                        ? asset('dist/img/man.png')
                        : asset('dist/img/woman.png');
                @endphp

                <div class="card border-0 shadow-lg rounded-1 p-3 h-100">
                    <div class="position-relative">
                        <img src="{{ $eleve->imglink ? asset($eleve->imglink) : $imagePath }}"
                             alt="Photo de {{ $eleve->nom }}"
                             class="img-fluid rounded-circle border border-3 border-light shadow-sm mb-3"
                             width="130" height="130"
                             style="object-fit: cover; transition: transform 0.3s ease;">

                        <!-- Formulaire d’upload -->
                        <form action="{{ route('eleves.uploadImage', $eleve->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="photo" class="form-control form-control-sm mb-2" accept="image/*" required>
                            <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                                <i class="fas fa-upload me-1"></i> Mettre à jour la photo
                            </button>
                        </form>


                        <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill
                            {{ $eleve->sexe == 'M' ? 'bg-primary' : 'bg-danger' }}"
                              style="font-size: 0.75rem; transform: translate(20%, 20%);">
                    {{ $eleve->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                </span>
                    </div>

                    <h5 class="text-primary mt-2 mb-1 fw-bold text-uppercase">
                        {{ $eleve->nom }} {{ ucfirst($eleve->prenom) }}
                    </h5>

                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-id-card me-1"></i> Matricule : {{ $eleve->matricule }}
                    </small>

                    <div class="d-flex justify-content-center">
                            <a href="{{ route('eleves.carte', $eleve->id) }}" class="btn btn-labeled btn-success btn-sm">
                                <span class="btn-label"><i class="fa fa-id-card"></i></span> Voir la Carte
                            </a>
                    </div>
                </div>
            </div>

            <!-- Infos élève -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                    <div class="row g-4">

                        <!-- Classe -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 h-100">
                                <div class="icon-box text-primary fs-4">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Classe</small>
                                    <div class="fw-semibold fs-6 text-dark">
                                        {{ $eleve->classeActuelle?->classe?->nom ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date naissance -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 h-100">
                                <div class="icon-box text-primary fs-4">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Date de naissance</small>
                                    <div class="fw-semibold fs-6 text-dark">
                                        {{ $eleve->date_naissance ? $eleve->date_naissance->format('d/m/Y') : '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 h-100">
                                <div class="icon-box text-primary fs-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Adresse</small>
                                    <div class="fw-semibold fs-6 text-dark">
                                        {{ $eleve->adresse ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nationalité -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 h-100">
                                <div class="icon-box text-primary fs-4">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Nationalité</small>
                                    <div class="fw-semibold fs-6 text-dark">
                                        {{ $eleve->nationalite ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observation -->
                        <div class="col-md-12">
                            <div class="p-4 bg-white border rounded-3 shadow-sm">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-sticky-note text-primary me-2"></i>
                                    <span class="fw-semibold text-dark">Observation</span>
                                </div>
                                <p class="mb-0 text-muted fst-italic">
                                    {{ $eleve->observation ?: 'Aucune observation enregistrée.' }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <!-- Onglets -->
        <ul class="nav nav-tabs mb-3" id="profilTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="infos-tab" data-toggle="tab" data-target="#infos" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i> Informations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="parents-tab" data-toggle="tab" data-target="#parents" type="button" role="tab">
                    <i class="fas fa-users me-1"></i> Parents
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bourse-tab" data-toggle="tab" data-target="#bourse" type="button" role="tab">
                    <i class="fas fa-hand-holding-usd me-1"></i> Statut des bourses
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="profilTabsContent">
            <!-- Onglet Infos -->
            <div class="tab-pane fade show active" id="infos" role="tabpanel" aria-labelledby="infos-tab">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4">
                        <h5 class="text-primary fw-bold mb-4">
                            <i class="fas fa-user-circle me-2"></i> Informations personnelles
                        </h5>

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                <tr>
                                    <th class="text-muted w-25">
                                        <i class="fas fa-user me-2 text-primary"></i> Nom complet
                                    </th>
                                    <td class="fw-semibold text-dark">
                                        {{ strtoupper($eleve->nom) }} {{ ucfirst($eleve->prenom) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-id-card me-2 text-primary"></i> Matricule
                                    </th>
                                    <td class="fw-semibold text-dark">
                                        {{ $eleve->matricule }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-venus-mars me-2 text-primary"></i> Sexe
                                    </th>
                                    <td>
                                <span class="badge rounded-pill {{ $eleve->sexe == 'M' ? 'bg-primary' : 'bg-danger' }} px-3 py-2 shadow-sm">
                                    {{ $eleve->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                                </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i> Date de naissance
                                    </th>
                                    <td class="fw-semibold text-dark">
                                        {{ $eleve->date_naissance ? $eleve->date_naissance->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-map-pin me-2 text-primary"></i> Lieu de naissance
                                    </th>
                                    <td class="fw-semibold text-dark">
                                        {{ $eleve->lieudenaissance ?? '—' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i> Adresse
                                    </th>
                                    <td class="fw-semibold text-dark">
                                        {{ $eleve->adresse ?? '—' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted">
                                        <i class="fas fa-user-check me-2 text-primary"></i> Statut
                                    </th>
                                    <td>
                                        @if ($eleve->statut == 1)
                                            <span class="badge bg-success px-3 py-2 shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> Actif
                                    </span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2 shadow-sm">
                                        <i class="fas fa-times-circle me-1"></i> Désactivé
                                    </span>
                                        @endif
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Onglet Parents -->
            <div class="tab-pane fade" id="parents" role="tabpanel" aria-labelledby="parents-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary mb-0">
                        <i class="fas fa-users me-2"></i> Informations des Parents
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editParentsModal">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </button>
                </div>

{{--                @php--}}
{{--                    $parent = $eleve->parentEleve;--}}
{{--                @endphp--}}

                <div class="row">
                    <!-- Père -->
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border bg-light rounded">
                            <h6 class="text-primary"><i class="fas fa-male me-2"></i>Père</h6>
                            <table class="table table-borderless mb-0 small">
                                <tr><th>Nom complet :</th><td>{{ $parent?->pere_nom ?? '—' }}</td></tr>
                                <tr><th>Téléphone :</th><td>{{ $parent?->pere_tel ?? '—' }}</td></tr>
                                <tr><th>Profession :</th><td>{{ $parent?->pere_profession ?? '—' }}</td></tr>
                                <tr><th>Email :</th><td>{{ $parent?->pere_email ?? '—' }}</td></tr>
                                <tr><th>Adresse :</th><td>{{ $parent?->pere_adresse ?? '—' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Mère -->
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border bg-light rounded">
                            <h6 class="text-danger"><i class="fas fa-female me-2"></i>Mère</h6>
                            <table class="table table-borderless mb-0 small">
                                <tr><th>Nom complet :</th><td>{{ $parent?->mere_nom ?? '—' }}</td></tr>
                                <tr><th>Téléphone :</th><td>{{ $parent?->mere_tel ?? '—' }}</td></tr>
                                <tr><th>Profession :</th><td>{{ $parent?->mere_profession ?? '—' }}</td></tr>
                                <tr><th>Email :</th><td>{{ $parent?->mere_email ?? '—' }}</td></tr>
                                <tr><th>Adresse :</th><td>{{ $parent?->mere_adresse ?? '—' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Édition Parents -->
                <div class="modal fade" id="editParentsModal" tabindex="-1" role="dialog" aria-labelledby="editParentsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow-lg rounded-lg">
                            <!-- En-tête -->
                            <div class="modal-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="modal-title font-weight-bold mb-0">
                                    <i class="fas fa-user-edit mr-2"></i> Mettre à jour les informations des parents
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <!-- Formulaire -->
                            <form method="POST" action="{{ route('parent-eleve.update', $eleve->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-body bg-light">
                                    <div class="row">
                                        <!-- Informations du Père -->
                                        <div class="col-md-6 mb-4">
                                            <div class="border rounded p-3 bg-white h-100 shadow-sm">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-male mr-2"></i>Père
                                                </h6>

                                                <div class="form-group">
                                                    <label class="small text-muted">Nom complet</label>
                                                    <input type="text" name="pere_nom" value="{{ $parent?->pere_nom }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Téléphone</label>
                                                    <input type="text" name="pere_tel" value="{{ $parent?->pere_tel }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Profession</label>
                                                    <input type="text" name="pere_profession" value="{{ $parent?->pere_profession }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Email</label>
                                                    <input type="email" name="pere_email" value="{{ $parent?->pere_email }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Adresse</label>
                                                    <input type="text" name="pere_adresse" value="{{ $parent?->pere_adresse }}" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Informations de la Mère -->
                                        <div class="col-md-6 mb-4">
                                            <div class="border rounded p-3 bg-white h-100 shadow-sm">
                                                <h6 class="text-danger mb-3">
                                                    <i class="fas fa-female mr-2"></i>Mère
                                                </h6>

                                                <div class="form-group">
                                                    <label class="small text-muted">Nom complet</label>
                                                    <input type="text" name="mere_nom" value="{{ $parent?->mere_nom }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Téléphone</label>
                                                    <input type="text" name="mere_tel" value="{{ $parent?->mere_tel }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Profession</label>
                                                    <input type="text" name="mere_profession" value="{{ $parent?->mere_profession }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Email</label>
                                                    <input type="email" name="mere_email" value="{{ $parent?->mere_email }}" class="form-control form-control-sm">
                                                </div>

                                                <div class="form-group">
                                                    <label class="small text-muted">Adresse</label>
                                                    <input type="text" name="mere_adresse" value="{{ $parent?->mere_adresse }}" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pied du modal -->
                                <div class="modal-footer bg-white border-0 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i> Annuler
                                    </button>
                                    <button type="submit" class="btn btn-primary shadow-sm">
                                        <i class="fas fa-save mr-1"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



            </div>
            <!-- Onglet Bourse -->
            <div class="tab-pane fade" id="bourse" role="tabpanel" aria-labelledby="bourse-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary mb-0">
                        <i class="fas fa-hand-holding-usd me-2"></i> Statut des bourses
                    </h6>
                </div>

                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">
                        @if($attribution && $attribution->bourse)
                            @php
                                $bourse = $attribution->bourse;
                            @endphp

                            <table class="table table-borderless mb-3 small">
                                <tr>
                                    <th>Statut :</th>
                                    <td><span class="badge bg-success">Boursier</span></td>
                                </tr>
                                <tr>
                                    <th>Nom de la bourse :</th>
                                    <td>{{ $bourse->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Description :</th>
                                    <td>{{ $bourse->description ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Date d’attribution :</th>
                                    <td>{{ $attribution->date_attribution ? \Carbon\Carbon::parse($attribution->date_attribution)->format('d/m/Y') : '—' }}</td>
                                </tr>
                                <tr>
                                    <th>État :</th>
                                    <td>
                                        @if($attribution->isActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- 🎯 Liste des frais et pourcentages de réduction --}}
                            @if($bourse->frais->count() > 0)
                                <h6 class="fw-bold text-muted mt-4 mb-2">Détails de la couverture :</h6>
                                <table class="table table-sm table-striped small">
                                    <thead>
                                    <tr class="bg-light">
                                        <th>Frais concernés</th>
                                        <th>Pourcentage couvert</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($bourse->frais as $frais)
                                        <tr>
                                            <td>{{ $frais->libelle }}</td>
                                            <td>{{ number_format($frais->pivot->pourcentage, 2) }} %</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted text-center mt-3">Aucun frais associé à cette bourse.</p>
                            @endif

                        @else
                            <div class="text-center py-3">
                                <span class="badge bg-danger">Non boursier</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    <style>
        .nav-tabs .nav-link.active {
            background: #007bff;
            color: #fff !important;
        }
        .nav-tabs .nav-link {
            color: #555;
            font-weight: 500;
        }
        th {
            width: 180px;
        }
    </style>
@endsection
