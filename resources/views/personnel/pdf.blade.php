<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste du Personnel Administratif</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 20px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .logo {
            width: 80px;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school-info {
            font-size: 11px;
            line-height: 1.4;
        }

        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-top: 8px;
        }

        .line {
            border-bottom: 2px solid #000;
            margin: 8px 0 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        td {
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            width: 100%;
        }

        .signature {
            width: 40%;
            float: right;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- ================= EN-TÊTE ================= --}}
<table class="header-table">
    <tr>
        <td width="20%">
            @if($ecole && $ecole->logo)
                <img src="{{ public_path('storage/'.$ecole->logo) }}" class="logo">
            @endif
        </td>

        <td width="60%" class="text-center">
            <div class="school-name">
                {{ $ecole->nom ?? 'NOM DE L’ÉTABLISSEMENT' }}
            </div>
            <div class="school-info">
                Adresse : {{ $ecole->adresse ?? '-' }} <br>
                Tél : {{ $ecole->telephone ?? '-' }} |
                Email : {{ $ecole->email ?? '-' }} <br>
                @if($ecole->site_web)
                    Site web : {{ $ecole->site_web }}
                @endif
            </div>
        </td>

        <td width="20%"></td>
    </tr>
</table>

<div class="title">
    LISTE DU PERSONNEL ADMINISTRATIF
</div>

@if($ecole && $ecole->annee)
    <div class="text-center" style="font-size:11px;">
        Année scolaire : {{ $ecole->annee->libelle }}
    </div>
@endif

<div class="line"></div>

{{-- ================= TABLEAU ================= --}}
<table>
    <thead>
    <tr>
        <th>Matricule</th>
        <th>Nom & Prénom</th>
        <th>Poste / Fonction</th>
        <th>Téléphone</th>
        <th>Email</th>
        <th>Salaire (FCFA)</th>
        <th>Statut</th>
    </tr>
    </thead>
    <tbody>
    @php $totalSalaire = 0; @endphp

    @foreach($personnel as $p)
        @php $totalSalaire += $p->salaire ?? 0; @endphp
        <tr>
            <td class="text-center">{{ $p->id }}</td>
            <td>{{ strtoupper($p->nom) }} {{ ucfirst($p->prenom) }}</td>
            <td>{{ $p->poste ?? '-' }}</td>
            <td>{{ $p->tel ?? '-' }}</td>
            <td>{{ $p->email ?? '-' }}</td>
            <td class="text-right">
                {{ number_format($p->salaire, 0, ',', ' ') }}
            </td>
            <td class="text-center">
                {{ $p->statut ? 'Actif' : 'Inactif' }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <th colspan="5" class="text-right">TOTAL SALAIRES</th>
        <th class="text-right">
            {{ number_format($totalSalaire, 0, ',', ' ') }} FCFA
        </th>
        <th></th>
    </tr>
    </tfoot>
</table>

{{-- ================= SIGNATURE ================= --}}
<div class="footer">
    <div class="signature">
        <p>
            Fait le {{ now()->format('d/m/Y') }} <br><br>
            <strong>Le Directeur</strong><br><br><br>
            {{ $ecole->directeur ?? '_________________' }}
        </p>
    </div>
</div>

</body>
</html>
