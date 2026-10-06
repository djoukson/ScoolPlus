@extends('layouts.app')

@section('title', 'Paiement par classe')
@section('page-title', 'État des paiements - ' . $classe->nom)

@section('content')
    <div class="container py-4">

        {{-- Boutons actions --}}
        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('etatscolaritess') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux classes
            </a>

            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>



        {{-- En-tête --}}
        <div class="text-center mb-4">
            <h5 class="fw-bold">Fiche de scolarité – {{ $classe->nom }}</h5>
            <p><strong>Année scolaire :</strong> {{ $annee }}</p>
            <p><strong>Effectif :</strong> {{ $data->count() }} élève(s)</p>
        </div>



        {{-- FILTRES --}}
        <form id="form" method="GET" class="mb-4 p-3 rounded border shadow-sm d-flex flex-wrap gap-3">

            {{-- Statut --}}
            <div>
                <label class="fw-bold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="impayes" @selected(request('statut')=='impayes')>
                        Impayés
                    </option>
                    <option value="soldes" @selected(request('statut')=='soldes')>
                        Soldés
                    </option>
                </select>
            </div>


            {{-- Input montant --}}
            <div>
                <label class="fw-bold small">Montant payé inférieur à</label>
                <input type="number"
                       name="montant_max"
                       value="{{ request('montant_max') }}"
                       min="0"
                       placeholder="50000"
                       class="form-control">
            </div>


            {{-- Bouton --}}
            <div class="align-self-end">
                <button class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Filtrer
                </button>
            </div>

            {{-- Reset --}}
            <div class="align-self-end">
                <a href="{{ route('etatPaiementClasse', $classe->id) }}"
                   class="btn btn-outline-secondary">
                    Réinitialiser
                </a>
            </div>

        </form>




        {{-- TABLEAU --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
                État des paiements – {{ $classe->nom }}
            </div>

            <div class="card-body p-0">
                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light text-uppercase small">
                    <tr>
                        <th>#</th>
                        <th>Nom & Prénom</th>
                        <th>Type d’inscription</th>
                        <th>Bourse</th>
                        <th>Montant total</th>
                        <th>Payé</th>
                        <th>Reste</th>
                    </tr>
                    </thead>

                    <tbody>

                    @php
                        $total_montant = 0;
                        $total_paye = 0;
                        $total_reste = 0;
                    @endphp

                    @forelse($data as $index => $item)

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $item['eleve']->nom }}
                                {{ $item['eleve']->prenom }}
                            </td>

                            <td>
                                @if($item['type_inscription'] === 'Réinscrit')
                                    <span class="badge bg-secondary">Réinscrit · sans frais d’inscription</span>
                                @else
                                    <span class="badge bg-primary">Nouveau</span>
                                @endif
                            </td>


                            <td>
                                @if($item['bourse'])
                                    <span class="badge bg-success">
                                {{ $item['bourse']->nom }}

                                        @if(count($item['bourse_pourcentages']))
                                            ({{ implode('%, ', $item['bourse_pourcentages']) }}%)
                                        @endif
                            </span>
                                @else
                                    <span class="text-muted">(aucune)</span>
                                @endif
                            </td>


                            <td>
                                {{ number_format($item['montant_total'],0,',',' ') }}
                                FCFA
                            </td>


                            <td class="text-success fw-bold">
                                {{ number_format($item['montant_paye'],0,',',' ') }}
                                FCFA
                            </td>


                            <td class="fw-bold {{ $item['reste'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($item['reste']),0,',',' ') }}
                                FCFA
                                @if($item['reste'] < 0)
                                    <small>(trop payé)</small>
                                @endif
                            </td>
                        </tr>

                        @php
                            $total_montant += $item['montant_total'];
                            $total_paye    += $item['montant_paye'];
                            $total_reste   += $item['reste'];
                        @endphp

                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Aucun élève trouvé avec ces critères.
                            </td>
                        </tr>
                    @endforelse

                    </tbody>



                    {{-- TOTAUX --}}
                    <tfoot>
                    <tr class="fw-bold table-light">
                        <td colspan="3" class="text-end">TOTAL :</td>

                        <td>
                            {{ number_format($total_montant,0,',',' ') }} FCFA
                        </td>

                        <td class="text-success">
                            {{ number_format($total_paye,0,',',' ') }} FCFA
                        </td>

                        <td class="{{ $total_reste > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(abs($total_reste),0,',',' ') }} FCFA
                        </td>
                    </tr>
                    </tfoot>


                </table>
            </div>
        </div>
    </div>



    {{-- IMPRESSION --}}
    <style>
        @media print {
            .btn  {
                display:none;
            }
            @media print {
                nav,
                aside,
                header,
                footer,
                .btn,
                button,
                form,
                #form {
                    display: none !important;
                }
            }

        }
    </style>
    <script>
        function printPage(){
            document.getElementById('form').style.display = 'none';
            window.print();
            document.getElementById('form').style.display = 'flex';
        }
    </script>
@endsection
