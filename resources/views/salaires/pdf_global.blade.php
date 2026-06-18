<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Salaires - {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 10px;
        }
        .header img {
            height: 70px;
            margin-right: 15px;
        }
        .header .infos {
            line-height: 1.2;
        }
        .header .infos h1 {
            margin: 0;
            font-size: 20px;
            color: #1a202c;
        }
        .header .infos p {
            margin: 0;
            font-size: 12px;
            color: #4a5568;
        }
        h2 {
            margin-top: 30px;
            color: #1a202c;
            border-bottom: 1px solid #4a5568;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #cbd5e0;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #edf2f7;
            color: #2d3748;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #718096;
            margin-top: 50px;
            border-top: 1px solid #cbd5e0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">

    {{-- Header École --}}
    @php
        $ecole = \App\Models\Ecole::first(); // tu peux filtrer par annee si besoin
    @endphp
    <div class="header">

        <div class="infos">
            <h1>{{ $ecole->nom ?? 'Nom de l\'École' }}</h1>
            <p>{{ $ecole->adresse ?? '' }}</p>
            <p>Tél: {{ $ecole->telephone ?? '' }} | Email: {{ $ecole->email ?? '' }}</p>
            @if($ecole->site_web)
                <p>Site web: {{ $ecole->site_web }}</p>
            @endif
        </div>
    </div>

    {{-- Période --}}
    <h2>Période: {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</h2>

    {{-- Enseignants Primaire --}}
    @if(!empty($enseignantsPrimaire))
        <h2>Enseignants - Primaire</h2>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Niveau</th>
                <th>Salaire</th>
            </tr>
            </thead>
            <tbody>
            @foreach($enseignantsPrimaire as $e)
                <tr>
                    <td>{{ $e['nom'] }}</td>
                    <td>{{ $e['prenom'] }}</td>
                    <td>{{ $e['niveau'] }}</td>
                    <td class="text-right">{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- Enseignants Collège/Lycée --}}
    @if(!empty($enseignantsCollegeLycee))
        <h2>Enseignants - Collège & Lycée</h2>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Niveau</th>
                <th>Nombre d'heures</th>
                <th>Heures manquee(s)</th>
                <th>Taux horaire</th>
                <th>Salaire</th>
            </tr>
            </thead>
            <tbody>
            @foreach($enseignantsCollegeLycee as $e)
                <tr>
                    <td>{{ $e['nom'] }}</td>
                    <td>{{ $e['prenom'] }}</td>
                    <td>{{ $e['niveau'] }}</td>
                    <td class="text-right">{{ $e['heures'] }}</td>
                    <td>{{ $e['heures_manquees'] }}</td>
                    <td class="text-right">{{ number_format($e['taux'], 2, ',', ' ') }} FCFA</td>
                    <td class="text-right">{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- Personnel administratif --}}
    @if(!empty($personnel))
        <h2>Personnel administratif</h2>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Poste</th>
                <th>Salaire</th>
            </tr>
            </thead>
            <tbody>
            @foreach($personnel as $p)
                <tr>
                    <td>{{ $p['nom'] }}</td>
                    <td>{{ $p['prenom'] }}</td>
                    <td>{{ $p['poste'] }}</td>
                    <td class="text-right">{{ number_format($p['salaire'], 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
    <div class="alert alert-success fw-bold fs-5 text-end mt-4">
        GRAND TOTAL SALAIRES :
        {{ number_format($grandTotalGlobal, 0, ',', ' ') }} FCFA
    </div>
    {{-- Footer --}}
    <div class="footer">
        Généré le {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - SchoolPlus v1.12
    </div>
</div>
</body>
</html>
