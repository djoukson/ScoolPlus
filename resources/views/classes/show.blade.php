@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2>Détails de la classe : {{ $classe->nom }}</h2>
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

        <a href="{{ route('classes.index') }}" class="btn btn-warning">⬅ Retour</a>
        <p style="margin-bottom: 10px"></p>
        <div class="card mb-3" style="background: linear-gradient(135deg, #fbacaf, #9cf1cb);">
            <div class="card-body">
                <p><strong>Niveau :</strong> {{ ucfirst($classe->niveau) }}</p>
                <p><strong>Année scolaire :</strong> {{ $classe->annee?->nom ?? '-' }}</p>

                @if($classe->niveau === 'primaire')
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body">

                            @if($classe->enseignant)
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}</h6>
                                        <small class="text-muted">Responsable de la classe</small>
                                    </div>

                                    <form action="{{ route('classes.retirerEnseignant', $classe->id) }}" method="POST"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cet enseignant ?')"
                                          class="ms-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Retirer
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-muted fst-italic mb-0">
                                    Aucun enseignant responsable n’a encore été affecté à cette classe.
                                </p>
                            @endif
                        </div>
                    </div>

                @else
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body">
                            <b style="color: #132488"> Professeurs Affectés</b>
                            @forelse($classe->affectations as $aff)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $aff->enseignant->nom }} {{ $aff->enseignant->prenom }}</strong>
                                        <small class="text-muted d-block">
                                            Matière : {{ $aff->matiere->nom }}
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary">
                                                Coef. {{ $aff->matiere->coefficient }}
                                            </span>
                                        <form action="{{ route('classe.retirerProfesseur', [$classe->id, $aff->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">❌</button>
                                        </form>

                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Aucun professeur affecté pour cette classe.</p>
                            @endforelse
                        </div>
                    </div>
                @endif


            </div>
        </div>

        <h4 class="mb-3 text-primary fw-bold">
            <i class="bi bi-gear-fill"></i> Actions disponibles
        </h4>

        <div class="row g-3">
            @if($classe->niveau === 'primaire')
                <div class="col-md-4">
                    <a href="{{ route('classes.affecterEnseignant', $classe->id) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 action-card">
                            <div class="card-body text-center">
                                <i class="bi bi-person-badge fs-1 text-primary"></i>
                                <h6 class="mt-3 fw-bold">Affecter un enseignant</h6>
                                <p class="text-muted small mb-0">
                                    Désigner un responsable unique pour cette classe
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <div class="col-md-4">
                    <a href="{{ route('classes.affecterProfesseurs', $classe->id) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 action-card">
                            <div class="card-body text-center">
                                <i class="bi bi-book-half fs-1 text-success"></i>
                                <h6 class="mt-3 fw-bold">Affecter des professeurs</h6>
                                <p class="text-muted small mb-0">
                                    Assigner des enseignants aux différentes matières
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            <div class="col-md-4">
                <a href="{{ route('classes.inscrireEleves', $classe->id) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 action-card">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill fs-1 text-warning"></i>
                            <h6 class="mt-3 fw-bold">Inscrire des élèves</h6>
                            <p class="text-muted small mb-0">
                                Ajouter de nouveaux élèves dans cette classe
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <style>
            .action-card {
                background: linear-gradient(135deg, #9ab0f3, #92f3cc);
                border-radius: 14px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.06);
                transition: all 0.35s ease;
                cursor: pointer;
            }

            .action-card:hover {
                transform: translateY(-6px) scale(1.02);
                background: linear-gradient(135deg, #4e73df, #1cc88a);
                color: #fff !important;
                box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            }

            .action-card:hover i,
            .action-card:hover h6,
            .action-card:hover p {
                color: #fff !important;
            }

            /* Animation subtile icône */
            .action-card i {
                transition: transform 0.3s ease;
            }

            .action-card:hover i {
                transform: scale(1.15) rotate(-5deg);
            }
        </style>

    </div>
@endsection
