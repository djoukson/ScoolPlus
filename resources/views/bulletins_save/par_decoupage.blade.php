<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", sans-serif; font-size: 11px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin: 10px auto; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        .title { font-weight: bold; text-transform: uppercase; margin-top: 6px; }
        .page-break { page-break-after: always; }
        .header-bulletin { display: flex; align-items: center; text-align: center; margin-bottom: 10px; }
        .header-bulletin .gauche { text-align: left; width: 30%; }
        .header-bulletin .centre { text-align: center; flex: 1; }
        .header-bulletin .droite { text-align: right; width: 30%; }
        .btn-actions { margin: 15px; display: flex; gap: 10px; }
        .btn { padding: 8px 15px; font-size: 12px; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: #fff; }
        .btn-retour { background-color: #6c757d; }
        .btn-print { background-color: #007bff; }
        @media print { .btn-actions { display: none; } }
    </style>
</head>
<body>

<div class="btn-actions">
    <a href="{{ url()->previous() }}" class="btn btn-retour">⬅ Retour</a>
    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer</button>
</div>

@foreach($allBulletins as $data)
    @php
        $inscription = $data['inscription'];
        $evaluations = $data['evaluations'];
        $rangsMatieres = $data['rangsMatieres'];
        $statsMatieres = $data['statsMatieres'];
        $effectif = $data['effectif'];
    @endphp

    <div class="page-break">
        <div class="header-bulletin">
            <div class="gauche">
                <p>Ministère des Enseignements</p>
                <p>Primaire et Secondaire</p>
                <p><strong>{{ $ecole?->nom }}</strong></p>
                <p><strong>{{ $ecole?->adresse }}</strong></p>
                <p><strong>{{ $ecole?->telephone }}</strong></p>
                <p><strong>{{ $ecole?->email }}</strong></p>
            </div>
            <div class="centre">
                @if($ecole?->logo && file_exists(public_path('storage/'.$ecole->logo)))
                    <img src="{{ asset('storage/'.$ecole->logo) }}" width="90">
                @endif
            </div>
            <div class="droite" style="text-align: right;">
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <img src="{{ asset('dist/img/Armoiries_du_Togo.png') }}" alt="Armoiries du Togo" style="width: 40px; height: 40px;">
                    <div>
                        <p style="margin: 0; font-weight: bold;">REPUBLIQUE TOGOLAISE</p>
                        <p style="margin: 0;">Travail - Liberté - Patrie</p>
                    </div>
                </div>

                <p><strong>Année : </strong>{{ $inscription->annee->nom ?? '-' }}</p>
                <p><strong>Classe : </strong>{{ $inscription->classe->nom ?? '-' }}</p>
                <p><strong>Effectif : </strong>{{ $effectif ?? '-' }}</p>
            </div>
        </div>
        <hr style="height: 3px;background-color: #0d6efd">

        <h1 class="title" style="text-align: center;">BULLETIN DE NOTES DU </h1>
        <h2 style="text-align: center;color: maroon;font-size: x-large"> {{ strtoupper($decoupage->nom) }}</h2>

        <h2 style="text-align: center;"><b>{{ strtoupper($inscription->eleve->nom) }} {{ ucfirst($inscription->eleve->prenom) }}</b></h2>
        <p style="text-align: right">Sexe : {{ $inscription->eleve->sexe ?? '-' }}</p>
        <table>
            <thead>
            <tr style="background-color:#70aff8">
                <th rowspan="2">Disciplines</th>
                <th colspan="8">Élève</th>
                <th colspan="3">Classe</th>
            </tr>
            <tr style="background-color:#70aff8">
                <th>Clas.</th><th>Comp.</th><th>Moy.T</th><th>Coef</th><th>M.Coef</th><th>Rang</th><th>Appréciation</th><th>Prof</th>
                <th>Fort.M</th><th>Faible.M</th><th>Moy.G</th>
            </tr>
            </thead>
            <tbody>
            @php
                $totalCoef = 0;
                $totalCoefNotes = 0;
            @endphp

            @foreach($data['notesParMatiere'] as $matiereId => $note)

                @php
                    $matiere = $note['matiere'];
                    $coef    = $note['coef'];

                    // Notes (0 si absent)
                    $noteClas = $note['devoir'] ?? 0;
                    $noteComp = $note['compo'] ?? 0;

                    $moyT     = $note['moyenne'];
                    $mCoef    = $note['moycoef'];

                    $totalCoef += $coef;
                    $totalCoefNotes += $mCoef;

                    $prof = $matiere->affectations
                                ->where('classe_id', $inscription->classe->id)
                                ->first()?->enseignant;

                    $app = match(true){
                        $moyT>=18 => "Excellent",
                        $moyT>=16 => "T.bien",
                        $moyT>=14 => "Bien",
                        $moyT>=12 => "A.Bien",
                        $moyT>=10 => "Passable",
                        $moyT>=8  => "Insuffisant",
                        $moyT>=6  => "T.Insuffisant",
                        default   => "Médiocre",
                    };
                @endphp

                <tr>
                    <td style="text-align:left;">{{ $matiere->nom }}</td>

                    <td>{{ $noteClas }}</td>
                    <td>{{ $noteComp }}</td>

                    <td>{{ number_format($moyT,2) }}</td>

                    <td>{{ $coef }}</td>

                    <td>{{ number_format($mCoef,2) }}</td>

                    <td>{{ $rangsMatieres[$matiereId] ?? '-' }}</td>

                    <td>{{ $app }}</td>

                    <td>{{ $prof?->nom ?? '-' }}</td>

                    <td>{{ isset($statsMatieres[$matiereId]) ? number_format($statsMatieres[$matiereId]['fort'],2) : '-' }}</td>
                    <td>{{ isset($statsMatieres[$matiereId]) ? number_format($statsMatieres[$matiereId]['faible'],2) : '-' }}</td>
                    <td>{{ isset($statsMatieres[$matiereId]) ? number_format($statsMatieres[$matiereId]['moyenne'],2) : '-' }}</td>
                </tr>

            @endforeach

            <tr style="font-weight:bold;background-color:#91fbc8">
                <td colspan="4" style="text-align:left;">TOTAL</td>
                <td>{{ $totalCoef }}</td>
                <td>{{ number_format($totalCoefNotes,2) }}</td>
                <td colspan="6"></td>
            </tr>

            </tbody>

        </table>

        <h4 style="text-align:center;margin-top:20px;"><b>Récapitulatif</b></h4>
        <table border="1">
            <thead>
            <tr>
                <th>Période</th>
                <th>Moy. Élève</th>
                <th>Rang</th>
                <th>Appréciation</th>
                <th>M. Classe</th>
                <th>M. Forte</th>
                <th>M. Faible</th>
            </tr>
            </thead>
            <tbody>
            @php
                // Normalisation
                $nomDecoupage = strtolower(trim($decoupage->nom));

                // Détermination si on est en trimestre ou semestre
                $isTrimestre = str_contains($nomDecoupage, 'trimestre');
                $isSemestre  = str_contains($nomDecoupage, 'semestre');

                // Valeur max à afficher
                $maxPeriode = 1;
                $dernierDecoupage = false;

                if($isTrimestre) {
                    if(str_contains($nomDecoupage, '2')) $maxPeriode = 2;
                    elseif(str_contains($nomDecoupage, '3')) {
                        $maxPeriode = 3;
                        $dernierDecoupage = true; // 3ème trimestre = dernier
                    }

                    $ordrePeriodes = [
                        '1er Trimestre' => 1,
                        '2eme Trimestre' => 2,
                        '3eme Trimestre' => 3,
                    ];
                }
                elseif($isSemestre) {
                    if(str_contains($nomDecoupage, '2')) {
                        $maxPeriode = 2;
                        $dernierDecoupage = true; // 2ème semestre = dernier
                    }

                    $ordrePeriodes = [
                        '1er Semestre' => 1,
                        '2eme Semestre' => 2,
                    ];
                }
            @endphp

            {{-- Lignes des périodes normales --}}
            @foreach($data['recap'] as $ligne)
                @php
                    $ordre = $ordrePeriodes[$ligne->periode] ?? null;
                @endphp

                @if($ordre && $ordre <= $maxPeriode)
                    <tr>
                        <td>{{ $ligne->periode }}</td>
                        <td><b>{{ $ligne->moyenne_eleve }}</b></td>
                        <td><b>{{ $ligne->rang }}</b></td>
                        <td>{{ $ligne->appreciation }}</td>
                        <td>{{ $ligne->moyenne_classe }}</td>
                        <td>{{ $ligne->moyenne_forte }}</td>
                        <td>{{ $ligne->moyenne_faible }}</td>
                    </tr>
                @endif
            @endforeach

            @if(isset($data['recap_annuelle']) && $data['recap_annuelle'])
                <tr style="background-color:#f0f0f0; font-weight:bold;">
                    <td>Moyenne Annuelle</td>
                    <td>{{ $data['recap_annuelle']['moyenne'] }}</td>
                    <td>{{ $data['recap_annuelle']['rang'] ?? '-' }}</td>
                    <td>{{ $data['recap_annuelle']['appreciation'] }}</td>
                </tr>
            @endif
            </tbody>
        </table>


        <div style="margin-top:30px;display:flex;justify-content:space-between;">
            <div><p>Le Professeur Principal</p></div>
            <div style="text-align:right;">
                <p>Le Directeur</p>
                <p style="margin-top:50px;"><b>{{ $ecole?->directeur }}</b></p>
            </div>
        </div>
        <hr>
        <div style="display:flex;justify-content:space-between;">
            <span>{{ now()->format('d/m/Y H:i') }}</span>
            <span>SchoolPlus</span>
        </div>
    </div>
@endforeach
</body>
</html>
