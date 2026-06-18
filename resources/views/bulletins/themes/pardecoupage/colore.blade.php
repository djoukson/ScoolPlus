<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body{
            font-family:"Times New Roman", Georgia, serif;
            font-size:13px; /* +15% */
            color:#2c2c2c;
            margin:0;
            padding:0;
            background:#f3f6f9;
        }
        .page-break{
            page-break-after: always;
            padding:10px; /* +15% */
            background:#fff;
        }
        .card{
            background:#fff;
            border-radius:6px;
            padding:10px 12px; /* +15% */
            margin-bottom:10px;
            box-shadow:0 1px 4px rgba(0,0,0,0.08);
        }
        .header{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-bottom:8px;
        }
        .header-left,.header-right{
            width:28%;
            font-size:12px; /* +15% */
            line-height:1.35;
        }
        .header-center{
            flex:1;
            text-align:center;
        }
        .header-center h2{
            margin:0;
            font-size:20px; /* +15% */
            color:#1e3a5f;
            letter-spacing:1px;
        }
        .badge{
            display:inline-block;
            margin-top:5px;
            padding:4px 13px; /* +15% */
            border-radius:20px;
            background:#f59e0b; /* orange vif */
            color:#fff;
            font-size:12px; /* +15% */
            font-weight:600;
        }
        .btn-actions{margin:12px 0; display:flex; gap:10px;} /* +15% */
        .btn{padding:8px 14px; font-size:13px; font-weight:600; border:none; border-radius:6px; cursor:pointer; color:#fff; text-decoration:none;}
        .btn-retour{ background:#6b7280; }
        .btn-print{ background:#3b82f6; }
        @media print{ .btn-actions{ display:none; } }

        .identite{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:9px; /* +15% */
            background:#fefae0;
            border-radius:6px;
            margin-bottom:8px;
        }
        .identite div{ flex:1; }
        .qr-code{text-align:center;}

        table{
            width:100%;
            border-collapse:collapse;
            margin:8px 0; /* +15% */
            font-size:12px; /* +15% */
        }
        th, td{
            border:1px solid #d1d5db;
            padding:5px 7px; /* +15% */
            text-align:center;
        }
        th{ background:#3b82f6; color:#fff; font-weight:600; }
        tr:nth-child(even){ background:#fef3c7; }

        h4{text-align:center; font-size:16px; color:#1e3a5f; margin:7px 0;} /* +15% */

        .footer{display:flex;justify-content:space-between;margin-top:10px;font-size:12px;} /* +15% */
        .footer table{width:auto;font-size:12px;border-collapse:collapse;}
        .footer table th,.footer table td{border:1px solid #000;padding:4px 6px;} /* +15% */
        .footer-note{display:flex;justify-content:space-between;font-size:11px;margin-top:4px;} /* +15% */
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

        <div class="card header">
            <div class="header-left">
                <p>MINISTERE DE L'EDUCATION NATIONALE</p>
                <p>DRE GRAND LOME / IESG-AGOE-NYIVE</p>
                <p style="font-size:14px;font-weight:700;color:#1e3a5f">{{ $ecole?->nom }}</p>
                <p>{{ $ecole?->adresse }}</p>
                <p>{{ $ecole?->telephone }}</p>
                <p>{{ $ecole?->email }}</p>
            </div>
            <div class="header-center">
                @if($ecole?->logo && file_exists(public_path('storage/'.$ecole->logo)))
                    <img src="{{ asset('storage/'.$ecole->logo) }}" width="95" style="margin-bottom:4px;">
                @endif
                <h2>BULLETIN DE NOTES</h2>
                <span class="badge">{{ strtoupper($decoupage->nom) }}</span>
            </div>
            <div class="header-right" style="text-align:right;">
                <img src="{{ asset('dist/img/Armoiries_du_Togo.png') }}" width="44"><br>
                <strong>REPUBLIQUE TOGOLAISE</strong><br>
                Travail - Liberté - Patrie
                <hr>
                Année : <b>{{ $inscription->annee->nom ?? '-' }}</b><br>
                Classe : <b>{{ $inscription->classe->nom ?? '-' }}</b><br>
                Effectif : <b>{{ $effectif ?? '-' }}</b>
            </div>
        </div>

        @php
            $qrData = "Ecole={$ecole->nom}\nMatricule={$inscription->eleve->id}\nAnnee={$inscription->annee->nom}\nRef=SP-{{$inscription->id}}\nSIG=".substr(hash('sha256',$inscription->id.$inscription->eleve->id),0,6);
        @endphp
        <div class="card identite">
            <div>
                <b>Nom :</b> {{ strtoupper($inscription->eleve->nom) }}<br>
                <b>Prénom :</b> {{ ucfirst($inscription->eleve->prenom) }}<br>
                <b>Matricule :</b> {{ $inscription->eleve->id }}
            </div>
            <div class="qr-code">{!! QrCode::size(88)->generate($qrData) !!}</div>
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
            <tr style="font-weight:bold;background:#fefae0">
                <td colspan="{{ $maxDevoirs +  4}}" style="text-align:left;">TOTAL</td>
                <td>{{ $totalCoef }}</td>
                <td>{{ number_format($totalCoefNotes,2) }}</td>
                <td colspan="7"></td>
            </tr>
            </tbody>
        </table>

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
                        <td style='color:#3b82f6;font-weight:bold;'>{{ $ligne->moyenne_eleve }}</td>
                        <td><b>{{ $ligne->rang }}</b></td>
                        <td style='color:#3b82f6;font-weight:bold;'>{{ $ligne->appreciation }}</td>
                        <td>{{ $ligne->moyenne_classe }}</td>
                        <td>{{ $ligne->moyenne_forte }}</td>
                        <td>{{ $ligne->moyenne_faible }}</td>
                    </tr>
                @endif
            @endforeach

            @if(isset($data['recap_annuelle']) && $data['recap_annuelle'])
                <tr style="background:#fefae0;font-weight:bold;">
                    <td>Moyenne Annuelle</td>
                    <td>{{ $data['recap_annuelle']['moyenne'] }}</td>
                    <td>{{ $data['recap_annuelle']['rang']??'-' }}</td>
                    <td>{{ $data['recap_annuelle']['appreciation'] }}</td>
                    <td colspan="3"></td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="card footer">
            <div>
                Prof. Titulaire<br><br>
                <b>{{ $inscription->classe->titulaire?->enseignant?->nom ?? 'Non défini' }}</b>
            </div>
            <div style="text-align:center;">
                <table>
                    <thead>
                    <tr>
                        <td>Retards</td><td style="width:104px;"></td>
                        <td>Absences</td><td style="width:104px;"></td>
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
                Directeur<br><br>
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
