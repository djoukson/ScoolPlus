@extends('layouts.app')

@section('title', 'Gestion des Notes')
@section('page-title', 'Gestion des Notes')

@section('content')
    <div class="container-fluid py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Gestion des Notes et bulletins
            </div>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les Notes
                    </li>
                </ol>
            </div>
        </div>

        {{-- ✅ Tableau moderne --}}
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="bg-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Classe</th>
                            <th>Niveau</th>
                            <th>Enseignant / Matières</th>
                            <th>Bulletins</th>
                            <th>Notes</th>
                            <th>Ajouter des Notes</th>
                        </tr>
                        </thead>
                        <tbody class="text-center">
                        @forelse($classes as $index => $classe)

                            {{-- Ignorer les classes Primaire --}}
                            @if(strtolower($classe->niveau->nom) === 'primaire')
                                @continue
                            @endif

                            <tr>
                                {{-- Numéro --}}
                                <td class="fw-bold">{{ $index + 1 }}</td>

                                {{-- Nom de la classe --}}
                                <td class="fw-bold text-primary">{{ $classe->nom }}</td>

                                {{-- Niveau --}}
                                <td><span class="badge bg-secondary">{{ ucfirst($classe->niveau->nom) }}</span></td>

                                {{-- Matières de la classe --}}
                                <td>
            <span class="badge bg-info-subtle text-dark">
                <i class="fas fa-book me-1"></i>
                {{ $classe->affectations_count ?? 0 }} matière(s)
            </span>
                                </td>

                                {{-- Bouton Bulletins --}}
                                <td>
                                    <a href="{{ route('bulletins.index', $classe->id) }}"
                                       class="btn btn-labeled btn-outline-success btn-sm">
                                        <span class="btn-label"><i class="fa fa-id-card"></i></span> Bulletins
                                    </a>
                                </td>

                                {{-- Bouton Notes --}}
                                <td>
                                    <a href="{{ route('evaluations.notes', $classe->id) }}"
                                       class="btn btn-labeled btn-outline-info btn-sm">
                                        <span class="btn-label"><i class="fa fa-chart-line"></i></span> Notes
                                    </a>
                                </td>

                                {{-- Bouton Ajouter --}}
                                <td>
                                    <a href="{{ route('evaluations.createByClasse', $classe->id) }}"
                                       class="btn btn-labeled btn-outline-primary btn-sm">
                                        <span class="btn-label"><i class="fa fa-plus-circle"></i></span> Ajouter
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i> Aucune classe trouvée pour l’année en cours.
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
        .text-gradient {
            background: linear-gradient(90deg, #6366f1, #22c55e, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-labeled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.25s ease-in-out;
        }

        .btn-labeled .btn-label {
            background: rgba(0, 0, 0, 0.1);
            padding: 5px 8px;
            border-radius: 50%;
            margin-right: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-labeled:hover {
            transform: translateY(-2px);
        }

        .table thead th {
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        .bg-success-subtle { background: rgba(40,167,69,0.08); }
        .bg-danger-subtle { background: rgba(220,53,69,0.08); }
        .bg-info-subtle { background: rgba(23,162,184,0.08); }

        .card-header.bg-gradient-primary {
            background: linear-gradient(135deg, #6366f1, #06b6d4);
        }
    </style>
@endpush
