<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>États financiers - {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</title>

    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        .header {
            display: flex;
            border-bottom: 2px solid #2d3748;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header .infos h1 {
            margin: 0;
            font-size: 20px;
        }

        .header .infos p {
            margin: 2px 0;
            font-size: 12px;
        }

        h2 {
            margin-top: 25px;
            font-size: 15px;
            border-bottom: 1px solid #718096;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #cbd5e0;
            padding: 6px;
        }

        th {
            background-color: #edf2f7;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background-color: #f7fafc;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #718096;
            margin-top: 40px;
            border-top: 1px solid #cbd5e0;
            padding-top: 5px;
        }
    </style>
</head>

<body>

@php
    $ecole = \App\Models\Ecole::first();
    $totalEntrees = 0;
    $totalSorties = 0;
@endphp

{{-- ================= HEADER ================= --}}
<div class="header">
    <div class="infos">
        <h1>{{ $ecole->nom ?? 'Nom de l’École' }}</h1>
        <p>{{ $ecole->adresse ?? '' }}</p>
        <p>Tél : {{ $ecole->telephone ?? '' }} | Email : {{ $ecole->email ?? '' }}</p>
        <p><strong>Période :</strong> {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</p>
    </div>
</div>

{{-- ================= ENTRÉES ================= --}}
@if(in_array($type, ['all','scolarite']))
    <h2>Entrées – Paiements scolarité & inscription</h2>
    <table>
        <thead>
        <tr>
            <th>Élève</th>
            <th>Frais</th>
            <th>Montant</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($paiements as $p)
            @php $total += $p->montant_paye; @endphp
            <tr>
                <td>
                    {{ $p->eleve
                        ? strtoupper($p->eleve->nom).' '.ucfirst($p->eleve->prenom)
                        : '-' }}
                </td>
                <td>
                    {{ $p->frais ? $p->frais->libelle : '-' }}
                </td>                <td class="text-right">{{ number_format($p->montant_paye,0,',',' ') }} FCFA</td>
                <td>{{ $p->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="2">TOTAL</td>
            <td colspan="2">{{ number_format($total,0,',',' ') }} FCFA</td>
        </tr>
        </tbody>
    </table>
    @php $totalEntrees += $total; @endphp
@endif

@if(in_array($type, ['all','souscription']))
    <h2>Entrées – Souscriptions</h2>
    <table>
        <thead>
        <tr>
            <th>Service</th>
            <th>Élève</th>
            <th>Montant</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($souscriptions as $s)
            @php $total += $s->montant; @endphp
            <tr>
                <td>
                    {{ $s->service ? $s->service->libelle : '-' }}
                </td>
                <td>
                    {{ $s->eleve
                        ? strtoupper($s->eleve->nom).' '.ucfirst($s->eleve->prenom)
                        : '-' }}
                </td>
                <td class="text-right">{{ number_format($s->montant,0,',',' ') }} FCFA</td>
                <td>{{ $s->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="2">TOTAL</td>
            <td colspan="2">{{ number_format($total,0,',',' ') }} FCFA</td>
        </tr>
        </tbody>
    </table>
    @php $totalEntrees += $total; @endphp
@endif

{{-- ================= SORTIES ================= --}}
@if(in_array($type, ['all','depense']))
    <h2>Sorties – Dépenses</h2>
    <table>
        <thead>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Montant</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($depenses as $d)
            @php $total += $d->montant; @endphp
            <tr>
                <td>{{ $d->titre }}</td>
                <td>{{ $d->description }}</td>
                <td class="text-right">{{ number_format($d->montant,0,',',' ') }} FCFA</td>
                <td>{{ $d->date_depense->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="2">TOTAL</td>
            <td colspan="2">{{ number_format($total,0,',',' ') }} FCFA</td>
        </tr>
        </tbody>
    </table>
    @php $totalSorties += $total; @endphp
@endif

@if(in_array($type, ['all','salaire']))
    <h2>Sorties – Salaires & rémunérations</h2>
    <table>
        <thead>
        <tr>
            <th>Personnel</th>
            <th>Montant</th>
        </tr>
        </thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($salaires as $s)
            @php $total += $s->salaire; @endphp
            <tr>
                <td>{{ $s->nom ?? 'Personnel' }}</td>
                <td class="text-right">{{ number_format($s->salaire,0,',',' ') }} FCFA</td>
            </tr>
        @endforeach
        <tr class="total">
            <td>TOTAL</td>
            <td>{{ number_format($total,0,',',' ') }} FCFA</td>
        </tr>
        </tbody>
    </table>
    @php $totalSorties += $total; @endphp
@endif

{{-- ================= RÉCAP ================= --}}
@if($type === 'all')
    <h2>Récapitulatif global</h2>
    <table>
        <tr>
            <th>Total Entrées</th>
            <td class="text-right">{{ number_format($totalEntrees,0,',',' ') }} FCFA</td>
        </tr>
        <tr>
            <th>Total Sorties</th>
            <td class="text-right">{{ number_format($totalSorties,0,',',' ') }} FCFA</td>
        </tr>
        <tr class="total">
            <th>Solde</th>
            <td class="text-right">
                {{ number_format($totalEntrees - $totalSorties,0,',',' ') }} FCFA
            </td>
        </tr>
    </table>
@endif

<div class="footer">
    Généré le {{ now()->format('d/m/Y H:i') }} — SchoolPlus
</div>

</body>
</html>
