<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépenses - {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</title>
    <style>
        body { font-family: "Segoe UI", Roboto, Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; color:#1f2937; }
        .container { padding: 20px; }
        .header { border-bottom: 2px solid #4a5568; margin-bottom: 20px; padding-bottom:10px; }
        .header h1 { margin:0; font-size: 20px; }
        .header p { margin:0; font-size:12px; color:#4a5568; }
        h2 { margin-top:20px; border-bottom:1px solid #4a5568; padding-bottom:5px; }
        table { width:100%; border-collapse: collapse; margin-top:10px; margin-bottom:30px; }
        th, td { border:1px solid #cbd5e0; padding:6px 8px; text-align:left; font-size:12px; }
        th { background-color:#edf2f7; color:#2d3748; }
        .text-right { text-align:right; }
        .footer { text-align:center; font-size:10px; color:#718096; margin-top:50px; border-top:1px solid #cbd5e0; padding-top:5px; }
    </style>
</head>
<body>
<div class="container">
    {{-- Header École --}}
    <div class="header">
        <h1>{{ $ecole->nom ?? 'Nom de l\'École' }}</h1>
        <p>{{ $ecole->adresse ?? '' }}</p>
        <p>Tél: {{ $ecole->telephone ?? '' }} | Email: {{ $ecole->email ?? '' }}</p>
        @if($ecole->site_web)
            <p>Site web: {{ $ecole->site_web }}</p>
        @endif
    </div>

    {{-- Période --}}
    <h2>Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</h2>

    {{-- Tableau dépenses --}}
    <h2>Dépenses</h2>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Titre</th>
            <th>Description</th>
            <th>Montant (FCFA)</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach($depenses as $index => $d)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $d->titre }}</td>
                <td>{{ $d->description ?? '-' }}</td>
                <td class="text-right">{{ number_format($d->montant, 2, ',', ' ') }}</td>
                <td>{{ \Carbon\Carbon::parse($d->date_depense)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="alert alert-success fw-bold fs-5 text-end mt-4">
        TOTAL DÉPENSES : {{ number_format($grandTotal, 2, ',', ' ') }} FCFA
    </div>

    <div class="footer">
        Généré le {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - SchoolPlus
    </div>
</div>
</body>
</html>
