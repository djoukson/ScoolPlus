<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Enseignants</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
        h3 { margin-bottom: 5px; }
    </style>
</head>
<body>
<h2>Salaires Enseignants - {{ $mois }}</h2>

{{-- Primaire --}}
@if(isset($enseignantsPrimaire) && count($enseignantsPrimaire))
    <h3>Enseignants - Primaire</h3>
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
                <td>{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

{{-- Collège & Lycée --}}
@if(isset($enseignantsCollegeLycee) && count($enseignantsCollegeLycee))
    <h3>Enseignants - Collège & Lycée</h3>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Niveau</th>
            <th>Nombre d'heures</th>
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
                <td>{{ $e['heures'] }}</td>
                <td>{{ number_format($e['taux'], 2, ',', ' ') }} FCFA</td>
                <td>{{ number_format($e['salaire'], 2, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

</body>
</html>
