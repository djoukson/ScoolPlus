<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body{
            font-family: "Segoe UI", "Roboto", "Arial", sans-serif;
            font-size:12px;
            color:#1f2937;
            margin:2px;
            padding:2px;
            background:#f8f8f8;
        }

        .page-break{
            page-break-after: always;
            padding:10px;
            background:#fff;
        }

        .card{
            background:#fff;
            border-radius:10px;
            padding:10px 15px;
            margin-bottom:20px;
            box-shadow:0 3px 10px rgba(0,0,0,0.05);
        }

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:5px;
        }
        .header-left, .header-right{
            width:30%;
            font-size:11px;
            line-height:1.2;
        }
        .header-center{
            flex:1;
            text-align:center;
        }
        .header-center h2{
            margin:0;
            font-size:20px;
            color:#7f1d1d;
            letter-spacing:1px;
        }
        .badge{
            display:inline-block;
            margin-top:5px;
            padding:5px 12px;
            border-radius:25px;
            background:#f59e0b;
            color:#fff;
            font-size:10px;
            font-weight:600;
        }

        .btn-actions{
            margin:10px 0;
            display:flex;
            gap:12px;
        }
        .btn{
            padding:8px 16px;
            font-size:13px;
            font-weight:600;
            border:none;
            border-radius:6px;
            cursor:pointer;
            color:#fff;
            text-decoration:none;
        }
        .btn-retour{ background:#6b7280; }
        .btn-print{ background:#7f1d1d; }
        @media print{ .btn-actions{ display:none; } }

        .identite{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:5px;
            background:#fef3c7; /* or très clair */
            border-radius:8px;
            margin-bottom:6px;
        }
        .identite div{ flex:1; }
        .qr-code{text-align:center;}

        table{
            width:100%;
            border-collapse:collapse;
            margin:8px 0;
            font-size:11px;
        }
        th, td{
            border:1px solid #d1d5db;
            padding:5px 7px;
            text-align:center;
        }
        th{
            background:#7f1d1d;
            color:#fff;
            font-weight:600;
        }
        tr:nth-child(even){ background:#fff7ed; } /* or très clair */

        h4{
            text-align:center;
            font-size:14px;
            color:#7f1d1d;
            margin:6px 0;
        }

        .footer{
            display:flex;
            justify-content:space-between;
            margin-top:10px;
            font-size:10px;
        }
        .footer table{
            width:auto;
            font-size:9.5px;
            border-collapse:collapse;
        }
        .footer table th, .footer table td{
            border:1px solid #000;
            padding:3px 5px;
        }

        .footer-note{
            display:flex;
            justify-content:space-between;
            font-size:9px;
            margin-top:4px;
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
                <p style="font-size:12px;font-weight:700;color:#7f1d1d">{{ $ecole?->nom }}</p>
                <p>{{ $ecole?->adresse }}</p>
                <p>{{ $ecole?->telephone }}</p>
                <p>{{ $ecole?->email }}</p>
            </div>
            <div class="header-center">
                @if($ecole?->logo && file_exists(public_path('storage/'.$ecole->logo)))
                    <img src="{{ asset('storage/'.$ecole->logo) }}" width="80" style="margin-bottom:4px;">
                @endif
                <h2>BULLETIN DE NOTES</h2>
                <span class="badge">{{ strtoupper($decoupage->nom) }}</span>
            </div>
            <div class="header-right" style="text-align:right;">
                <img src="{{ asset('dist/img/Armoiries_du_Togo.png') }}" width="35"><br>
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
        <div class="card identite">
            <div>
                <b>Nom :</b> {{ strtoupper($inscription->eleve->nom) }}<br>
                <b>Prénom :</b> {{ ucfirst($inscription->eleve->prenom) }}<br>
                <b>Matricule :</b> {{ $inscription->eleve->id }}
            </div>
            <div class="qr-code">{!! QrCode::size(70)->generate($qrData) !!}</div>
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
                <th rowspan="2" style="text-align:left;">Disciplines</th>
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
                    <td>{{ $app }}</td>
                    <td>{{ $matiere->affectations->where('classe_id',$inscription->classe->id)->first()?->enseignant?->nom ?? '-' }}</td>
                    <td>{{ $statsMatieres[$matiereId]['fort'] ?? '-' }}</td>
                    <td>{{ $statsMatieres[$matiereId]['faible'] ?? '-' }}</td>
                    <td>{{ number_format($statsMatieres[$matiereId]['moyenne'],2) ?? '-' }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="font-weight:bold;background:#fef3c7">
                <td colspan="{{ $maxDevoirs +  4}}" style="text-align:left;">TOTAL</td>
                <td>{{ $totalCoef }}</td>
                <td>{{ number_format($totalCoefNotes,2) }}</td>
                <td colspan="7"></td>
            </tr>
            </tbody>
        </table>

        <!-- RECAP -->
        <h4>Récapitulatif</h4>
        <table>
            <thead>
            <tr>
                <th>Période</th><th>Moy</th><th>Rang</th><th>Appr</th><th>M.Cl</th><th>Fort</th><th>Faible</th>
            </tr>
            </thead>
            <tbody>
            @php
                $nomDecoupage = strtolower(trim($decoupage->nom));
                $isTrimestre = str_contains($nomDecoupage,'trimestre');
                $isSemestre  = str_contains($nomDecoupage,'semestre');
                $maxPeriode = 1; $dernierDecoupage=false;
                if($isTrimestre){ if(str_contains($nomDecoupage,'2')) $maxPeriode=2; elseif(str_contains($nomDecoupage,'3')){$maxPeriode=3;$dernierDecoupage=true;} $ordrePeriodes=['1er Trimestre'=>1,'2eme Trimestre'=>2,'3eme Trimestre'=>3];}
                elseif($isSemestre){ if(str_contains($nomDecoupage,'2')) {$maxPeriode=2;$dernierDecoupage=true;} $ordrePeriodes=['1er Semestre'=>1,'2eme Semestre'=>2];}
            @endphp

            @foreach($data['recap'] as $ligne)
                @php $ordre=$ordrePeriodes[$ligne->periode]??null; @endphp
                @if($ordre && $ordre<=$maxPeriode)
                    <tr>
                        <td>{{ $ligne->periode }}</td>
                        <td style='color:#7f1d1d;font-weight:bold;'>{{ $ligne->moyenne_eleve }}</td>
                        <td><b>{{ $ligne->rang }}</b></td>
                        <td style='color:#7f1d1d;font-weight:bold;'>{{ $ligne->appreciation }}</td>
                        <td>{{ $ligne->moyenne_classe }}</td>
                        <td>{{ $ligne->moyenne_forte }}</td>
                        <td>{{ $ligne->moyenne_faible }}</td>
                    </tr>
                @endif
            @endforeach

            @if(isset($data['recap_annuelle']) && $data['recap_annuelle'])
                <tr style="background:#fef3c7;font-weight:bold;">
                    <td>Moyenne Annuelle</td>
                    <td>{{ $data['recap_annuelle']['moyenne'] }}</td>
                    <td>{{ $data['recap_annuelle']['rang']??'-' }}</td>
                    <td>{{ $data['recap_annuelle']['appreciation'] }}</td>
                    <td colspan="3"></td>
                </tr>
            @endif
            </tbody>
        </table>

        <!-- FOOTER -->
        <div class="card footer">
            <div>
                Prof. Titulaire<br><br><br>
                <b>{{ $inscription->classe->titulaire?->enseignant?->nom ?? 'Non défini' }}</b>
            </div>
            <div style="text-align:center;">
                <table>
                    <thead>
                    <tr>
                        <td>Retards</td><td style="width:90px;"></td>
                        <td>Absences</td><td style="width:90px;"></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Avertissement</td><td></td>
                        <td>Exclusion</td><td></td>
                    </tr>
                    <tr>
                        <td>Félicitations</td><td></td>
                        <td>Encouragement</td><td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:right;">
                Directeur<br><br><br>
                <b>{{ $ecole?->directeur }}</b>
            </div>
        </div>

        <div class="footer-note">
            <span>{{ now()->format('d/m/Y H:i') }}</span>
            <span>Ce bulletin est délivré en un seul exemplaire</span>
            <span><b>SchoolPlus v1.12</b></span>
        </div>

    </div>
@endforeach

</body>
</html>
