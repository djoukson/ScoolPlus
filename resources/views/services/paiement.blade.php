@extends('layouts.app')

@section('title', 'Paiements des souscriptions')
@section('page-title', 'Paiements des souscriptions')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Les paiements
            </div>
            {{-- Bouton Liste des classes --}}
            <div class="mb-3">
                <button class="btn btn-outline-secondary" onclick="printPaiements()">
                    <i class="fas fa-print"></i> Imprimer
                </button>
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les souscriptions
                    </li>
                </ol>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div id="paiementsTable" class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white fw-bold">
                📌 Paiements
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr class="text-center">
                        <th>Élève</th>
                        <th>Service</th>
                        <th>Montant total</th>
                        <th>Montant payé</th>
                        <th>Reste</th>
                        <th class="no-print">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($paiementsGrouped as $p)
                        <tr class="text-center align-middle">
                            <td class="text-start">{{ $p['eleve']->nom }} {{ $p['eleve']->prenom }}</td>
                            <td>{{ $p['service']->libelle }}</td>
                            <td><span class="badge bg-info">{{ number_format($p['montant_total'],2) }} FCFA</span></td>
                            <td><span class="badge bg-success">{{ number_format($p['montant_paye'],2) }} FCFA</span></td>
                            <td>
                                @if($p['reste'] > 0)
                                    <span class="badge bg-warning text-dark">{{ number_format($p['reste'],2) }} FCFA</span>
                                @else
                                    <span class="badge bg-success">Payé</span>
                                @endif
                            </td>
                            <td class="no-print">
                                <a href="{{ route('services.historique', ['eleve' => $p['eleve']->id, 'service' => $p['service']->id]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </a>

                                <a href="{{ route('services.historique', ['eleve' => $p['eleve']->id, 'service' => $p['service']->id]) }}?add=1"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Nouveau paiement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">🚫 Aucun paiement enregistré.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🔹 Style d’impression --}}
    <style>
        @media print {
            .no-print { display: none !important; }
            .btn, .alert, .card-header, .page-title, .d-flex, .container > .d-flex { display: none !important; }
            .table { border: 1px solid #000; width: 100%; }
            th, td { border: 1px solid #000 !important; padding: 8px !important; }
            .badge { background: none !important; color: #000 !important; font-weight: normal; }
        }
    </style>

    {{-- 🔹 Script impression --}}
    <script>
        function printPaiements() {
            window.print();
        }
    </script>

@endsection
