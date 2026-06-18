@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">
            <i class="fas fa-edit text-warning"></i>
            Modifier le paiement #{{ $paiement->id }}
        </h2>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('paiements.update', $paiement) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Formulaire réutilisable --}}
                    {{-- Formulaire réutilisable --}}
                    @include('paiements.editform', [
                        'paiement'    => $paiement,
                        'inscriptions'=> $inscriptions,
                        'frais'       => $frais
                    ])


                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
