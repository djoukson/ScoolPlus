@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow border-0 rounded-4 overflow-hidden" id="emploi-card">
            <!-- Header visible uniquement à l’écran -->
            <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center no-print">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-week fs-4"></i>
                    <h5 class="mb-0 fw-bold">Mon emploi du temps</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold">
                    👨‍🏫 {{ $enseignant->nom ?? '' }} {{ $enseignant->prenom ?? '' }}
                </span>
                    <button onclick="window.print()" class="btn btn-light btn-sm fw-semibold shadow-sm">
                        <i class="bi bi-printer-fill text-primary"></i> Imprimer
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body bg-light p-4" id="print-area">
                @if($emplois->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle text-primary fs-1 mb-3"></i>
                        <h6 class="fw-semibold text-secondary">Aucun emploi du temps disponible pour le moment.</h6>
                    </div>
                @else
                    @php
                        $jours = ['lundi','mardi','mercredi','jeudi','vendredi','samedi'];
                        $heures = \App\Models\HeureCours::orderBy('id')->get();
                        $ecole = \App\Models\Ecole::first();
                    @endphp

                        <!-- Entête pour impression -->
                    <div class="text-center mb-4 print-header">
                        <img src="{{ asset('dist/img/logo.png') }}" alt="Logo école" style="height:60px;" class="mb-2">
                        <h4 class="fw-bold mb-0">{{ $ecole->nom ?? 'Nom de l’école' }}</h4>
                        <p class="mb-0 fst-italic">{{ $ecole->devise ?? 'Travail - Liberté - Patrie' }}</p>
                        <small>Année scolaire : {{ $ecole->annee_scolaire ?? '2025-2026' }}</small>
                        <h3 class="fw-bold mt-2">Emploi du temps du professeur </h3>
                        <p class="fw-semibold">Mr/Mme {{ $enseignant->nom ?? '' }} {{ $enseignant->prenom ?? '' }}</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle bg-white shadow-sm">
                            <thead class="bg-primary text-white">
                            <tr>
                                <th>Jour / Heure</th>
                                @foreach($heures as $heure)
                                    <th>{{ $heure->libelle }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($jours as $jour)
                                <tr>
                                    <th class="bg-light text-capitalize">{{ $jour }}</th>
                                    @foreach($heures as $heure)
                                        @php
                                            $emploi = $emplois->first(function($e) use ($jour, $heure) {
                                                return strtolower(trim($e->jour)) === strtolower($jour)
                                                    && $e->heure_cours_id === $heure->id;
                                            });
                                        @endphp
                                        @if($emploi)
                                            <td class="bg-info-subtle">
                                                <b>{{ $emploi->affectation->matiere->nom ?? '' }}</b><br>
                                                <b>{{ $emploi->classe->nom ?? '' }}</b><br>
                                                <small class="text-muted">{{ $emploi->heure->heure_debut }} - {{ $emploi->heure->heure_fin }}</small>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Styles --}}
    <style>
        .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #0056d2); }
        .table td, .table th { vertical-align: middle; text-align: center; }
        .table-bordered th, .table-bordered td { border: 1px solid #dee2e6; }
        .bg-info-subtle { background-color: #dbeafe !important; }
        .bg-secondary-subtle { background-color: #e9ecef !important; border-radius: 10px; }

        /* Impression */
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            #emploi-card { box-shadow: none !important; border: none !important; }
            th, td { border: 1px solid #000 !important; color: black !important; }
            table { width: 100%; border-collapse: collapse !important; }
            .print-header { display: block; margin-bottom: 15px; text-align: center; }
        }
    </style>
@endsection
