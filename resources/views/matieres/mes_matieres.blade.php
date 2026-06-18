@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Mes matières
            </div>

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        👨‍🏫 {{ $enseignant->nom ?? '' }} {{ $enseignant->prenom ?? '' }}
                    </li>
                </ol>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4 overflow-hidden">

            <div class="card-body bg-light p-4">
                @if($affectations->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle text-primary fs-1 mb-3"></i>
                        <h6 class="fw-semibold text-secondary">Aucune matière affectée pour le moment.</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle text-center table-hover shadow-sm bg-white rounded-3">
                            <thead class="bg-primary text-white">
                            <tr>
                                <th>#</th>
                                <th>Classe</th>
                                <th>Matière</th>
                                <th>Heures attribuées</th>
                                <th>Heures planifiées</th>
                                <th>Écart</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($affectations as $index => $affectation)
                                @php
                                    $attribuees = $affectation->heures_attribuees ?? 0;
                                    $planifiees = $affectation->emplois_du_temps_count ?? 0;
                                    $ecart = $attribuees - $planifiees;
                                @endphp
                                <tr class="table-row-hover">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $affectation->classe->nom ?? '-' }}</td>
                                    <td>{{ $affectation->matiere->nom ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark px-3 py-2">{{ $attribuees }} h</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white px-3 py-2">{{ $planifiees }} h</span>
                                    </td>
                                    <td>
                                        @if($ecart == 0)
                                            <span class="badge bg-success text-white">OK</span>
                                        @elseif($ecart > 0)
                                            <span class="badge bg-warning text-dark">-{{ $ecart }} h à planifier</span>
                                        @else
                                            <span class="badge bg-danger text-white">+{{ abs($ecart) }} h en trop</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Totaux</td>
                                <td>
                                    <span class="badge bg-primary text-white px-3 py-2">
                                        {{ $affectations->sum('heures_attribuees') }} h
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-white px-3 py-2">
                                        {{ $affectations->sum('emplois_du_temps_count') }} h
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #0056d2); }
        .table-row-hover:hover { background-color: #f8f9fa; transition: 0.3s; }
        .badge.bg-secondary-subtle { background-color: #e9ecef; border-radius: 12px; }
    </style>
@endsection
