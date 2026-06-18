@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Breadcrumb --}}
        <div class="pro-breadcrumb mb-4">
            <div class="breadcrumb-title">
                Gestion des États & Rapports
            </div>

            {{-- Formulaire de filtre --}}
            <form method="GET" class="row g-2 mt-3">
                <div class="col-md-2">
                    <input type="date"
                           name="date_start"
                           class="form-control"
                           value="{{ request('date_start') }}"
                           required>
                </div>
                <div class="col-md-2">
                    <input type="date"
                           name="date_end"
                           class="form-control"
                           value="{{ request('date_end') }}"
                           required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        Afficher
                    </button>
                </div>
            </form>

            {{-- Breadcrumb --}}
            <div class="breadcrumb-wrapper mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item active">États & Rapports</li>
                </ol>
            </div>
        </div>

        {{-- ===================== --}}
        {{-- AFFICHAGE CONDITIONNEL --}}
        {{-- ===================== --}}
        @if(isset($dateDebut) && isset($dateFin))

            {{-- Boutons impression --}}
            <div class="mb-4 d-flex gap-2 flex-wrap">
                <a target="_blank"
                   href="{{ route('etats.print', request()->all() + ['type' => 'all']) }}"
                   class="btn btn-success">
                    🖨️ Tous les états
                </a>

                <a target="_blank"
                   href="{{ route('etats.print', request()->all() + ['type' => 'scolarite']) }}"
                   class="btn btn-outline-success">
                    🧾 Scolarité & inscription
                </a>

                <a target="_blank"
                   href="{{ route('etats.print', request()->all() + ['type' => 'souscription']) }}"
                   class="btn btn-outline-primary">
                    📌 Souscriptions
                </a>

                <a target="_blank"
                   href="{{ route('etats.print', request()->all() + ['type' => 'depense']) }}"
                   class="btn btn-outline-danger">
                    💸 Dépenses
                </a>

                <a target="_blank"
                   href="{{ route('etats.print', request()->all() + ['type' => 'salaire']) }}"
                   class="btn btn-outline-dark">
                    👥 Salaires & rémunérations
                </a>
            </div>

            {{-- ===================== --}}
            {{-- ENTRÉES --}}
            {{-- ===================== --}}

            {{-- Paiements scolarité & inscription --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    Entrées – Paiements scolarité & inscription
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Frais</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalPaiements = 0; @endphp
                        @forelse($paiements as $p)
                            @php $totalPaiements += $p->montant_paye; @endphp
                            <tr>
                                <td>
                                    {{ $p->eleve
                                        ? strtoupper($p->eleve->nom).' '.ucfirst($p->eleve->prenom)
                                        : '-' }}
                                </td>
                                <td>
                                    {{ $p->frais ? $p->frais->libelle : '-' }}
                                </td>
                                <td>{{ number_format($p->montant_paye,0,',',' ') }} FCFA</td>
                                <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Aucun paiement trouvé
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">TOTAL</td>
                            <td colspan="2">{{ number_format($totalPaiements,0,',',' ') }} FCFA</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Souscriptions --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Entrées – Souscriptions
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Service</th>
                            <th>Élève</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalSouscriptions = 0; @endphp
                        @forelse($souscriptions as $s)
                            @php $totalSouscriptions += $s->montant; @endphp
                            <tr>
                                <td>
                                    {{ $s->service ? $s->service->libelle : '-' }}
                                </td>
                                <td>
                                    {{ $s->eleve
                                        ? strtoupper($s->eleve->nom).' '.ucfirst($s->eleve->prenom)
                                        : '-' }}
                                </td>
                                <td>{{ number_format($s->montant,0,',',' ') }} FCFA</td>
                                <td>{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Aucune souscription trouvée
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">TOTAL</td>
                            <td colspan="2">{{ number_format($totalSouscriptions,0,',',' ') }} FCFA</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- ===================== --}}
            {{-- SORTIES --}}
            {{-- ===================== --}}

            {{-- Dépenses --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-danger text-white">
                    Sorties – Dépenses
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalDepenses = 0; @endphp
                        @forelse($depenses as $d)
                            @php $totalDepenses += $d->montant; @endphp
                            <tr>
                                <td>{{ $d->titre }}</td>
                                <td>{{ $d->description }}</td>
                                <td>{{ number_format($d->montant,0,',',' ') }} FCFA</td>
                                <td>{{ $d->date_depense->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Aucune dépense trouvée
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2">TOTAL</td>
                            <td colspan="2">{{ number_format($totalDepenses,0,',',' ') }} FCFA</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Salaires --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white">
                    Sorties – Salaires & rémunérations
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Personnel</th>
                            <th>Montant</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalSalaires = 0; @endphp
                        @forelse($salaires as $s)
                            @php $totalSalaires += $s->salaire; @endphp
                            <tr>
                                <td>{{ $s->nom ?? 'Personnel' }}</td>
                                <td>{{ number_format($s->salaire,0,',',' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    Aucun salaire trouvé
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="fw-bold">
                            <td>TOTAL</td>
                            <td>{{ number_format($totalSalaires,0,',',' ') }} FCFA</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- TOTAL GLOBAL --}}
            <div class="alert alert-info fw-bold fs-5 text-end">
                TOTAL GLOBAL :
                {{ number_format(($totalPaiements + $totalSouscriptions) - ($totalDepenses + $totalSalaires),0,',',' ') }}
                FCFA
            </div>

        @endif

    </div>
@endsection
