@extends('layouts.app')

@section('title', 'État de la scolarité')
@section('page-title', 'Dashboard des classes')

@section('content')
    <div class="container py-4">
        <!-- Entête école -->
        <!-- Entête école -->
        <div class="header d-flex justify-content-between align-items-center mb-3 p-3 border rounded-3 bg-light">
            <div class="left d-flex align-items-center gap-3">
                @if(!empty($ecole->logo))
                    <img src="{{ asset('storage/' . $ecole->logo) }}" alt="Logo de l'école" class="logo-ecole">
                @endif
                <div>
                    <h2 class="fw-bold mb-1">{{ strtoupper($ecole->nom ?? 'École CASE') }}</h2>
                    <p class="mb-0">{{ $ecole->adresse ?? 'Adresse non spécifiée' }}</p>
                    <p class="mb-0">Tél : {{ $ecole->telephone ?? '-' }} | Email : {{ $ecole->email ?? '-' }}</p>
                </div>
            </div>
            <div class="right text-end">
                <p class="mb-1"><strong>Date d'impression :</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p class="mb-0"><strong>Année scolaire :</strong> {{ $anneenom->nom }}</p>
            </div>
        </div>

        <!-- Titre centré avec bouton -->
        <div class="print-header d-flex justify-content-center align-items-center mb-4 gap-3">
            <h5 class="fw-bold mb-0">Liste des classes et paiements</h5>
            <button class="btn btn-sm btn-print" onclick="printTable()"><i class="fas fa-print"></i> Imprimer</button>
        </div>




        <div class="card shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover align-middle mb-0" id="classesTable">
                        <thead class="table-light text-uppercase small">
                        <tr>
                            <th>#</th>
                            <th>Classe</th>
                            <th>Effectif</th>
                            <th>Montant total frais</th>
                            <th>Prévision totale</th>
                            <th>Montant payé</th>
                            <th>Paiement</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($classes as $index => $classe)
                            @php
                                $effectifColor = $classe->effectif >= 20 ? '#28a745' : ($classe->effectif >= 10 ? '#ffc107' : '#dc3545');
                                $payColor = $classe->pourcentage_paye >= 70 ? '#198754' : ($classe->pourcentage_paye >= 40 ? '#fd7e14' : '#dc3545');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $classe->nom }}</td>
                                <td>
                                    <span class="badge px-3 py-2" style="background-color: {{ $effectifColor }}; color: #fff;">
                                        {{ $classe->effectif }}
                                    </span>
                                </td>
                                <td>{{ number_format($classe->total_frais, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($classe->prevision, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($classe->montant_paye, 0, ',', ' ') }} FCFA</td>
                                <td style="width: 150px;">
                                    <div class="progress" style="height: 8px; border-radius: 4px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $classe->pourcentage_paye }}%; background-color: {{ $payColor }};"
                                             aria-valuenow="{{ $classe->pourcentage_paye }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $classe->pourcentage_paye }}%</small>
                                </td>
                                <td>
                                    <a href="{{ route('paiements.etat.paiement.par.classe', $classe->id) }}"
                                       class="btn btn-sm btn-gradient d-flex align-items-center gap-1">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .logo-ecole {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 0.5rem;
            border: 1px solid #ddd;
        }
        .header h2 {
            font-size: 1.5rem;
        }
        .table-modern {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        .table-modern tbody tr {
            background-color: #fff;
            border-radius: 0.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .table-modern tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .table-modern th, .table-modern td {
            vertical-align: middle !important;
        }
        .badge {
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6610f2, #007bff);
            color: #fff;
            font-size: 0.8rem;
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            transition: all 0.25s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.18);
        }
        .btn-print {
            background-color: #198754;
            color: white;
            border-radius: 0.4rem;
            padding: 0.35rem 0.6rem;
            transition: all 0.25s ease;
        }
        .btn-print:hover {
            background-color: #157347;
        }
        .progress {
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
        @media print {
            body * { visibility: hidden; }

            /* Afficher uniquement ce qui doit être imprimé */
            .header, #classesTable, .print-header,
            .header *, #classesTable *, .print-header * {
                visibility: visible;
            }

            /* Positionner correctement les éléments */
            .header { position: absolute; top: 0; left: 0; width: 100%; }
            .print-header { position: absolute; top: 140px; left: 0; width: 100%; justify-content: center !important; }
            #classesTable { position: absolute; top: 200px; left: 0; width: 100%; }

            /* Masquer le bouton imprimer */
            .btn-print { display: none !important; }
        }
    </style>

    <!-- Script pour impression -->
    <script>
        function printTable() {
            window.print();
        }
    </script>
@endsection
