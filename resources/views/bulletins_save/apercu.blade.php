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
        .date-heure { display: flex; justify-content: space-between; margin-top: 10px; }

        /* ✅ Style pour les boutons */
        .btn-actions {
            margin: 15px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            font-size: 12px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
        }
        .btn-retour { background-color: #6c757d; }
        .btn-print { background-color: #007bff; }

        @media print {
            .btn-actions { display: none; } /* On cache les boutons à l'impression */
        }
    </style>
</head>
<body>

{{-- ✅ Boutons d’action --}}
<div class="btn-actions">
    <a href="{{ url()->previous() }}" class="btn btn-retour">⬅ Retour</a>
    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer</button>
</div>


@foreach($groupedEvaluations as $decid => $group)
    @php
        $decoupage = \App\Models\Decoupage::find($decid);
        $nomDecoupage = $decoupage ? $decoupage->nom : '-';
        $coefficienttotal = 0;
        $totalCoefNotes = 0;
    @endphp

    {{-- ==================== HEADER ==================== --}}
    <div class="header-bulletin">
        <div class="gauche">
            <p>Ministère des Enseignements</p>
            <p>Primaire et Secondaire</p>
            <p><strong>{{ $ecole?->nom ?? '-' }}</strong></p>
            <p><strong>{{ $ecole?->slogan ?? '' }}</strong></p>
            <p><strong>{{ $ecole?->adresse ?? '-' }} / {{ $ecole?->telephone ?? '-' }}</strong></p>
            <p><strong>{{ $ecole?->email ?? '' }} </strong></p>
        </div>
        <div class="centre">
            <img src="{{ asset('storage/'.$ecole->logo) }}" width="100">
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
    {{-- ==================== TITRE BULLETIN ==================== --}}
    <h1 class="title" style="text-align: center;">BULLETIN DE NOTE DU</h1>
    <h2 style="text-align: center;color: maroon;font-size: x-large">{{ $nomDecoupage }}</h2>
    <h1 style="text-align: center;"><b>{{ $inscription->eleve->nom ?? '-' }} {{ $inscription->eleve->prenom ?? '' }}</b></h1>
    <p style="text-align: right">Sexe : {{ $inscription->eleve->sexe ?? '-' }}</p>
    {{-- ==================== TABLEAU DES NOTES ==================== --}}
    <table>
        <thead>
        <tr style="background-color: #70aff8">
            <th rowspan="2" style="text-align:left;">Disciplines</th>
            <th colspan="8">Élève</th>
            <th colspan="3">Classe</th>
        </tr>
        <tr style="background-color: #70aff8">
            <th>Clas.</th><th>Comp.</th><th>Moy.T</th><th>Coef</th><th>M.Coef</th><th>Rang</th><th>Appréciation</th><th>Prof</th>
            <th>Fort.M</th><th>Faible.M</th><th>Moy.G</th>
        </tr>
        </thead>
        <tbody>
        @php
            $matieresClasse = $inscription->classe->affectations->pluck('matiere');
        @endphp

        @foreach($matieresClasse as $matiere)
            @php
                $evalsMatiere = $group->where('matiere_id', $matiere->id);

                $coef = $matiere->coefficient ?? 1;

                $noteClas = $evalsMatiere->firstWhere('typeEvaluation.nom','Devoir')->note ?? null;
                $noteComp = $evalsMatiere->firstWhere('typeEvaluation.nom','Composition')->note ?? null;

                $moyT = null;
                if($noteClas!==null && $noteComp!==null) $moyT=($noteClas+$noteComp)/2;
                elseif($noteClas!==null) $moyT=$noteClas;
                elseif($noteComp!==null) $moyT=$noteComp;

                $mCoef = $moyT!==null ? $moyT*$coef : null;

                $appreciation='-';
                if($moyT!==null){
                    if($moyT>=18) $appreciation="Excellent";
                    elseif($moyT>=16) $appreciation="Très bien";
                    elseif($moyT>=14) $appreciation="Bien";
                    elseif($moyT>=12) $appreciation="Assez Bien";
                    elseif($moyT>=10) $appreciation="Passable";
                    elseif($moyT>=8) $appreciation="Insuffisant";
                    elseif($moyT>=6) $appreciation="T.Insuffisant";
                    else $appreciation="Médiocre";
                }

                $prof = $matiere->affectations->where('classe_id',$inscription->classe->id)->first()?->enseignant;

                $coefficienttotal += $coef;
                if($mCoef!==null) $totalCoefNotes += $mCoef;
            @endphp
            <tr>
                <td style="text-align:left;">{{ $matiere->nom ?? '-' }}</td>
                <td>{{ $noteClas ?? '0' }}</td>
                <td>{{ $noteComp ?? '0' }}</td>
                <td>{{ $moyT!==null ? number_format($moyT,2) : '0' }}</td>
                <td>{{ $coef }}</td>
                <td>{{ $mCoef!==null ? number_format($mCoef,2) : '0' }}</td>
                <td>{{ $rangsMatieres[$matiere->id] ?? '0' }}</td>
                <td>{{ $appreciation }}</td>
                <td>{{ $prof?->nom ?? '-' }}</td>
                <td>{{ isset($statsMatieres[$matiere->id]) ? number_format($statsMatieres[$matiere->id]['fort'],2) : '-' }}</td>
                <td>{{ isset($statsMatieres[$matiere->id]) ? number_format($statsMatieres[$matiere->id]['faible'],2) : '-' }}</td>
                <td>{{ isset($statsMatieres[$matiere->id]) ? number_format($statsMatieres[$matiere->id]['moyenne'],2) : '-' }}</td>
            </tr>
        @endforeach

        <tr style="font-weight:bold;background-color:#91fbc8">
            <td colspan="4" style="text-align:left;">Total</td>
            <td>{{ $coefficienttotal }}</td>
            <td>{{ $coefficienttotal>0 ? number_format($totalCoefNotes,2) : '-' }}</td>
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
            <th>M. Annuelle</th>
        </tr>
        </thead>
        <tbody>
        @php
            // Normalisation
            $nomDecoupage = strtolower(trim($nomDecoupage));

            // Détermination si on est en trimestre ou semestre
            $isTrimestre = str_contains($nomDecoupage, 'trimestre');
            $isSemestre  = str_contains($nomDecoupage, 'semestre');

            // Valeur max à afficher
            $maxPeriode = 1;

            if($isTrimestre) {
                if(str_contains($nomDecoupage, '2')) $maxPeriode = 2;
                elseif(str_contains($nomDecoupage, '3')) $maxPeriode = 3;

                $ordrePeriodes = [
                    '1er Trimestre' => 1,
                    '2eme Trimestre' => 2, // pour éviter les confusions é/è
                    '3eme Trimestre' => 3,
                ];
            }
            elseif($isSemestre) {
                if(str_contains($nomDecoupage, '2')) $maxPeriode = 2;

                $ordrePeriodes = [
                    '1er Semestre' => 1,
                    '2eme Semestre' => 2,
                ];
            }
        @endphp

        @foreach($recap as $ligne)
            @php
                $ordre = $ordrePeriodes[$ligne->periode] ?? null;
            @endphp

            @if($ordre && $ordre <= $maxPeriode)
                <tr style="font-weight: bold">
                    <td>{{ $ligne->periode }}</td>
                    <td>{{ $ligne->moyenne_eleve }}</td>
                    <td>{{ $ligne->rang }}</td>
                    <td>{{ $ligne->appreciation }}</td>
                    <td>{{ $ligne->moyenne_classe }}</td>
                    <td>{{ $ligne->moyenne_forte }}</td>
                    <td>{{ $ligne->moyenne_faible }}</td>
                    <td>{{ $ligne->moyenne_annuelle }}</td>
                </tr>
            @endif
        @endforeach
        @if(isset($recap_annuelle['moyenne']) && $recap_annuelle['moyenne'])
            <tr style="background-color:#f0f0f0; font-weight:bold;">
                <td>Moyenne Annuelle</td>
                <td>{{ $recap_annuelle['moyenne'] }}</td>
                <td>{{ $recap_annuelle['rang'] ?? '-' }}</td>
                <td>{{ $recap_annuelle['appreciation'] }}</td>
            </tr>
        @endif

        </tbody>
    </table>



    {{-- ==================== DECISION ET SIGNATURE ==================== --}}
    <div style="margin-top:20px;">
        <strong>Decision du Conseil :</strong>
    </div>
    <div style="margin-top:40px; display:flex; justify-content:space-between;">
        <div style="text-align:left;">
            <p>Le Professeur Principal</p>
        </div>
        <div style="text-align:right;">
            <p>Le Directeur</p>
            <p style="margin-top:50px;"><strong>{{ $ecole?->directeur ?? '-' }}</strong></p>
        </div>
    </div>
    <hr style="margin-top:40px">
    <div class="date-heure">
        <span>{{ now()->format('d/m/Y H:i') }}</span>
        <span>SchoolPlus</span>
    </div>

@endforeach

</body>
</html>
