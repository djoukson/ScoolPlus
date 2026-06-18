<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin - {{ $inscription->eleve->nom ?? 'Élève' }}</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #111; }
        .header, .footer { text-align: center; }
        .header h4, .header h5 { margin: 0; }
        .infos { margin-top: 12px; margin-bottom: 18px; }
        table { width: 90%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        .no-border td { border: none; text-align: left; padding: 2px 6px; }
        .title { font-weight: bold; text-transform: uppercase; margin-top: 6px; }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

<div class="header">
    <h4>{{ $ecole?->nom ?? 'Nom de l\'École' }}</h4>
    <div>{{ $ecole?->adresse ?? '' }} {{ $ecole?->telephone ? ' | Tél: '.$ecole->telephone : '' }}</div>
    <h3 class="title">Bulletin - {{ $decoupage?->nom ?? 'Période' }}</h3>
</div>

<div class="infos">
    <table class="no-border">
        <tr>
            <td><strong>Élève :</strong> {{ $inscription->eleve->nom ?? '-' }} {{ $inscription->eleve->prenom ?? '' }}</td>
            <td><strong>Classe :</strong> {{ $inscription->classe->nom ?? '-' }}</td>
            <td><strong>Année :</strong> {{ $inscription->annee->nom ?? '-' }}</td>
            <td><strong>Effectif :</strong> {{ $effectif ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- Si tu veux afficher regroupé par découpages --}}
{{-- Si tu veux afficher regroupé par découpages (ex: trimestre) --}}
@foreach($groupedEvaluations as $decName => $group)
    {{-- ✅ Ajout du saut de page à partir du 2ème découpage --}}
    @if (!$loop->first)
        <div class="page-break"></div>
    @endif

    <h4 style="margin-top:14px;">{{ $decName }}</h4>

    <table>
        <thead>
        <tr>
            <th>Discipline</th>
            <th>Type</th>
            <th>Coef</th>
            <th>Note</th>
            <th>Note × Coef</th>
            <th>Enseignant</th>
            <th>Date</th>
            <th>Observation</th>
        </tr>
        </thead>
        <tbody>
        @foreach($group as $eval)
            @php
                $coef = $eval->matiere->coefficient ?? 1;
                $mcoef = is_numeric($eval->note) ? number_format($eval->note * $coef, 2) : '-';
            @endphp
            <tr>
                <td>{{ $eval->matiere->nom ?? '-' }}</td>
                <td>{{ $eval->typeEvaluation->nom ?? '-' }}</td>
                <td>{{ $coef }}</td>
                <td>{{ $eval->note }}</td>
                <td>{{ $mcoef }}</td>
                <td>{{ $eval->enseignant?->nom ?? $eval->enseignant?->prenom ?? '-' }}</td>
                <td>
                    @if($eval->date_eval)
                        {{ \Carbon\Carbon::parse($eval->date_eval)->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $eval->observation ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Récap par trimestre --}}
    <div style="margin-top:14px;">
        <strong>Moyenne {{ $decName }} :</strong>
        <span>
            {{ $group->avg('note') ? number_format($group->avg('note'), 2) . '/20' : 'N/A' }}
        </span>
    </div>
@endforeach


{{-- Récapitulatif simple --}}
<div style="margin-top:14px;">
    <strong>Moyenne générale :</strong>
    <span>{{ $moyenne !== null ? number_format($moyenne, 2) . '/20' : 'N/A' }}</span>
</div>

{{-- Signatures --}}
<div style="margin-top:40px; display:flex; justify-content:space-between;">
    <div style="text-align:left;">
        <p>Le Professeur Principal</p>
    </div>
    <div style="text-align:right;">
        <p>Le Directeur</p>
    </div>
</div>

</body>
</html>
