<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Enseignants {{ $niveau ? ' - '.$niveau : '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }
        .header-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-title h2 {
            font-weight: bold;
            margin: 0;
        }
        .header-title p {
            margin: 0;
            font-size: 13px;
            color: #555;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            table {
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .table {
                width: auto !important;
                max-width: 100% !important;
            }
            body {
                zoom: 85%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container mt-4">

    <!-- En-tête -->
    <div class="header-title">
        <h2>Liste des Enseignants</h2>

        @if($niveau)
            <p>Niveau sélectionné : <strong>{{ ucfirst($niveau) }}</strong></p>
        @else
            <p><strong>Tous les niveaux confondus</strong></p>
        @endif

        <p>Date d’impression : {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

    <!-- Bouton impression -->
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
    </div>

    <!-- Tableau enseignants -->
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Matricule</th>
            <th>Nom & Prénom</th>
            <th>Niveau</th>
            <th>Type</th>
            <th>Spécialité</th>

            @if(strtolower($niveau) === 'primaire')
                <th>Salaire Mensuel</th>
            @endif

            <th>Téléphone</th>
            <th>Email</th>
            <th>Statut</th>
            <th>Date Enregistrement</th>
            <th>Observation</th>
        </tr>
        </thead>

        <tbody>
        @forelse($enseignants as $index => $ens)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: center">{{ $ens->id }}</td>
                <td>{{ strtoupper($ens->nom) }} {{ ucfirst($ens->prenom) }}</td>
                <td>{{ $ens->niveau ? $ens->niveau->nom : '-' }}</td>
                <td>{{ $ens->type ?? '-' }}</td>
                <td>{{ $ens->specialite ?? '-' }}</td>

                @if(strtolower($niveau) === 'primaire')
                    <td style="text-align:right">
                        {{ $ens->salaire_mensuel
                            ? number_format($ens->salaire_mensuel, 0, ',', ' ') . ' FCFA'
                            : '-' }}
                    </td>
                @endif

                <td>{{ $ens->tel ?? '-' }}</td>
                <td>{{ $ens->email ?? '-' }}</td>

                <td>
                    {!! $ens->statut
                        ? '<span style="color:green">✔️ Actif</span>'
                        : '<span style="color:red">❌ Désactivé</span>' !!}
                </td>

                <td>{{ \Carbon\Carbon::parse($ens->created_at)->format('d/m/Y') }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ strtolower($niveau) === 'primaire' ? 12 : 11 }}"
                    class="text-center text-muted">
                    Aucun enseignant trouvé pour ce niveau.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

</body>
</html>
