@extends('layouts.app')

@section('title', 'Proclamation – ' . $classe->nom)
@section('page-title', 'Proclamation – ' . $classe->nom)

@section('content')
    <div class="container py-4">

        {{-- Boutons --}}
        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>

        {{-- En-tête --}}
        <div class="text-center mb-4">
            <h5 class="fw-bold">Fiche de proclamation – {{ $classe->nom }}</h5>
            <p><strong>Période :</strong> {{ $decoupage->nom }}</p>
            <p><strong>Effectif :</strong> {{ $moyennes->count() }} élève(s)</p>
        </div>

        {{-- TABLEAU --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
                Résultats – {{ $classe->nom }} | {{ $decoupage->nom }}
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light text-uppercase small">
                    <tr>
                        <th>#</th>
                        <th>Nom & Prénom</th>

                        <th>Moyenne période</th>
                        <th>Rang période</th>
                        <th>Appréciation période</th>

                        {{-- ✅ Colonnes UNIQUEMENT pour le dernier découpage --}}
                        @if($estDernierDecoupage)
                            <th>Moyenne annuelle</th>
                            <th>Rang annuel</th>
                            <th>Appréciation annuelle</th>
                        @endif
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($moyennes as $index => $moyenne)
                        <tr>

                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $moyenne->inscription->eleve->nom }}
                                {{ $moyenne->inscription->eleve->prenom }}
                            </td>

                            {{-- Données découpage --}}
                            <td class="fw-bold text-primary">
                                {{ number_format($moyenne->moyenne, 2) }}
                            </td>

                            <td class="fw-bold">
                                {{ $moyenne->rang }}
                            </td>

                            <td>
                                {{ $moyenne->appreciation }}
                            </td>

                            {{-- ✅ Données ANNUELLES --}}
                            @if($estDernierDecoupage)
                                <td class="fw-bold text-success">
                                    {{ number_format($moyenne->moyenne_annuelle, 2) }}
                                </td>

                                <td class="fw-bold">
                                    {{ $moyenne->rang_annuel }}
                                </td>

                                <td>
                                    {{ $moyenne->appreciation_annuelle }}
                                </td>
                            @endif

                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $estDernierDecoupage ? 8 : 5 }}"
                                class="text-center text-muted">
                                Aucun résultat pour ce découpage
                            </td>
                        </tr>
                    @endforelse

                    </tbody>

                </table>
            </div>
        </div>

    </div>

    {{-- STYLE IMPRESSION --}}
    <style>
        @media print {
            nav, aside, header, footer, .btn, button {
                display: none !important;
            }
        }
    </style>

@endsection
