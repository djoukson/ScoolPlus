<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body{
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 11px;
            color:#1f2937;
            margin-top:-10px ;
            padding:0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin:7px 0;
        }

        th, td{
            border:1px solid #d1d5db;
            padding:5.5px;
            text-align:center;
        }

        th{
            background:#1e3a8a;
            color:#fff;
            font-weight:600;
        }

        .page-break{
            page-break-after: always;
            padding:10px;
        }

        .card{
            border:1px solid #e5e7eb;
            border-radius:8px;
            padding:10px;
            margin-bottom:10px;
        }

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .header-left{
            width:30%;
            font-size:10px;
        }

        .header-center{
            text-align:center;
            flex:1;
        }

        .header-right{
            width:30%;
            text-align:right;
            font-size:10px;
        }

        .badge{
            display:inline-block;
            padding:3px 8px;
            border-radius:20px;
            background:#2563eb;
            color:#fff;
            font-size:10px;
            font-weight:600;
        }

        .btn-actions{
            margin:10px;
            display:flex;
            gap:10px;
        }

        .btn{
            padding:7px 14px;
            font-size:12px;
            font-weight:600;
            border:none;
            border-radius:6px;
            cursor:pointer;
            color:#fff;
            text-decoration:none;
        }

        .btn-retour{ background:#6b7280; }
        .btn-print{ background:#2563eb; }

        @media print{
            .btn-actions{ display:none; }
        }
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

        <!-- HEADER -->
        <div class="card header">
            <div class="header-left">
                <p>MINISTERE DE L'EDUCATION NATIONALE</p>
                <p>DRE GRAND LOME / IESG-AGOE-NYIVE</p>
                <p style="font-size:14px;font-weight:700;color:#1e40af">
                    {{ $ecole?->nom }}
                </p>
                <p>{{ $ecole?->adresse }}</p>
                <p>{{ $ecole?->telephone }}</p>
                <p>{{ $ecole?->email }}</p>
            </div>

            <div class="header-center">
                @if($ecole?->logo && file_exists(public_path('storage/'.$ecole->logo)))
                    <img src="{{ asset('storage/'.$ecole->logo) }}" width="90">
                @endif
                <h2 style="margin:5px 0">BULLETIN DE NOTES</h2>
                <span class="badge">{{ strtoupper($decoupage->nom) }}</span>
            </div>

            <div class="header-right">
                <img src="{{ asset('dist/img/Armoiries_du_Togo.png') }}" width="40"><br>
                <strong>REPUBLIQUE TOGOLAISE</strong><br>
                Travail - Liberté - Patrie
                <hr>
                Année : <b>{{ $inscription->annee->nom ?? '-' }}</b><br>
                Classe : <b>{{ $inscription->classe->nom ?? '-' }}</b><br>
                Effectif : <b>{{ $effectif ?? '-' }}</b>
            </div>
        </div>

        <!-- IDENTITE ELEVE -->
        @php
            $qrData = "Ecole={$ecole->nom}\nMatricule={$inscription->eleve->id}\nAnnee={$inscription->annee->nom}\nRef=SP-{{$inscription->id}}\nSIG=".substr(hash('sha256',$inscription->id.$inscription->eleve->id),0,6);
        @endphp

        <div class="card" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <b>Nom :</b> {{ strtoupper($inscription->eleve->nom) }}<br>
                <b>Prénom :</b> {{ ucfirst($inscription->eleve->prenom) }}<br>
                <b>Matricule :</b> {{ $inscription->eleve->id }}
            </div>

            <div>
                {!! QrCode::size(80)->generate($qrData) !!}
            </div>

            <div style="text-align:right;">
                Sexe : <b>{{ $inscription->eleve->sexe ?? '-' }}</b><br>
                Statut :
                <b>
                    @if($inscription->status_eleve === 'Redoublant')
                        {{ $inscription->eleve->sexe === 'F' ? 'Redoublante' : 'Redoublant' }}
                    @else
                        {{ $inscription->eleve->sexe === 'F' ? 'Nouvelle' : 'Nouveau' }}
                    @endif
                </b>
            </div>
        </div>

        <!-- TABLE NOTES -->
        <table>
            <thead>
            <tr>
                <th rowspan="2" style="text-align: left;">Disciplines</th>
                <th colspan="{{ $maxDevoirs + 8 }}">Élève</th>
                <th colspan="4">Classe</th>
            </tr>
            <tr>
                {{-- Colonnes devoirs --}}
                @for($i = 1; $i <= $maxDevoirs; $i++)
                    <th>Not.{{ $i }}</th>
                @endfor

                <th>Moy.D</th><th>Comp.</th><th>Moy.</th><th>Coef.</th><th>M.Coef</th><th>Rang</th><th>Appréciation</th><th>Prof</th>
                <th>Fort M.</th><th>Faible M.</th><th>Moy.G</th><th>Sign</th>
            </tr>
            </thead>
            <tbody>

            @php $totalCoef=0; $totalCoefNotes=0; @endphp

            @foreach($data['notesParMatiere'] as $matiereId => $note)
                @php
                    $matiere = $note['matiere'];
                    $coef = $note['coef'];
                    $totalCoef += $coef;
                    $totalCoefNotes += $note['moycoef'];

                    $moyT = $note['moyenne'];

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


                   $nom = $matiere->nom ?? '';
                $mots = preg_split('/\s+/', trim($nom));

                if (count($mots) > 2) {
                    // Créer le sigle (première lettre de chaque mot)
                    $sigle = $matiere->sigle;
                } else {
                    $sigle = $nom;
                }
                @endphp

                <tr>
                    <td style="text-align:left;">{{ $sigle }}</td>

                    {{-- Affichage des devoirs dynamiques --}}
                    @for($i = 0; $i < $maxDevoirs; $i++)
                        <td>
                            {{ $note['devoirs'][$i] ?? '-' }}
                        </td>
                    @endfor
                    <td>{{ number_format($note['moy_devoirs'], 2) }}</td>
                    <td>{{ $note['compo'] ?? 0 }}</td>
                    <td>{{ number_format($note['moyenne'],2) }}</td>
                    <td>{{ $coef }}</td>
                    <td>{{ number_format($note['moycoef'],2) }}</td>
                    <td>{{ $rangsMatieres[$matiereId] ?? '-' }}</td>
                    <td>
                        {{ $app}}
                    </td>
                    <td>
                        {{ $matiere->affectations->where('classe_id',$inscription->classe->id)->first()?->enseignant?->nom ?? '-' }}
                    </td>
                    <td>{{ $statsMatieres[$matiereId]['fort'] ?? '-' }}</td>
                    <td>{{ $statsMatieres[$matiereId]['faible'] ?? '-' }}</td>
                    <td>{{ number_format($statsMatieres[$matiereId]['moyenne'],2) ?? '-' }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr style="font-weight:bold;background:#e0f2fe">
                <td colspan="{{ $maxDevoirs +  4}}" style="text-align:left;">TOTAL</td>
                <td>{{ $totalCoef }}</td>
                <td>{{ number_format($totalCoefNotes,2) }}</td>
                <td colspan="7"></td>
            </tr>
            </tbody>
        </table>

        <!-- RECAP -->
        <h4 style="text-align:center">Récapitulatif</h4>
        <table>
            <thead>
            <tr>
                <th>Période</th><th>Moy</th><th>Rang</th><th>Appr</th><th>M.Cl</th><th>Fort</th><th>Faible</th>
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
                    <td style='color:red;font-size:12px'><b>{{ $ligne->moyenne_eleve }}</b></td>
                    <td><b>{{ $ligne->rang }}</b></td>
                    <td style='color:red;font-size:12px'>{{ $ligne->appreciation }}</td>
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

        <!-- FOOTER -->
        <div class="card" style="display:flex;justify-content:space-between;">
            <div>
                Prof. Titulaire<br><br><br><br>
                <b>{{ $inscription->classe->titulaire?->enseignant?->nom.' '.$inscription->classe->titulaire?->enseignant?->prenom ?? 'Non défini' }}</b>
            </div>
            <div style="text-align:center;">
                <table style="border-collapse:collapse;font-size:10px;">
                    <thead>
                    <tr>
                        <td style="border:1px solid #000;padding:4px 6px;">Retards</td>
                        <td style="border:1px solid #000;width:110px;"></td>
                        <td style="border:1px solid #000;padding:4px 6px;">Absences</td>
                        <td style="border:1px solid #000;width:110px;"></td>
                    </tr>
                    </thead>
                   <tbody>
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
                   </tbody>

                </table>

            </div>
            <div style="text-align:right;">
                Directeur<br><br><br><br>
                <b>{{ $ecole?->directeur }}</b>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:10px;">
            <span>{{ now()->format('d/m/Y H:i') }}</span>
            <span>Ce bulletin est délivré en un seul exemplaire</span>
            <span><b>SchoolPlus v1.12</b></span>
        </div>

    </div>
@endforeach

</body>
</html>
