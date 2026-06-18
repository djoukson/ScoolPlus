@extends('layouts.app')

@section('title', 'Historique des paiements')
@section('page-title', 'Historique des paiements de ' . $eleve->nom . ' ' . $eleve->prenom)

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Les souscriptions
            </div>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('services.paiementsouscriptions') }}">Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les souscriptions
                    </li>
                </ol>
            </div>
        </div>


        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div class="d-flex gap-2">
                <a href="{{ route('services.historique', [$eleve->id, $service->id]) }}?add=1" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Ajouter un paiement
                </a>

                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimer
                </button>
            </div>
        </div>

        @if($showAddForm)
            <div class="card shadow-sm border-0 rounded-3 mb-4 no-print">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-plus"></i> Ajouter un nouveau paiement
                </div>
                <div class="card-body">
                    <form action="{{ route('services.storePaiement') }}" method="POST">
                        @csrf
                        <input type="hidden" name="eleve_id" value="{{ $eleve->id }}">
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <input type="text" class="form-control" value="{{ $service->libelle }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant payé</label>
                            <input type="number" class="form-control" name="montant" id="montant" min="1" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('services.historique', [$eleve->id, $service->id]) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-primary text-white fw-bold">
                📌 Historique des paiements de {{ $eleve->nom }} {{ $eleve->prenom }}
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                    <tr>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Montant payé</th>
                        <th class="no-print">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($paiements as $p)
                        <tr class="text-center">
                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->service->libelle }}</td>
                            <td><span class="badge bg-success">{{ number_format($p->montant, 2) }} FCFA</span></td>
                            <td class="no-print">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-warning"
                                            onclick="openEditPaiementModal({{ $p->id }}, {{ $p->montant }})">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <form action="{{ route('services.deletePaiement', $p->id) }}" method="POST"
                                          onsubmit="return confirm('Confirmer la suppression ?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">🚫 Aucun paiement enregistré pour cet élève.</td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot class="table-light">
                    <tr class="fw-bold text-center">
                        <td colspan="2">💰 Montant du service</td>
                        <td colspan="2">{{ number_format($montantService, 2) }} FCFA</td>
                    </tr>
                    <tr class="fw-bold text-center">
                        <td colspan="2">✅ Total payé</td>
                        <td colspan="2" class="text-success">{{ number_format($totalPaye, 2) }} FCFA</td>
                    </tr>
                    <tr class="fw-bold text-center">
                        <td colspan="2">⚠️ Total restant</td>
                        <td colspan="2" class="text-danger">{{ number_format($totalRestant, 2) }} FCFA</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Paiement -->
    <div class="modal fade no-print" id="editPaiementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPaiementForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_montant" class="form-label">Montant payé</label>
                            <input type="number" name="montant" id="edit_montant" class="form-control" min="1" required>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ STYLE D’IMPRESSION --}}
    <style>
        @media print {
            .no-print { display: none !important; }
            .btn, .alert, .modal, .page-title { display: none !important; }
            body { background: #fff !important; }
            .container { max-width: 100% !important; }
            .table { border-collapse: collapse !important; width: 100%; font-size: 14px; }
            th, td { border: 1px solid #000 !important; padding: 6px !important; }
            .badge { background: none !important; color: #000 !important; font-weight: normal; }
            h3, h4, h5, .fw-bold { color: #000 !important; }
        }
    </style>

    {{-- ✅ SCRIPT MODAL --}}
    <script>
        function openEditPaiementModal(paiementId, montant) {
            const form = document.getElementById('editPaiementForm');
            form.action = `/services/paiements/${paiementId}/update`;
            document.getElementById('edit_montant').value = montant;
            new bootstrap.Modal(document.getElementById('editPaiementModal')).show();
        }
    </script>

@endsection
