<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élèves - {{ $classe->nom }}</title>
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
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .table th, .table td { padding: 6px !important; }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <!-- En-tête -->
    <div class="header-title">
        <h2>Liste et informations des élèves </h2>
        <p>Classe : <strong>{{ $classe->nom }}</strong></p>
        <p>Date d’impression : {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

    <!-- Bouton impression -->
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
    </div>

    <!-- Tableau élèves -->
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Matricule</th>
            <th>Nom & Prénom</th>
            <th>Date de naissance</th>
            <th>Sexe</th>
            <th>Tuteur</th>
            <th>Telephone</th>
            <th>Date d'inscription</th>
            <th>Observation</th>
        </tr>
        </thead>
        <tbody>
        @forelse($eleves as $index => $eleve)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: center">{{ $eleve->id }}</td>
                <td>{{ strtoupper($eleve->nom) }} {{ ucfirst($eleve->prenom) }}</td>
                <td>{{ \Carbon\Carbon::parse($eleve->date_naissance)->format('d/m/Y') }}</td>
                <td>{{ $eleve->sexe == 'M' ? 'Masculin' : 'Féminin' }}</td>
                <td>{{ $eleve->tuteur_nom }}</td>
                <td>{{ $eleve->tuteur_tel }}</td>
                <td>{{ $eleve->created_at }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Aucun élève dans cette classe.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
