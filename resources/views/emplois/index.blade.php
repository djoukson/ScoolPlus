@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Emplois du temps : {{ $annee_courante?->nom ?? 'Non définie' }}
            </div>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Emplois du temps
                    </li>
                </ol>
            </div>
        </div>


        {{-- ✅ Tableau professionnel --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-gradient-primary text-white">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th>Nom de la classe</th>
                            <th>Niveau</th>
                            <th>Nombre d’élèves</th>
                            <th>Enseignant(s)</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($classes as $index => $classe)
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td class="fw-semibold text-dark">
                                    <i class="far fa-building text-secondary me-1"></i>
                                    {{ $classe->nom }}
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($classe->niveau->nom) {
                                            'Primaire' => 'bg-success',
                                            'College'  => 'bg-info',
                                            'Lycee'    => 'bg-warning',
                                            default    => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge px-3 py-2 {{ $badgeClass }}">
                                        {{ ucfirst($classe->niveau->nom) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-users text-primary me-1"></i>
                                    <strong>{{ $classe->inscriptions_count }}</strong>
                                </td>
                                <td>
                                    @if($classe->niveau->nom === 'Primaire')
                                        @if($classe->enseignant)
                                            <i class="fas fa-user text-success me-1"></i>
                                            {{ $classe->enseignant->nom }} {{ $classe->enseignant->prenom }}
                                        @else
                                            <span class="text-danger">Non affecté</span>
                                        @endif
                                    @else
                                        @if($classe->affectations->count() > 0)
                                            <i class="fas fa-chalkboard text-info me-1"></i>
                                            <strong class="text-success">{{ $classe->affectations->count() }} prof(s)</strong>
                                        @else
                                            <span class="text-danger">Aucun professeur</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('emplois.create', $classe->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                                        <i class="fas fa-clock me-1"></i> Emploi du temps
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i> Aucune classe enregistrée.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        /* === TABLEAU PROFESSIONNEL === */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        thead {
            background: linear-gradient(90deg, #007bff, #00aaff);
        }
        thead th {
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        tbody tr {
            transition: all .2s ease-in-out;
        }
        tbody tr:hover {
            background-color: #f1f7ff;
            transform: scale(1.01);
        }

        /* === BADGES ET BOUTONS === */
        .bg-gradient-primary {
            background: linear-gradient(45deg, #36b9cc, #4e73df);
        }
        .btn-outline-primary {
            border: 1px solid #007bff;
        }
        .btn-outline-primary:hover {
            background: #007bff;
            color: #fff;
        }
    </style>
@endpush
