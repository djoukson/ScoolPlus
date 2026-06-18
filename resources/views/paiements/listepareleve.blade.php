@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user"></i> {{ $eleve->nom.' '.$eleve->prenom }}</h2>
            <div class="d-flex gap-2">
                <!-- Bouton imprimer tout l'état -->
                <a href="{{ route('paiements.create') }}" class="btn btn-primary">
                    <i class="fas fa-dollar"></i> Nouveau paiement
                </a>
                <a href="{{ route('paiements.printAll', $eleve->id) }}" target="_blank" class="btn btn-success">
                    <i class="fas fa-print"></i> Imprimer l'état complet
                </a>
                <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <!-- Frais d'inscription -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 text-white" style="background: linear-gradient(135deg, #6f42c1, #8e44ad);">
                    <div class="card-body">
                        @php
                            $reducInscription = $bourse?->frais->firstWhere('libelle', "Frais d'Inscription")->pivot->pourcentage ?? 0;
                        @endphp

                        @if($reducInscription > 0)
                            <p class="mb-1">
                                <strong class="text-warning">Réduction Bourse :</strong>
                                <span class="badge bg-warning text-dark">{{ number_format($reducInscription, 0) }} %</span>
                                <i class="fas fa-gift"></i> Bourse active
                            </p>
                        @else
                            <p class="mb-1"><strong>Réduction Bourse :</strong> {{ number_format($reducInscription, 0) }} %</p>
                        @endif

                        <h5 class="card-title"><i class="fas fa-user-graduate"></i> Frais d'inscription</h5>
                        <p class="mb-1"><strong>Total :</strong> {{ number_format($totalFrais, 0, ',', ' ') }} FCFA</p>
                        <p class="mb-1"><strong>Déjà payé :</strong> {{ number_format($totalPayé, 0, ',', ' ') }} FCFA</p>
                        <p class="mb-0">
                            <strong>Restant :</strong>
                            <span class="{{ $restant < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($restant, 0, ',', ' ') }} FCFA</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Scolarité -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 text-white" style="background: linear-gradient(135deg, #198754, #28a745);">
                    <div class="card-body">
                        @php
                            $reducScolarite = $bourse?->frais->firstWhere('libelle', "Scolarité")->pivot->pourcentage ?? 0;
                        @endphp

                        @if($reducScolarite > 0)
                            <p class="mb-1">
                                <strong class="text-warning">Réduction Bourse :</strong>
                                <span class="badge bg-warning text-dark">{{ number_format($reducScolarite, 0) }} %</span>
                                <i class="fas fa-gift"></i> Bourse active
                            </p>
                        @else
                            <p class="mb-1"><strong>Réduction Bourse :</strong> {{ number_format($reducScolarite, 0) }} %</p>
                        @endif

                        <h5 class="card-title"><i class="fas fa-book"></i> Scolarité</h5>
                        <p class="mb-1"><strong>Total :</strong> {{ number_format($totalScolarite, 0, ',', ' ') }} FCFA</p>
                        <p class="mb-1"><strong>Déjà payé :</strong> {{ number_format($totalPayeScolarite, 0, ',', ' ') }} FCFA</p>
                        <p class="mb-0">
                            <strong>Restant :</strong>
                            <span class="{{ $restantScolarite < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($restantScolarite, 0, ',', ' ') }} FCFA</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Classe</th>
                        <th>Année</th>
                        <th>Frais</th>
                        <th>Montant Payé</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($eleve->paiements as $paiement)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $paiement->classe->nom ?? '—' }}</td>
                            <td>{{ $paiement->annee->nom ?? '—' }}</td>
                            <td>{{ $paiement->frais->libelle ?? '—' }}</td>
                            <td><span class="badge bg-success">{{ number_format($paiement->montant_paye,0,',',' ') }} FCFA</span></td>
                            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                            <td class="text-center d-flex gap-1 justify-content-center">
                                <!-- Modifier -->
                                <a href="{{ route('paiements.edit', $paiement) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Supprimer -->
                                <form action="{{ route('paiements.destroy', $paiement) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <!-- Imprimer ligne -->
                                <a href="{{ route('paiements.print', $paiement->id) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Aucun paiement enregistré pour cet élève.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Êtes-vous sûr ?',
                        text: "Cette action est irréversible !",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Oui, supprimer !',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
