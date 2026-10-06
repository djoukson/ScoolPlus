@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                📘 Liste des Classes – <span class="text-primary">{{ $anneeActive->nom ?? 'Année scolaire' }}</span>
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Gestion des Classes
                    </li>
                </ol>
            </div>
        </div>

        {{-- ✅ Grille de cartes modernes --}}
        <div class="row g-4">
            @forelse($classes as $classe)
                <div class="col-md-3 col-lg-3">
                    <div class="card shadow-lg border-0 rounded-4 h-100 class-card position-relative">
                        <div class="card-body d-flex flex-column">
                            {{-- Nom de la classe --}}
                            <h4 class="fw-bold text-dark mb-3">
                                <i class="fas fa-door-open text-primary me-2"></i>
                                {{ $classe->nom }}
                            </h4>

                            {{-- Nombre de matières --}}
                            {{-- Nombre de matières ou enseignant --}}
                            @if($classe->niveau->nom == 'Primaire')
                                <p class="text-muted mb-3">
                                    <i class="fas fa-chalkboard-teacher text-success me-1"></i>
                                    <span class="fw-semibold">Enseignant :</span>
                                    @if($classe->enseignant)
                                            <span class="badge bg-gradient-primary">
                                                {{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger">Aucun</span>
                                        @endif
                                    </p>
                            @else
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-book text-success me-1"></i>

                                        @if($classe->affectations_count !=0)
                                            <span class="fw-semibold">Matières :</span>
                                            <span class="badge bg-gradient-primary">
                                            {{ $classe->affectations_count }}
                                        </span>
                                        @else
                                            <span class="badge bg-danger">Aucune matière</span>
                                        @endif

                                </p>
                                {{-- Bouton vers matières --}}
                                <a href="{{ route('matieres.dansclasse', $classe->id) }}"
                                   class="btn btn-outline-info mt-auto w-100">
                                    <i class="fas fa-book-open me-1"></i> Voir les matières
                                </a>

                                {{-- Lien invisible qui rend toute la card cliquable --}}
                                <a href="{{ route('matieres.dansclasse', $classe->id) }}"
                                   class="stretched-link"></a>
                            @endif


                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p>Aucune classe trouvée pour l’année en cours.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection

@push('styles')
    <style>
        /* ✅ Texte en dégradé moderne */
        .text-gradient {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ✅ Carte moderne avec hover */
        .class-card {
            transition: all 0.3s ease-in-out;
        }
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* ✅ Bouton dégradé */
        .btn-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            border: none;
            color: #162e5e;
            font-weight: 500;
        }
        .btn-gradient-primary:hover {
            opacity: 0.9;
            color: #fff;
        }

    </style>
@endpush
