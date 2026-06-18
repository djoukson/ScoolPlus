@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3 class="mb-4">Détails du paiement</h3>

        <div class="card">
            <div class="card-body">
                <p><strong>Élève :</strong> {{ $paiement->eleve->nom }} {{ $paiement->eleve->prenom }}</p>
                <p><strong>Classe :</strong> {{ $paiement->classe->nom ?? '-' }}</p>
                <p><strong>Année scolaire :</strong> {{ $paiement->annee->libelle ?? '-' }}</p>
                <p><strong>Frais :</strong> {{ $paiement->frais->libelle ?? '-' }}</p>
                <p><strong>Montant payé :</strong> {{ number_format($paiement->montant_paye, 0, ',', ' ') }} FCFA</p>
                <p><strong>Date de paiement :</strong> {{ $paiement->date_paiement->format('d/m/Y') }}</p>
                <p><strong>Mode de paiement :</strong> {{ $paiement->mode_paiement }}</p>
            </div>
        </div>

        <a href="{{ route('paiements.index') }}" class="btn btn-secondary mt-3">Retour à la liste</a>
    </div>
@endsection
