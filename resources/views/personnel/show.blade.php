@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Fil d’Ariane --}}
        <div class="pro-breadcrumb mb-3">
            <div class="breadcrumb-title">
                👤 Détail du Personnel Administratif
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('personnel.index') }}">Personnel</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ strtoupper($personnel->nom) }} {{ ucfirst($personnel->prenom) }}
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            {{-- Carte Profil --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 text-center">
                    <div class="card-body">
                        <div class="avatar bg-primary text-white rounded-circle mx-auto mb-3"
                             style="width:90px;height:90px;display:flex;align-items:center;justify-content:center;font-size:36px;">
                            {{ strtoupper(substr($personnel->nom,0,1)) }}
                        </div>

                        <h5 class="fw-bold mb-1">
                            {{ strtoupper($personnel->nom) }} {{ ucfirst($personnel->prenom) }}
                        </h5>

                        <span class="badge bg-info mb-2">
                        {{ $personnel->poste ?? 'Personnel Administratif' }}
                    </span>

                        <p class="text-muted mb-1">
                            📞 {{ $personnel->tel ?? '—' }}
                        </p>

                        <p class="text-muted mb-3">
                            📧 {{ $personnel->email ?? '—' }}
                        </p>

                        <span class="badge {{ $personnel->statut ? 'bg-success' : 'bg-danger' }}">
                        {{ $personnel->statut ? 'Actif' : 'Inactif' }}
                    </span>
                    </div>
                </div>
            </div>

            {{-- Informations détaillées --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-dark text-white rounded-top-4">
                        <h6 class="mb-0">📋 Informations générales</h6>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <strong>🧑 Nom :</strong>
                                <div>{{ strtoupper($personnel->nom) }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🧑 Prénom :</strong>
                                <div>{{ ucfirst($personnel->prenom) }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🏢 Poste / Fonction :</strong>
                                <div>{{ $personnel->poste ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>🚻 Sexe :</strong>
                                <div>
                                    {{ $personnel->sexe == 'M' ? 'Masculin' : ($personnel->sexe == 'F' ? 'Féminin' : '-') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <strong>🩸 Groupe sanguin :</strong>
                                <div>{{ $personnel->groupesanguin ?? '-' }}</div>
                            </div>

                            <div class="col-md-6">
                                <strong>💰 Salaire mensuel :</strong>
                                <div class="fw-bold text-success">
                                    {{ number_format($personnel->salaire, 0, ',', ' ') }} FCFA
                                </div>
                            </div>

                            <div class="col-md-12">
                                <strong>🏠 Adresse :</strong>
                                <div>{{ $personnel->adresse ?? '-' }}</div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-end bg-light">
                        <a href="{{ route('personnel.index') }}" class="btn btn-secondary btn-sm">
                            ⬅ Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
