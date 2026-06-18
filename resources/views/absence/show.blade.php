@extends('layouts.app')

@section('title', 'Gestion des absences')

@section('content')
    <div class="container-fluid py-4">

        {{-- Bouton Retour --}}
        <a href="{{ route('absence.index') }}" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>

        {{-- Card contenant le détail --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Absences de {{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}</h5>
                <span class="badge bg-light text-dark">Total heures : {{ $total_heures }} h</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered absenceTable">
                    <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th class="text-center">Heures</th>
                        <th class="text-center">Justifié</th>
                        <th>Date</th>
                        <th >Action</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($absences as $absence)
                        <tr>
                            <td>{{ $absence->matiere->nom }}</td>
                            <td class="text-center">{{ $absence->heures }} h</td>
                            <td class="text-center">
                                @if($absence->is_justified)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-warning">Non</span>
                                @endif
                            </td>
                            <td>{{ $absence->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle"
                                            type="button"
                                            data-toggle="dropdown"
                                            aria-expanded="false">
                                        Options
                                    </button>

                                    <ul class="dropdown-menu">
                                        {{-- Justifier / Déjustifier --}}
                                        <li>
                                            <form action="{{ route('absences.toggleJustified', $absence->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item">
                                                    @if($absence->is_justified)
                                                        <i class="fas fa-times-circle text-warning me-1"></i>
                                                        Marquer non justifiée
                                                    @else
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                        Marquer justifiée
                                                    @endif
                                                </button>
                                            </form>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        {{-- Supprimer --}}
                                        <li>
                                            <form action="{{ route('absences.destroy', $absence->id) }}" method="POST"
                                                  onsubmit="return confirm('Supprimer cette absence ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-1"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
