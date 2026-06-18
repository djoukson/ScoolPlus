@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Classes & Enseignants
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Enseignants/Professeurs : Année {{ $annee_courante?->nom ?? 'Non définie' }}
                    </li>
                </ol>
            </div>
        </div>


        {{-- ✅ Grille des classes --}}
        <div class="row g-4">
            @foreach($classes as $classe)
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-lg h-100 rounded-4 hover-zoom">
                        <div class="card-body d-flex flex-column">

                            {{-- Nom + niveau --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-secondary">
                                    <i class="far fa-building me-2"></i> {{ $classe->nom }}
                                </h5>
                                <span class="badge px-3 py-2 {{ $classe->niveau->nom === 'Primaire' ? 'bg-success' : 'bg-info' }}">
                            {{ ucfirst($classe->niveau->nom) }}
                        </span>
                            </div>

                            {{-- Élèves --}}
                            <p class="mb-2 text-muted">
                                <i class="fas fa-users text-primary me-1"></i>
                                <strong>{{ $classe->inscriptions_count }}</strong> élève(s)
                            </p>

                            @if($classe->niveau->nom !== 'Primaire')
                                <p class="mb-2">
                                    <i class="fas fa-user text-success me-1"></i>
                                    <strong>Titulaire :</strong>
                                    @if($classe->titulaire)
                                        {{ $classe->titulaire->enseignant->nom }} {{ $classe->titulaire->enseignant->prenom }}

                                        <!-- Bouton pour ouvrir le modal -->
                                        <button type="button" class="btn btn-link p-0 text-danger ms-2" data-toggle="modal" data-target="#confirmDeleteModal{{ $classe->id }}">
                                            <i class="fas fa-user-minus"></i>
                                        </button>

                                        <!-- Modal de confirmation -->
                                <div class="modal fade" id="confirmDeleteModal{{ $classe->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $classe->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel{{ $classe->id }}">Confirmer la suppression</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                Voulez-vous vraiment retirer le titulaire ?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                <form action="{{ route('titulaires.destroy', $classe->titulaire->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Oui, retirer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                <span class="text-danger">Non défini</span>
                                @endif
                                </p>
                            @endif


                            {{-- Enseignant(s) affecté(s) --}}
                            @if($classe->niveau->nom === 'Primaire')
                                <p class="mb-2">
                                    <i class="fas fa-user text-success me-1"></i>
                                    <strong>Responsable :</strong>
                                    @if($classe->enseignant)
                                        {{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}
                                    @else
                                        <span class="text-danger">Non affecté</span>
                                    @endif
                                </p>
                            @else
                                <p class="mb-2">
                                    <i class="fas fa-chalkboard text-info me-1"></i>
                                    @if($classe->affectations->count() > 0)
                                        <strong class="text-success">{{ $classe->affectations->count() }} prof(s) affecté(s)</strong>
                                    @else
                                        <span class="text-danger">Aucun professeur affecté</span>
                                    @endif
                                </p>
                            @endif

                            {{-- Boutons --}}
                            <div class="mt-auto d-flex justify-content-end gap-2">
                                <a href="{{ route('enseignantclasseaffectation.index', $classe->id) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                                    <i class="fas fa-user-plus me-1"></i> Affectations
                                </a>

                                @if($classe->niveau->nom !== 'Primaire')
                                    <button class="btn btn-sm btn-outline-success rounded-pill shadow-sm"
                                            data-toggle="modal" data-target="#modalTitulaire{{ $classe->id }}">
                                        <i class="fas fa-user-tie me-1"></i> +Titulaire
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Modal pour choisir le titulaire --}}
                @if($classe->niveau->nom !== 'Primaire')
                    <div class="modal fade" id="modalTitulaire{{ $classe->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('titulaires.store') }}">
                                @csrf
                                <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                                <input type="hidden" name="annee_id" value="{{ $annee_courante->id }}">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Choisir le titulaire - {{ $classe->nom }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <select name="enseignant_id" class="form-select" required>
                                            <option value="">-- Sélectionner un enseignant --</option>
                                            @foreach($enseignants as $enseignant)
                                                @php
                                                    $estTitulaire = \App\Models\Titulaire::where('enseignant_id', $enseignant->id)
                                                        ->where('annee_id', $annee_courante->id)
                                                        ->exists();
                                                @endphp

                                                @if($enseignant->niveau && $enseignant->niveau->nom !== 'Primaire')
                                                    <option value="{{ $enseignant->id }}"
                                                        {{ $estTitulaire ? 'disabled style=color:#ccc' : '' }}>
                                                        {{ $enseignant->nom }} {{ $enseignant->prenom }}
                                                        {{ $estTitulaire ? '(Déjà titulaire)' : '' }}
                                                    </option>
                                                @endif
                                            @endforeach

                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">Enregistrer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .hover-zoom {
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .hover-zoom:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 12px 25px rgba(0,0,0,0.12) !important;
        }
        .btn-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            color: #fff;
            border: none;
        }
        .btn-gradient-primary:hover {
            opacity: 0.9;
            color: #fff;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #36b9cc, #4e73df);
        }
    </style>
@endpush
