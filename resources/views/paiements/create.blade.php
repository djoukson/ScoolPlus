@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="pro-breadcrumb">
            <div class="breadcrumb-title">
                Nouveau paiement
            </div>
            {{-- Bouton Liste des classes --}}

            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('paiements.index') }}">Retour</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Les paiements
                    </li>
                </ol>
            </div>
        </div>

        <div class="card shadow-sm mt-3">

            <div class="card-body">
                <form action="{{ route('paiements.store') }}" method="POST">
                    @include('paiements.form', ['paiement' => null])
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
