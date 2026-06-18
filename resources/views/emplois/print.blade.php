<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - {{ $classe->nom }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            color: #222;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 20px;
        }

        .header h4 {
            margin: 4px 0 0;
            font-weight: normal;
            color: #555;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px 5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f0f4f8;
            text-transform: uppercase;
            font-size: 12px;
        }

        tr:nth-child(even) td {
            background-color: #fafafa;
        }

        td strong {
            color: #000;
            font-size: 13px;
        }

        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 12px;
            color: #666;
        }

        .print-btn {
            display: block;
            margin: 15px auto;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            .print-btn { display: none; }
        }

        /* Entête officielle optionnelle */
        .official-header {
            text-align: center;
            font-size: 13px;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .official-header img {
            width: 50px;
            height: 50px;
            margin-bottom: 5px;
        }

    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>

<div class="header">
    <h2>EMPLOI DU TEMPS</h2>
    <h1>Classe : {{ $classe->nom }}</h1>
</div>

<table>
    <thead>
    <tr>
        <th>Jour / Heure</th>
        @foreach($heures as $heure)
            <th>{{ $heure->libelle }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] as $jour)
        <tr>
            <th>{{ $jour }}</th>
            @foreach($heures as $heure)
                @php
                    $emploi = $emploisExistants->firstWhere(fn($e) =>
                        $e->jour === $jour && $e->heure_cours_id === $heure->id
                    );
                @endphp
                <td>
                    @if($emploi)
                        <strong>{{ $emploi->affectation->matiere->nom ?? '-' }}</strong><br>
                        <small>{{ $emploi->affectation->enseignant->nom ?? '' }}</small>
                    @else
                        -
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>Imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
</div>

</body>
</html>
