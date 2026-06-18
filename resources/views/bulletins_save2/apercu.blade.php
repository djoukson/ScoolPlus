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
            <p>MINISTERE DE L' EDUCATION NATIONALE</p>
            <p>DRE GRAND LOME / IESG-AGOE-NYIVE</p>
            <p style="font-family: 'Arial Black', sans-serif; font-size: 15px; color: #03306e; margin: -5px 0;">
                <strong>{{ $ecole?->nom }}</strong>
            </p>
            <p>{{ $ecole?->adresse }}</p>
            <p>{{ $ecole?->telephone }}</p>
            <p>{{ $ecole?->email }}</p>
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

            <p><strong>Année : {{ $inscription->annee->nom ?? '-' }}</strong></p>
            <p><strong>Classe : {{ $inscription->classe->nom ?? '-' }}</strong></p>
            <p><strong>Effectif : {{ $effectif ?? '-' }}</strong></p>
        </div>

    </div>
    <hr style="height: 3px;background-color: #0d6efd">

    <h1 class="title" style="text-align: center;">BULLETIN DE NOTES DU </h1>
    <h2 style="text-align: center;color: maroon;font-size: x-large;margin-top: -15px"> {{ strtoupper($nomDecoupage) }}</h2>
    @php
        $qrData = "Ecole={$ecole->nom}\nEleve={$inscription->eleve->nom} {$inscription->eleve->prenom}\nMatricule={$inscription->eleve->id}\nClasse={$inscription->classe->nom}\nAnnee={$inscription->annee->nom}\nRef=SP-{{$inscription->id}}\nSIG=".substr(hash('sha256',$inscription->id.$inscription->eleve->id),0,6);
    @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;margin-top: -10px">

        <!-- Colonne gauche : Nom + Prénom -->
        <div style="display:flex; flex-direction:column; justify-content:center; flex:1;">
            <h2 style="margin:5px; font-size:18px; color:#2c3e50;">
                Nom : <span style="font-weight:700;">{{ strtoupper($inscription->eleve->nom) }}</span>
            </h2>
            <h2 style="margin:5px; font-size:16px; color:#2c3e50; margin-top:4px;">
                Prénom : <span style="font-weight:600;">{{ ucfirst($inscription->eleve->prenom) }}</span>
            </h2>
            <h2 style="margin:5px; font-size:16px; color:#2c3e50; margin-top:4px;">
                Matricule : <span style="font-weight:400;">{{$inscription->eleve->id}}</span>
            </h2>
        </div>

        <!-- Colonne centre : QR Code -->
        <div style="flex:0; text-align:center; padding:5px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
            {!! QrCode::size(80)->generate($qrData) !!}
        </div>

        <!-- Colonne droite : Sexe + Statut -->
        <div style="display:flex; flex-direction:column; justify-content:center; align-items:flex-end; flex:1;">
            <p style="margin:5px; font-size:14px; color:#34495e;">
                Sexe : <span style="font-weight:600;">{{ $inscription->eleve->sexe ?? '-' }}</span>
            </p>
            <p style="margin:5px; font-size:14px; color:#34495e; margin-top:4px;">
                Statut :
                @if($inscription->status_eleve === 'Redoublant')
                    <span style="font-weight:700;">Redoublant</span>
                @else
                    <span style="font-weight:700;">Nouveau</span>
                @endif
            </p>
        </div>

    </div>
    {{-- ==================== TABLEAU DES NOTES ==================== --}}
    <table>
        <thead>
        <tr style="background-color: #70aff8">
            <th rowspan="2" style="text-align:left;">Disciplines</th>
            <th colspan="8">Élève</th>
            <th colspan="4">Classe</th>
        </tr>
        <tr style="background-color: #70aff8">
            <th>Clas.</th><th>Comp.</th><th>Moy.T</th><th>Coef</th><th>M.Coef</th><th>Rang</th><th>Appréciation</th><th>Prof</th>
            <th>Fort.M</th><th>Faible.M</th><th>Moy.G</th><th>Sign.</th>
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
  		 <td></td>
            </tr>
        @endforeach

        <tr style="font-weight:bold;background-color:#91fbc8">
            <td colspan="4" style="text-align:left;">Total</td>
            <td>{{ $coefficienttotal }}</td>
            <td>{{ $coefficienttotal>0 ? number_format($totalCoefNotes,2) : '-' }}</td>
            <td colspan="7"></td>
        </tr>
        </tbody>
    </table>
    <h4 style="text-align:center;margin-top:10px;"><b>Récapitulatif</b></h4>
    <table border="1" style="margin-top: -5px">
        <thead>
        <tr>
            <th>Trimestre</th>
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
    <div style="margin-top:10px;">
        <strong>Decision du Conseil :</strong>
    </div>

    <div style="margin-top:20px;display:flex;justify-content:space-between;align-items:flex-start;">

        <!-- Professeur titulaire -->
        <div>
            <p>Prof. Titulaire</p>

            @php
                // Récupération du titulaire pour la classe et l'année courante
                $titulaire = $inscription->classe->titulaire; // pas de ->first() ici
                $enseignantTitulaire = $titulaire ? $titulaire->enseignant : null;
            @endphp

            @if($enseignantTitulaire)
                <p style="margin-top:50px;"><strong>{{ $enseignantTitulaire->nom }} {{ $enseignantTitulaire->prenom }}</strong></p>
            @else
                <p class="text-danger" style="margin-top:50px;"><strong>Non défini</strong></p>
            @endif
        </div>



        <!-- Tableau central -->
        <div style="text-align:center;">
            <table style="border-collapse:collapse;font-size:12px;margin:auto;">
                <tr>
                    <td style="border:1px solid #000;padding:4px 6px;">Retards</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                    <td style="border:1px solid #000;padding:4px 6px;">Absences</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                </tr>
                <tr>
                    <td style="border:1px solid #000;padding:4px 6px;">Avertissement</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                    <td style="border:1px solid #000;padding:4px 6px;">Exclusion</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                </tr>
                <tr>
                    <td style="border:1px solid #000;padding:4px 6px;">Félicitations</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                    <td style="border:1px solid #000;padding:4px 6px;">Encouragement</td>
                    <td style="border:1px solid #000;width:110px;"></td>
                </tr>
                <tr>
                    <td style="border:1px solid #000;padding:4px 6px;" colspan="2">
                        Tableau d’honneur
                    </td>
                    <td style="border:1px solid #000;width:220px;" colspan="2"></td>
                </tr>
            </table>

        </div>


        <!-- Directeur -->
        <div style="text-align:right;">
            <p>Directeur</p>
            <p style="margin-top:50px;"><b>{{ $ecole?->directeur }}</b></p>
        </div>

    </div>

    <hr>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span>{{ now()->format('d/m/Y H:i') }}</span>

        <div style="font-size:12px;text-align:center;color:darkred;max-width:60%;">
            Ce bulletin est délivré en un seul exemplaire. Aucun duplicata ne sera fourni.
        </div>

        <span style="font-weight:600;">SchoolPlus</span>
    </div>

@endforeach

</body>
</html>
