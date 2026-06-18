@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Breadcrumb pro --}}
        <div class="pro-breadcrumb mb-3">
            <div class="breadcrumb-title">
                👨‍🏫 Détail de l’Enseignant
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('enseignants.index') }}">Enseignants</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ strtoupper($enseignant->nom) }} {{ ucfirst($enseignant->prenom) }}
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            {{-- Carte Profil --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 text-center">
                    <div class="card-body">
                        <div class="avatar bg-success text-white rounded-circle mx-auto mb-3"
                             style="width:90px;height:90px;display:flex;align-items:center;justify-content:center;font-size:36px;">
                            {{ strtoupper(substr($enseignant->nom,0,1)) }}
                        </div>

                        <h5 class="fw-bold mb-1">
                            {{ strtoupper($enseignant->nom) }} {{ ucfirst($enseignant->prenom) }}
                        </h5>

                        <span class="badge bg-primary mb-2">
                        Enseignant
                    </span>

                        <p class="text-muted mb-1">
                            📞 {{ $enseignant->tel ?? '—' }}
                        </p>

                        <p class="text-muted mb-3">
                            📧 {{ $enseignant->email ?? '—' }}
                        </p>

                        <span class="badge {{ $enseignant->statut ? 'bg-success' : 'bg-danger' }}">
                        {{ $enseignant->statut ? 'Actif' : 'Inactif' }}
                    </span>
                    </div>
                </div>
            </div>

            {{-- Informations détaillées --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-dark text-white rounded-top-4">
                        <h6 class="mb-0">📚 Informations pédagogiques</h6>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <strong>🧑 Nom :</strong>
                                <div>{{ strtoupper($enseignant->nom) }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🧑 Prénom :</strong>
                                <div>{{ ucfirst($enseignant->prenom) }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🎓 Spécialité :</strong>
                                <div>{{ $enseignant->specialite ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>📘 Type d’enseignant :</strong>
                                <div>{{ $enseignant->type ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🚻 Sexe :</strong>
                                <div>
                                    {{ $enseignant->sexe == 'M' ? 'Masculin' : ($enseignant->sexe == 'F' ? 'Féminin' : '-') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <strong>🩸 Groupe sanguin :</strong>
                                <div>{{ $enseignant->groupesanguin ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>💰 Salaire mensuel :</strong>
                                <div class="fw-bold text-success">
                                    {{ number_format($enseignant->salaire_mensuel, 0, ',', ' ') }} FCFA
                                </div>
                            </div>

                            <div class="col-md-6">
                                <strong>📚 Niveau affecté :</strong>
                                <div>{{ optional($enseignant->niveau)->nom ?? '-' }}</div>
                            </div>

                            <div class="col-md-12">
                                <strong>🏠 Adresse :</strong>
                                <div>{{ $enseignant->adresse ?? '-' }}</div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-end bg-light">
                        <a href="{{ route('enseignants.index') }}" class="btn btn-secondary btn-sm">
                            ⬅ Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
