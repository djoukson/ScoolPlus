<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Century Gothic", serif; font-size: 11px; color: #000; margin-top: -10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px auto; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        .title { font-weight: bold; text-transform: uppercase; margin-top: 6px; }
        .page-break { page-break-after: always; }
        .header-bulletin { display: flex; align-items: center; text-align: center; margin-bottom: 10px; }
        .header-bulletin .gauche { text-align: left; width: 30%; }
        .header-bulletin .centre { text-align: center; flex: 1; }
        .header-bulletin .droite { text-align: right; width: 30%; }
        .date-heure { display: flex; justify-content: space-between; margin-top: 10px; }

        .btn-actions {
            margin: 15px;
            display: flex;
            gap: 10px;
        }
        .btn { padding: 8px 15px; font-size: 12px; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: #fff; }
        .btn-retour { background-color: #6c757d; }
        .btn-print { background-color: #000; }

        @media print {
            .btn-actions { display: none; }
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
            <p>MINISTERE DE L' EDUCATION NATIONALE</p>
            <p>DRE GRAND LOME / IESG-AGOE-NYIVE</p>
            <p style="font-family: 'Arial Black', sans-serif; font-size: 15px; color: #000000; margin: -7px 0;">
                <strong>{{ $ecole?->nom }}</strong>
            </p>            <p>{{ $ecole?->adresse }}</p>
            <p>{{ $ecole?->telephone }}</p>
            <p>{{ $ecole?->email }}</p>
        </div>
        <div class="centre">
            <img src="{{ asset('storage/'.$ecole->logo) }}" width="100">
        </div>
        <div class="droite">
            <p><strong>REPUBLIQUE TOGOLAISE</strong></p>
            <p>Travail - Liberté - Patrie</p>
            <table style="
    width:70%;
    border-collapse:collapse;
    font-size:11px;
    margin-right:-5px;   /* 👉 aligne à droite */
">
                <tr>
                    <td style="border:1px solid #000; padding:4px; text-align:left;">
                        <strong>Année</strong>
                    </td>
                    <td style="border:1px solid #000; padding:4px; text-align:center;">
                        {{ $inscription->annee->nom ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:4px; text-align:left;">
                        <strong>Classe</strong>
                    </td>
                    <td style="border:1px solid #000; padding:4px; text-align:center;">
                        {{ $inscription->classe->nom ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:4px; text-align:left;">
                        <strong>Effectif</strong>
                    </td>
                    <td style="border:1px solid #000; padding:4px; text-align:center;">
                        {{ $effectif ?? '-' }}
                    </td>
                </tr>
            </table>

        </div>
    </div>
{{--    <hr style="height: 2px; background-color: #000; margin-top: -10px;">--}}

    <h1 class="title" style="text-align: center;">BULLETIN DE NOTES DU </h1>
    <h2 style="text-align: center; font-size: large; margin-top: -15px;">{{ strtoupper($nomDecoupage) }}</h2>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; margin-top: -10px">

        <div style="display:flex; flex-direction:column; justify-content:center; flex:1;">
            <h2 style="margin:5px; font-size:16px;">Nom : <strong>{{ strtoupper($inscription->eleve->nom) }}</strong></h2>
            <h2 style="margin:5px; font-size:16px;">Prénom : <strong>{{ ucfirst($inscription->eleve->prenom) }}</strong></h2>
            <h2 style="margin:5px; font-size:16px;">Matricule : <strong>{{$inscription->eleve->id}}</strong></h2>
        </div>



        <div style="display:flex; flex-direction:column; justify-content:center; align-items:flex-end; flex:1;">
            <p style="margin:5px; font-size:14px;">Sexe : <strong>{{ $inscription->eleve->sexe ?? '-' }}</strong></p>
            <p style="margin:5px; font-size:14px;">Statut :
                @if($inscription->status_eleve === 'Redoublant')
                    <strong>Redoublant</strong>
                @else
                    <strong>Nouveau</strong>
                @endif
            </p>
        </div>

    </div>

    {{-- ==================== TABLEAU DES NOTES ==================== --}}
    <table>
        <thead>
        <tr>
            <th rowspan="2">Disciplines</th>
            <th colspan="7">Élève</th>
            <th colspan="2">Avis</th>
        </tr>
        <tr>
            <th>Clas.</th><th>Comp.</th><th>Moy.T</th><th>Coef</th><th>M.Coef</th><th>Rang</th><th>Appréciation</th><th>Prof</th>
            <th>Sign.</th>
        </tr>
        </thead>
        <tbody>
        @php $matieresClasse = $inscription->classe->affectations->pluck('matiere'); @endphp
        @foreach($matieresClasse as $matiere)
            @php
                $evalsMatiere = $group->where('matiere_id', $matiere->id);
                $coef = $matiere->coefficient ?? 1;
                $noteClas = $evalsMatiere->firstWhere('typeEvaluation.nom','Devoir')->note ?? null;
                $noteComp = $evalsMatiere->firstWhere('typeEvaluation.nom','Composition')->note ?? null;
                $moyT = $noteClas!==null && $noteComp!==null ? ($noteClas+$noteComp)/2 : ($noteClas ?? $noteComp ?? null);
                $mCoef = $moyT!==null ? $moyT*$coef : null;
                $appreciation = '-';
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
                <td></td>
            </tr>
        @endforeach

        <tr style="font-weight:bold;">
            <td colspan="4" style="text-align:left;">Total</td>
            <td>{{ $coefficienttotal }}</td>
            <td>{{ $coefficienttotal>0 ? number_format($totalCoefNotes,2) : '-' }}</td>
            <td colspan="7"></td>
        </tr>
        </tbody>
    </table>

    {{-- ==================== RÉCAPITULATIF ==================== --}}
    <h4 style="text-align:center; margin-top:10px;"><b>Récapitulatif</b></h4>
    <table border="1" style="margin-top: -5px">
        <thead>
        <tr>
            <th colspan="7" style="background-color: rgba(195,195,195,0.63)">Resultats</th>
            <th colspan="3" style="background-color: #fa8484">Sanctions</th>
            <th colspan="2" style="background-color: #faff7a">Presence</th>
        </tr>
        <tr>
            <th>Periode</th>
            <th>Moy. </th>
            <th>Rang</th>
            <th>Mention</th>
            <th>M. Classe</th>
            <th>M. Forte</th>
            <th>M. Faible</th>

            <th>Averti.</th>
            <th>Exclu.</th>
            <th>Conseil</th>

            <th>Absence</th>
            <th>Retard</th>
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
                    <td style='color:red;font-size:12px'>{{ $ligne->moyenne_eleve }}</td>
                    <td>{{ $ligne->rang }}</td>
                    <td style='color:red;font-size:12px'>{{ $ligne->appreciation }}</td>
                    <td>{{ $ligne->moyenne_classe }}</td>
                    <td>{{ $ligne->moyenne_forte }}</td>
                    <td>{{ $ligne->moyenne_faible }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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
        <strong>Decision du Conseil :................................................</strong>
    </div>
    <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <p>Prof. Titulaire</p>
            <p style="margin-top:50px;"><strong>{{ $inscription->classe->titulaire?->enseignant->nom ?? 'Non défini' }}</strong></p>
        </div>



        <div style="text-align:right;">
            <p>Directeur</p>
            <p style="margin-top:50px;"><b>{{ $ecole?->directeur }}</b></p>
        </div>
    </div>

    <hr>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <span>{{ now()->format('d/m/Y H:i') }}</span>
        <div style="font-size:12px; text-align:center; max-width:60%;">
            Ce bulletin est délivré en un seul exemplaire.
        </div>
        <span style="font-weight:600;">SchoolPlus-V 1.12</span>
    </div>

@endforeach

</body>
</html>
