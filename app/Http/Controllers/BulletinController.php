<?php


namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\BulletinTheme;
use App\Models\Classe;
use App\Models\Decoupage;
use App\Models\Inscription;
use App\Models\Evaluation;
use App\Models\Matiere;
use App\Models\MoyenneGenerale;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Moyenne;
use App\Models\Ecole;
use Illuminate\Support\Str;


class BulletinController extends Controller
{

    public function activerTheme(Request $request)
    {
        $id = $request->theme_id; // ID du thème sélectionné

        // Désactiver tous les thèmes
        BulletinTheme::where('active', true)
            ->update(['active' => false]);

        // Activer le thème choisi
        BulletinTheme::where('id', $id)->update(['active' => true]);

        return back()->with('success', '🎨 Thème activé avec succès');
    }



    public function checkMissingNotes($classeId, $anneeId, $decoupageId)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // Récupérer les inscriptions de la classe
        $inscriptions = Inscription::with('eleve')
            ->where('classe_id', $classeId)
            ->where('annee_id', $anneeId)
            ->get();

        // Récupérer les matières
        $matieres = Affectation::where('classe_id', $classeId)
            ->where('annee_id', $anneeId)
            ->with('matiere')
            ->get()
            ->pluck('matiere');

        // Récupérer toutes les évaluations du découpage
        $evaluations = Evaluation::with('typeEvaluation')
            ->where('decoupage_id', $decoupageId)
            ->get()
            ->groupBy('inscription_id');

        $result = [];

        foreach ($inscriptions as $inscription) {

            $manquantes = [];

            foreach ($matieres as $matiere) {

                $evalsEleve = $evaluations[$inscription->id] ?? collect();
                $evalMatiere = $evalsEleve->where('matiere_id', $matiere->id);

                $hasDevoir2      = $evalMatiere->firstWhere('typeEvaluation.nom', 'Devoir2') !== null;
                $hasDevoir      = $evalMatiere->firstWhere('typeEvaluation.nom', 'Devoir') !== null;
                $hasComposition = $evalMatiere->firstWhere('typeEvaluation.nom', 'Composition') !== null;

                if (!$hasDevoir || !$hasDevoir2 || !$hasComposition) {
                    $manquantes[] = [
                        'matiere' => $matiere->nom,
                        'devoir2' => $hasDevoir2,
                        'devoir' => $hasDevoir,
                        'composition' => $hasComposition
                    ];
                }
            }

            if (!empty($manquantes)) {
                $result[] = [
                    'eleve' => $inscription->eleve->nom . ' ' . $inscription->eleve->prenom,
                    'inscription_id' => $inscription->id,
                    'matieres_manquantes' => $manquantes
                ];
            }
        }

        return $result;
    }

    private function calculerEtSauvegarderMoyennes($classeId,$decoupageId)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $decoupage = \App\Models\Decoupage::findOrFail($decoupageId);
        $anneeId   = $decoupage->annee_id;

        $inscriptions = \App\Models\Inscription::where('classe_id',$classeId)
            ->where('annee_id',$anneeId)
            ->get();

        // 1️⃣ Calcul collectif des moyennes classe
        $moyennesClasse = [];
        foreach ($inscriptions as $inscription){

            $evaluations = \App\Models\Evaluation::with(['typeEvaluation', 'matiere'])
                ->where('decoupage_id', $decoupageId)
                ->where('inscription_id',$inscription->id)
                ->get();

            $matieres = \App\Models\Matiere::whereHas('affectations', function ($q) use ($classeId) {
                $q->where('classe_id',$classeId);
            })->get();

            $totalCoef = 0;
            $totalPoints = 0;

            foreach ($matieres as $matiere){
                $coef = $matiere->coefficient ?? 1;

                $notes = $evaluations->where('matiere_id',$matiere->id);

                $devoir = $notes
                    ->filter(fn($n)=>str_starts_with($n->typeEvaluation->nom,'Devoir'))
                    ->avg('note') ?? 0;

                $compo  = $notes->firstWhere('typeEvaluation.nom','Composition')?->note ?? 0;

                $moy = ($devoir + $compo) / 2;

                $totalCoef += $coef;
                $totalPoints += ($moy * $coef);
            }

            $moyennesClasse[$inscription->id] = $totalCoef > 0 ? round($totalPoints / $totalCoef,2) : 0;
        }

        // 2️⃣ CLASSEMENT
        arsort($moyennesClasse);
        $rangs = [];

        $rang = 1;
        $dernierScore = null;

        foreach ($moyennesClasse as $id => $val) {

            if ($dernierScore !== null && $val < $dernierScore){
                $rang++;
            }

            $rangs[$id] = $rang;
            $dernierScore = $val;
        }

        // 3️⃣ SAVE ELEVES
        foreach ($moyennesClasse as $idInscription => $moy) {

            $app = match (true) {
                $moy >= 18 => 'Excellent',
                $moy >= 16 => 'T.Bien',
                $moy >= 14 => 'Bien',
                $moy >= 12 => 'Assez Bien',
                $moy >= 10 => 'Passable',
                $moy >= 8  => 'Insuffisant',
                $moy >= 6  => 'T.Insuffisant',
                default    => 'Médiocre',
            };


            \App\Models\Moyenne::updateOrCreate(
                [
                    'inscription_id' => $idInscription,
                    'decoupage_id'   => $decoupageId,
                ],
                [
                    'moyenne'      => $moy,
                    'rang'         => $rangs[$idInscription],
                    'appreciation' => $app,
                ]
            );
        }

        // 4️⃣ SAVE MOY CLASS
        \App\Models\MoyenneGenerale::updateOrCreate(
            [
                'classe_id' => $classeId,
                'annee_id' => $anneeId,
                'decoupage_id' => $decoupageId,
            ],
            [
                'moyenne_classe' => round(array_sum($moyennesClasse)/count($moyennesClasse),2),
                'moyenne_forte' => max($moyennesClasse),
                'moyenne_faible' => min($moyennesClasse),
            ]
        );
    }



    public function bulletin($classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $themes = BulletinTheme::all();
        $classe = Classe::findOrFail($classe_id);

        // ✅ Récupérer toutes les évaluations des élèves inscrits dans cette classe
        $evaluations = Evaluation::whereHas('inscription', function($q) use ($classe_id) {
            $q->where('classe_id', $classe_id);
        })
            ->with(['inscription.eleve', 'matiere', 'decoupage', 'typeEvaluation'])
            ->get();

        // ✅ Extraire uniquement les découpages présents dans ces évaluations
        $decoupages = Decoupage::whereIn(
            'id',
            $evaluations->pluck('decoupage_id')->unique()
        )->get();


        logAction('Consultation bulletin', "Consultation des bulletin de la classe {$classe->nom} ");

        return view('bulletins.index', compact('classe', 'evaluations', 'decoupages','themes'));
    }
    public function apercubulletin($inscriptionId, $decoupageId)
    {


        // 1️ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $theme = bulletinThemeActif();


        $view = $theme
            ? $theme->blade
            : 'bulletins.apercu'; // fallback sécurité

        $decoupage = Decoupage::findOrFail($decoupageId);
        $class = Classe::all();
        $ecole = Ecole::first();
        $inscription = Inscription::with('eleve', 'classe')->findOrFail($inscriptionId);
        $anneeId = $inscription->annee_id;
        $idClasse = $inscription->classe->id;
        $recap_annuelle = [];
        // Effectif de la classe
        $inscriptionsClasse = Inscription::where('classe_id', $idClasse)
            ->where('annee_id', $anneeId)
            ->pluck('id');
        $effectif = $inscriptionsClasse->count();

        // voir sil y a des notes manquantes
        // ➤ Si skipCheck = 1, on saute la vérification
        if (request()->has('skipCheck')) {
            goto skip_missing_notes_check;
        }

        $manques = $this->checkMissingNotes($idClasse, $anneeId, $decoupageId);

        if (!empty($manques)) {
            return view('bulletins.missing_modal', [
                'manques' => $manques,
                'inscriptionId' => $inscriptionId,
                'decoupageId' => $decoupageId,
                'idClasse' => $idClasse,
            ]);
        }
        // ➤ Étiquette pour sauter

        skip_missing_notes_check:


        // Toutes les matières affectées à la classe
        $matieresClasse = Affectation::where('classe_id', $idClasse)
            ->where('annee_id', $anneeId)
            ->with('matiere')
            ->get()
            ->pluck('matiere');

        // Toutes les évaluations de la classe pour ce découpage
        $evaluationsClasse = Evaluation::with(['typeEvaluation', 'inscription', 'matiere'])
            ->whereIn('inscription_id', $inscriptionsClasse)
            ->where('decoupage_id', $decoupageId)
            ->get()
            ->groupBy('matiere_id');

        $statsMatieres = [];
        $rangsMatieres = [];

        // Calcul des stats par matière
        foreach ($matieresClasse as $matiere) {
            $matiereId = $matiere->id;

            // Toutes les évaluations pour cette matière
            $evals = $evaluationsClasse[$matiereId] ?? collect();
            $parEleve = $evals->groupBy('inscription_id');

            // Moyenne par élève, 0 si pas de note
            $moyennesEleves = [];
            foreach ($inscriptionsClasse as $eleveId) {
                $notes = $parEleve[$eleveId] ?? collect();
                $devoir = $notes->firstWhere('typeEvaluation.nom', 'Devoir')?->note ?? 0;
                $compo  = $notes->firstWhere('typeEvaluation.nom', 'Composition')?->note ?? 0;
                $moyennesEleves[$eleveId] = ($devoir + $compo) / 2;
            }

            // Rang de l'élève pour cette matière
            $sorted = collect($moyennesEleves)->sortDesc()->values();
            $moyEleve = $moyennesEleves[$inscriptionId] ?? 0;
            $rang = $sorted->search($moyEleve) !== false ? $sorted->search($moyEleve) + 1 : null;
            $rangsMatieres[$matiereId] = $rang;

            // Stats de la matière pour la classe
            $statsMatieres[$matiereId] = [
                'fort'    => max($moyennesEleves),
                'faible'  => min($moyennesEleves),
                'moyenne' => $effectif > 0 ? array_sum($moyennesEleves) / $effectif : 0,
            ];
        }

        // S'assurer que $evaluationsAll est défini
        $evaluationsAll = Evaluation::with(['typeEvaluation', 'matiere'])
            ->whereIn('inscription_id', $inscriptionsClasse)
            ->where('decoupage_id', $decoupageId)
            ->get()
            ->groupBy('inscription_id');

// Moyennes générales pondérées par coefficient
        $moyennesClasse = collect();

        foreach ($inscriptionsClasse as $eleveId) {
            $totalPoints = 0;
            $totalCoef = 0;

            foreach ($matieresClasse as $matiere) {
                $coef = $matiere->coefficient ?? 1;

                // Récupérer les évaluations de l'élève pour cette matière, sinon collection vide
                $evals = $evaluationsAll[$eleveId] ?? collect();
                $evalsMatiere = $evals->where('matiere_id', $matiere->id);

                $devoir = $evalsMatiere->firstWhere('typeEvaluation.nom', 'Devoir')?->note ?? 0;
                $compo  = $evalsMatiere->firstWhere('typeEvaluation.nom', 'Composition')?->note ?? 0;

                // Moyenne de la matière (0 si pas de note)
                $noteMatiere = ($devoir + $compo) / 2;

                // Ajouter au total pondéré
                $totalPoints += $noteMatiere * $coef;

                // Ajouter le coefficient même si la note est 0
                $totalCoef += $coef;
            }

            // Calcul de la moyenne générale de l'élève
            $moyennesClasse[$eleveId] = $totalCoef > 0 ? $totalPoints / $totalCoef : 0;

        }

// Vérification : si l'élève courant n'est pas dans $moyennesClasse, on l'ajoute à 0
        if (!isset($moyennesClasse[$inscriptionId])) {
            $moyennesClasse[$inscriptionId] = 0;
        }

// Calcul du rang général et statistiques
        $sortedClasse = $moyennesClasse->sortDesc()->values();
        $rangGeneral = $sortedClasse->search($moyennesClasse[$inscriptionId]) + 1;
        $plusForte = $moyennesClasse->max();
        $plusFaible = $moyennesClasse->min();
        $moyenneGeneraleClasse = $moyennesClasse->avg();


        // Évaluations de l'élève pour le découpage
        $evaluations = Evaluation::with(['matiere.affectations.enseignant', 'decoupage', 'typeEvaluation'])
            ->where('inscription_id', $inscriptionId)
            ->where('decoupage_id', $decoupageId)
            ->get();
        $groupedEvaluations = $evaluations->groupBy('decoupage.id');


       // dd($groupedEvaluations);
        // Sauvegarde des moyennes par découpage
        $resultatsdecoupageId = [];

        foreach ($matieresClasse as $matiere) {
            $matiereIds[] = $matiere->id;
        }

// On ne boucle plus seulement sur $evaluations, mais sur le découpage
        foreach ($evaluations->groupBy('decoupage.id') as $decId => $evalsDec) {
            $totalCoefNotes = 0;
            $coefficientTotal = 0;

            foreach ($matieresClasse as $matiere) {
                $coef = $matiere->coefficient ?? 1;

                // Récupérer les évaluations de l'élève pour cette matière
                $evalsMatiere = $evalsDec->where('matiere_id', $matiere->id);

                $devoir = $evalsMatiere->firstWhere('typeEvaluation.nom', 'Devoir')?->note ?? 0;
                $compo  = $evalsMatiere->firstWhere('typeEvaluation.nom', 'Composition')?->note ?? 0;

                // Moyenne matière = 0 si pas de note
                $noteMatiere = ($devoir + $compo) / 2;

                // Ajouter au total pondéré
                $totalCoefNotes += $noteMatiere * $coef;

                // Ajouter le coefficient même si note = 0
                $coefficientTotal += $coef;
            }

            $moyenneDecoupage = $coefficientTotal > 0 ? $totalCoefNotes / $coefficientTotal : 0;

            // -- Appréciation complète --
            $app = $moyenneDecoupage >= 18 ? "Excellent" :
                ($moyenneDecoupage >= 16 ? "T.Bien" :
                    ($moyenneDecoupage >= 14 ? "Bien" :
                        ($moyenneDecoupage >= 12 ? "Assez Bien" :
                            ($moyenneDecoupage >= 10 ? "Passable" :
                                ($moyenneDecoupage >= 8 ? "Insuffisant" :
                                    ($moyenneDecoupage >= 6 ? "T.Insuffisant" : "Médiocre")
                                )
                            )
                        )
                    )
                );



            // 🔹 Déterminer si on est au dernier trimestre ou dernier semestre
            $nomDecoupage = strtolower(trim($decoupage->nom));
            $dernierDecoupage = false;

            if(str_contains($nomDecoupage, 'trimestre')) {
                $dernierDecoupage = str_contains($nomDecoupage, '3'); // 3ème trimestre = dernier
            } elseif(str_contains($nomDecoupage, 'semestre')) {
                $dernierDecoupage = str_contains($nomDecoupage, '2'); // 2ème semestre = dernier
            }
            $recap_annuelle = null;
// 🔹 Calcul moyenne annuelle si dernier découpage
            $moyenneAnnuelle = null;

            if ($dernierDecoupage) {
                // 1️⃣ Moyenne annuelle de l'élève
                $moyennesAnnuelles = Moyenne::where('inscription_id', $inscription->id)->pluck('moyenne');

                if ($moyennesAnnuelles->count() > 0) {
                    $moyenneAnnuelle = round($moyennesAnnuelles->avg(), 2);

                    $appAnnuelle = match (true) {
                        $moyenneAnnuelle >= 18 => "Excellent",
                        $moyenneAnnuelle >= 16 => "T.Bien",
                        $moyenneAnnuelle >= 14 => "Bien",
                        $moyenneAnnuelle >= 12 => "A.Bien",
                        $moyenneAnnuelle >= 10 => "Passable",
                        $moyenneAnnuelle >= 8  => "Insuffisant",
                        $moyenneAnnuelle >= 6  => "T.Insuffisant",
                        default              => "Médiocre",
                    };

                    // 🔹 Calcul du rang annuel parmi tous les élèves de la même classe et année
                    $elevesClasseIds = \App\Models\Inscription::where('classe_id', $inscription->classe_id)
                        ->where('annee_id', $inscription->annee_id)
                        ->pluck('id');

                    $moyennesAnnClasse = Moyenne::whereIn('inscription_id', $elevesClasseIds)
                        ->whereIn('decoupage_id', \App\Models\Decoupage::where('annee_id', $decoupage->annee_id)->pluck('id'))
                        ->selectRaw('inscription_id, AVG(moyenne) as moyenne_annuelle')
                        ->groupBy('inscription_id')
                        ->get()
                        ->sortByDesc('moyenne_annuelle')
                        ->values();

                    $rangAnnuel = null;
                    foreach ($moyennesAnnClasse as $index => $m) {
                        if ($m->inscription_id == $inscription->id) {
                            $rangAnnuel = $index + 1;
                            break;
                        }
                    }

                    $recap_annuelle = [
                        'moyenne' => $moyenneAnnuelle,
                        'appreciation' => $appAnnuelle,
                        'rang' => $rangAnnuel,
                    ];
                }
            }



            Moyenne::updateOrCreate(
                ['inscription_id' => $inscriptionId, 'decoupage_id' => $decId],
                ['moyenne' => $moyenneDecoupage, 'rang' => $rangGeneral, 'appreciation' => $app,

                    'moyenne_annuelle'      => $dernierDecoupage ? $moyenneAnnuelle : null,
                    'rang_annuel'           => $dernierDecoupage ? $rangAnnuel : null,
                    'appreciation_annuelle' => $dernierDecoupage ? $appAnnuelle : null,

                ]
            );

            MoyenneGenerale::updateOrCreate(
                ['classe_id' => $idClasse, 'annee_id' => $anneeId, 'decoupage_id'=> $decId],
                ['moyenne_classe' => $moyenneGeneraleClasse,
                    'moyenne_forte' => $plusForte,
                    'moyenne_faible' => $plusFaible,
                    ]
            );

            $resultatsdecoupageId[$decId] = [
                'moyenne'      => $moyenneDecoupage,
                'rang'         => $rangGeneral,
                'appreciation' => $app,
                'max'          => $plusForte,
                'min'          => $plusFaible,
                'moyClasse'    => $moyenneGeneraleClasse,
                'recap_annuelle' => $recap_annuelle,
            ];
        }


        // Récapitulatif
        $recap = Moyenne::where('inscription_id', $inscriptionId)
            ->join('inscriptions','inscriptions.id','=','moyennes.inscription_id')
            ->join('moyennes_generales', function($join) use ($anneeId){
                $join->on('moyennes.decoupage_id','=','moyennes_generales.decoupage_id')
                    ->on('inscriptions.classe_id','=','moyennes_generales.classe_id')
                    ->where('moyennes_generales.annee_id', $anneeId);
            })
            ->join('decoupages','decoupages.id','=','moyennes.decoupage_id')
            ->select(
                'decoupages.nom as periode',
                'moyennes.moyenne as moyenne_eleve',
                'moyennes.rang',
                'moyennes.appreciation',
                'moyennes_generales.moyenne_classe',
                'moyennes_generales.moyenne_forte',
                'moyennes_generales.moyenne_faible',
            )
            ->orderBy('moyennes.decoupage_id')
            ->get();

        // Log
        $eleveNom = $inscription->eleve->nom . ' ' . $inscription->eleve->prenom;
        $classeNom = $inscription->classe->nom ?? 'Classe inconnue';
        logAction('Consultation bulletin élève', "Affichage du bulletin de {$eleveNom} ({$classeNom}) pour le {$decoupage->nom}");

        return view($view, compact(
            'inscription',
            'groupedEvaluations',
            'class',
            'ecole',
            'effectif',
            'statsMatieres',
            'rangsMatieres',
            'rangGeneral',
            'plusForte',
            'plusFaible',
            'moyenneGeneraleClasse',
            'recap',
            'resultatsdecoupageId',
            'recap_annuelle'
        ));
    }


    public function bulletincalculParDecoupage($classeId, $decoupageId)
    {
        set_time_limit(0);

        if (!auth()->check()) return redirect()->route('login');
        if (!in_array(auth()->user()->role, ['admin','directeur','secretaire']))
            return back()->with('error','Accès refusé');

        $decoupage = Decoupage::with('annee')->findOrFail($decoupageId);
        $classe    = Classe::findOrFail($classeId);

        $inscriptions = Inscription::where('classe_id',$classeId)
            ->with('annee')
            ->get();

        if ($inscriptions->isEmpty()) return 1;

        $inscriptionIds = $inscriptions->pluck('id');

        /* ================= NOTES ================= */
        $evaluations = Evaluation::whereIn('inscription_id',$inscriptionIds)
            ->where('decoupage_id',$decoupageId)
            ->with('typeEvaluation')
            ->get()
            ->groupBy(['inscription_id','matiere_id']);

        /* ================= MATIÈRES ================= */
        $matieres = Matiere::whereHas('affectations',
            fn($q)=>$q->where('classe_id',$classeId)
        )->get()->keyBy('id');

        /* ================= MOYENNES ÉLÈVES ================= */
        $moyennesEleves = [];

        foreach ($inscriptions as $inscription) {

            $totalPoints = 0;
            $totalCoef   = 0;

            foreach ($matieres as $matiereId => $matiere) {

                $notes = $evaluations[$inscription->id][$matiereId] ?? collect();

                $moyDevoirs = $notes
                    ->filter(fn($n) => str_starts_with($n->typeEvaluation->nom, 'Devoir'))
                    ->map(fn($n) => $n->note ?? 0)  // note vide = 0
                    ->avg();                        // calcule la moyenne


                $compo = optional(
                    $notes->firstWhere('typeEvaluation.nom','Composition')
                )->note ?? 0;

                $moy  = ($moyDevoirs + $compo) / 2;
                $coef = $matiere->coefficient ?? 1;

                $totalPoints += $moy * $coef;
                $totalCoef   += $coef;
            }

            $moyennesEleves[$inscription->id] =
                $totalCoef ? round($totalPoints/$totalCoef,2) : 0;
        }

        /* ================= STATS CLASSE ================= */
        arsort($moyennesEleves);
        $classeMoy  = array_values($moyennesEleves);

        $moyClasse  = round(array_sum($classeMoy)/count($classeMoy),2);
        $maxClasse  = max($classeMoy);
        $minClasse  = min($classeMoy);

        /* ================= RANGS ================= */
        $rangs = [];
        $pos = 1;
        foreach ($moyennesEleves as $id=>$moy) {
            $rangs[$id] = $pos++;
        }

        /* ================= DETECTION TYPE ANNEE ================= */
        $nomDec = strtolower($decoupage->nom);
        $isTrimestre = str_contains($nomDec,'trimestre');
        $isSemestre  = str_contains($nomDec,'semestre');

        $dernierDecoupage =
            ($isTrimestre && str_contains($nomDec,'3')) ||
            ($isSemestre  && str_contains($nomDec,'2'));

        /* ================= MOYENNES ANNUELLES ================= */
        $moyennesAnnuelles = [];

        if ($dernierDecoupage) {

            $decoupagesAnnee = Decoupage::where('annee_id',$decoupage->annee_id)
                ->when($isTrimestre, fn($q)=>$q->where('nom','like','%Trimestre%'))
                ->when($isSemestre,  fn($q)=>$q->where('nom','like','%Semestre%'))
                ->pluck('id');

            $moyennesAnnuelles = Moyenne::whereIn('decoupage_id',$decoupagesAnnee)
                ->whereIn('inscription_id',$inscriptionIds)
                ->selectRaw('inscription_id, AVG(moyenne) as moy_ann')
                ->groupBy('inscription_id')
                ->get()
                ->keyBy('inscription_id');
        }

        /* ================= SAUVEGARDE ================= */
        foreach ($inscriptions as $inscription) {

            $moy  = $moyennesEleves[$inscription->id];
            $rang = $rangs[$inscription->id];

            $app = match(true){
                $moy>=17=>"Excellent",
                $moy>=16=>"T.Bien",
                $moy>=14=>"Bien",
                $moy>=12=>"A.Bien",
                $moy>=10=>"Passable",
                $moy>=8=>"Insuffisant",
                $moy>=6=>"T.Insuffisant",
                default=>"Médiocre",
            };

            $moyAnn = null;
            $rangAnn = null;
            $appAnn = null;

            if ($dernierDecoupage && isset($moyennesAnnuelles[$inscription->id])) {

                $moyAnn = round($moyennesAnnuelles[$inscription->id]->moy_ann,2);

                $classeAnn = $moyennesAnnuelles
                    ->sortByDesc('moy_ann')
                    ->values();

                $rangAnn = $classeAnn
                        ->search(fn($m)=>$m->inscription_id==$inscription->id) + 1;

                $appAnn = match(true){
                    $moyAnn>=17=>"Excellent",
                    $moyAnn>=16=>"T.Bien",
                    $moyAnn>=14=>"Bien",
                    $moyAnn>=12=>"A.Bien",
                    $moyAnn>=10=>"Passable",
                    $moyAnn>=8=>"Insuffisant",
                    $moyAnn>=6=>"T.Insuffisant",
                    default=>"Médiocre",
                };
            }
            Moyenne::updateOrCreate(
                ['inscription_id'=>$inscription->id,'decoupage_id'=>$decoupageId],
                [
                    'moyenne'=>$moy,
                    'rang'=>$rang,
                    'appreciation'=>$app,
                    'moyenne_annuelle'=>$moyAnn,
                    'rang_annuel'=>$rangAnn,
                    'appreciation_annuelle'=>$appAnn,
                ]
            );
        }

        MoyenneGenerale::updateOrCreate(
            ['classe_id'=>$classeId,'annee_id'=>$decoupage->annee_id,'decoupage_id'=>$decoupageId],
            [
                'moyenne_classe'=>$moyClasse,
                'moyenne_forte'=>$maxClasse,
                'moyenne_faible'=>$minClasse
            ]
        );

        //return redirect()->back()->with('success', 'Calcul effectué avec succès.');
    }

    public function bulletinParDecoupage($classe, $decoupageId)
    {
        set_time_limit(0);

        // 1️⃣ Vérifie si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Vérifie le rôle
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // 3️⃣ Thème du bulletin
        $theme = bulletinThemeActif();

// Vérifie si le thème est actif et contient un blade
        if ($theme && $theme->blade) {
            // On récupère juste le nom du fichier après le dernier point
            $bladeName = Str::afterLast($theme->blade, '.');

            // On pointe vers le dossier pardecoupage
            $view = "bulletins.themes.pardecoupage.$bladeName";
        } else {
            $view = 'bulletins.par_decoupage';
        }
        $this->bulletincalculParDecoupage($classe, $decoupageId);
        // 4️⃣ Données de base
        $ecole = Ecole::first();
        $decoupage = Decoupage::with('annee')->findOrFail($decoupageId);
        $classe = Classe::findOrFail($classe);

        // 5️⃣ Inscription des élèves
        $inscriptions = Inscription::with([
            'eleve',
            'classe',
            'annee',
            'evaluations.matiere.affectations.enseignant',
            'evaluations.typeEvaluation'
        ])
            ->where('classe_id', $classe->id)
            ->get();

        $anneeId = $decoupage->annee->id;
        $allBulletins = [];

        // 6️⃣ Vérification notes manquantes
        if (!request()->has('skipCheck')) {
            $manques = $this->checkMissingNotes($classe->id, $anneeId, $decoupageId);
            if (!empty($manques)) {
                return view('bulletins.missing_modal', [
                    'manques' => $manques,
                    'decoupageId' => $decoupageId,
                    'idClasse' => $classe->id,
                ]);
            }
        }


        // 7️⃣ Charger toutes les évaluations de la classe pour ce découpage
        $evaluationsClasse = Evaluation::with(['typeEvaluation', 'matiere', 'inscription'])
            ->whereIn('inscription_id', $inscriptions->pluck('id'))
            ->where('decoupage_id', $decoupageId)
            ->get();

// Indexer par élève puis matière puis type d'évaluation
        $evalsParEleve = $evaluationsClasse->groupBy('inscription_id')->map(function ($evals) {
            return $evals->groupBy('matiere_id')->map(function ($matiereNotes) {
                return $matiereNotes->keyBy(fn($e) => $e->typeEvaluation->nom);
            });
        });

// 1️⃣ Récupérer tous les types de devoirs existants (Devoir, Devoir2, etc.)
        $tousDevoirs = TypeEvaluation::where('nom', 'like', 'Devoir%')->pluck('nom')->toArray();

// 2️⃣ Fonction de calcul de moyenne pour un élève et une matière
        $calcMoyenne = function ($notes, $matiereId, $tousDevoirs) {
            // Construire la collection des notes des devoirs
            $notesDevoirs = collect($tousDevoirs)->map(function ($type) use ($notes, $matiereId) {
                $note = $notes->firstWhere(fn($n) => $n->typeEvaluation->nom === $type && $n->matiere_id == $matiereId)?->note;
                return is_numeric($note) ? (float) $note : 0;
            });
//var_dump($notesDevoirs);
            $moyDevoirs = $notesDevoirs->avg();
//echo $moyDevoirs;
            // Note de la composition
            $composition = $notes->firstWhere('typeEvaluation.nom', 'Composition')?->note;
            $composition = is_numeric($composition) ? (float)$composition : 0;

            return ($moyDevoirs + $composition) / 2;
        };
// 1️⃣ FIRST: Get all matieres for the class (BEFORE the student loop)
        $matieresClasse = Matiere::whereHas(
            'affectations',
            fn($q) => $q->where('classe_id', $classe->id)
        )->get()->keyBy('id');

        // 1️⃣ FIRST PASS: Calculate all averages for all students
        $moyennesParEleveMatiere = [];

        foreach ($inscriptions as $inscription) {
            $evalsEleve = $evalsParEleve->get($inscription->id, collect());

            foreach ($matieresClasse as $matiereId => $matiere) {
                $notes = $evalsEleve->get($matiereId, collect());
                $moyennesParEleveMatiere[$matiereId][$inscription->id] = $calcMoyenne($notes, $matiereId, $tousDevoirs);
            }
        }

// 2️⃣ SECOND PASS: Calculate ranks and stats for all subjects
        $rangsMatieresGlobal = [];
        $statsMatieresGlobal = [];

        foreach ($matieresClasse as $matiereId => $matiere) {
            $moyennesEleves = collect($moyennesParEleveMatiere[$matiereId]);

            // Sort averages in descending order to determine ranks
            $sortedAverages = $moyennesEleves->sortDesc();

            // Assign ranks (handle ties properly)
            $rank = 1;
            $previousAvg = null;
            $sameRankCount = 0;

            foreach ($sortedAverages as $inscriptionId => $avg) {
                if ($previousAvg !== null && $avg < $previousAvg) {
                    $rank += $sameRankCount;
                    $sameRankCount = 1;
                } else if ($previousAvg !== null && $avg == $previousAvg) {
                    $sameRankCount++;
                } else {
                    $sameRankCount = 1;
                }

                $rangsMatieresGlobal[$matiereId][$inscriptionId] = $rank;
                $previousAvg = $avg;
            }

            // Calculate stats
            $statsMatieresGlobal[$matiereId] = [
                'fort'    => $moyennesEleves->max(),
                'faible'  => $moyennesEleves->min(),
                'moyenne' => $moyennesEleves->avg(),
            ];
        }


        // 9️⃣ Boucle sur chaque élève
        foreach ($inscriptions as $inscription) {

            $idclasse = $inscription->classe->id;

            // Effectif de la classe pour l'année
            $effectif = Inscription::where('classe_id', $idclasse)
                ->where('annee_id', $inscription->annee_id)
                ->count();

            $evaluations = $evalsParEleve->get($inscription->id, collect())->flatten(1);
            if ($evaluations->isEmpty()) continue;


            // Get ranks for this student
            $rangsMatieres = [];
            foreach ($matieresClasse as $matiereId => $matiere) {
                $rangsMatieres[$matiereId] = $rangsMatieresGlobal[$matiereId][$inscription->id] ?? count($inscriptions);
            }

            // Get stats
            $statsMatieres = $statsMatieresGlobal;

            /* =======================
    MATIÈRES DE LA CLASSE
 ======================= */
//            $matieresClasse = Matiere::whereHas(
//                'affectations',
//                fn($q) => $q->where('classe_id', $idclasse)
//            )->get()->keyBy('id');
//

            /* ==========================================
               PRÉ-CALCUL GLOBAL : élève × matière
               (UNE SEULE FOIS – ULTRA RAPIDE)
            ========================================== */
            // Évaluations de cet élève
            $evalsEleve = $evalsParEleve->get($inscription->id, collect());

            foreach ($matieresClasse as $matiereId => $matiere) {
                $notes = $evalsEleve->get($matiereId, collect());
                $moyennesParEleveMatiere[$matiereId][$inscription->id] = $calcMoyenne($notes, $matiereId, $tousDevoirs);
            }

                //    dd(0);
            /* ============================
               RANGS + STATS PAR MATIÈRE
            ============================ */


            foreach ($matieresClasse as $matiereId => $matiere) {

                $moyEleve = $moyennesParEleveMatiere[$matiereId][$inscription->id] ?? 0;
                $moyennesEleves = collect($moyennesParEleveMatiere[$matiereId]);

                $rangsMatieres[$matiereId] =
                    $moyennesEleves->filter(fn($m) => $m > $moyEleve)->count() + 1;
//var_dump($moyennesEleves);
                $statsMatieres[$matiereId] = [
                    'fort'    => $moyennesEleves->max(),
                    'faible'  => $moyennesEleves->min(),
                    'moyenne' => $moyennesEleves->avg(),
                ];
            }


            /* =================================
               MOYENNE GÉNÉRALE ÉLÈVE + DÉTAILS
            ================================= */
            $totalCoefNotes = 0;
            $totalCoef = 0;
            $notesParMatiere = [];

            foreach ($matieresClasse as $matiereId => $matiere) {

                $notesMatiere = $evalsParEleve
                    ->get($inscription->id, collect())
                    ->get($matiereId, collect());

                $moy = $moyennesParEleveMatiere[$matiereId][$inscription->id] ?? 0;
                $coef = $matiere->coefficient ?? 1;

                $totalCoefNotes += $moy * $coef;
                $totalCoef += $coef;

                // reconstruire les devoirs en forçant 0 si absent
                $devoirs = collect($tousDevoirs)->map(function($type) use ($notesMatiere, $matiereId) {
                    $note = $notesMatiere->firstWhere(fn($n) => $n->typeEvaluation->nom === $type && $n->matiere_id == $matiereId)?->note;
                    return is_numeric($note) ? (float) $note : 0;
                })->toArray();

                $moyDevoirs = count($devoirs) ? array_sum($devoirs)/count($devoirs) : 0;

                $compo = $notesMatiere->firstWhere('typeEvaluation.nom','Composition')?->note;
                $compo = is_numeric($compo) ? (float)$compo : 0;

                $notesParMatiere[$matiereId] = [
                    'matiere'      => $matiere,
                    'devoirs'      => $devoirs,
                    'moy_devoirs'  => $moyDevoirs,
                    'compo'        => $compo,
                    'moyenne'      => $moy,
                    'coef'         => $coef,
                    'moycoef'      => $moy * $coef,
                ];
            }

//dd('ok');
            $moyenneEleve = $totalCoef
                ? round($totalCoefNotes / $totalCoef, 2)
                : 0;


            /* ==============================
               MOYENNES + RANG DE LA CLASSE
               (UNE SEULE FOIS)
            ============================== */
            $moyennesClasse = [];

            foreach ($evalsParEleve as $inscriptionId => $evalsEleve) {

                $totalPoints = 0;
                $totalCoefClasse = 0;

                foreach ($matieresClasse as $matiereId => $matiere) {
                    $coef = $matiere->coefficient ?? 1;
                    $moy  = $moyennesParEleveMatiere[$matiereId][$inscriptionId] ?? 0;

                    $totalPoints += $moy * $coef;
                    $totalCoefClasse += $coef;
                }

                $moyennesClasse[$inscriptionId] =
                    $totalCoefClasse ? round($totalPoints / $totalCoefClasse, 2) : 0;
            }

            $moyennesClasse = collect($moyennesClasse);

            $rangGeneral = $moyennesClasse
                    ->filter(fn($m) => $m > $moyenneEleve)
                    ->count() + 1;

            $plusForte = $moyennesClasse->max();
            $plusFaible = $moyennesClasse->min();
            $moyenneGeneraleClasse = $moyennesClasse->avg();


            /* =================
               APPRÉCIATION
            ================= */
            $app = match (true) {
                $moyenneEleve >= 17 => "Excellent",
                $moyenneEleve >= 16 => "T.Bien",
                $moyenneEleve >= 14 => "Bien",
                $moyenneEleve >= 12 => "A.Bien",
                $moyenneEleve >= 10 => "Passable",
                $moyenneEleve >= 8  => "Insuffisant",
                $moyenneEleve >= 6  => "T.Insuffisant",
                default              => "Médiocre",
            };

            // 13️⃣ Découpage final
            $nomDecoupage = strtolower(trim($decoupage->nom));
            $dernierDecoupage = str_contains($nomDecoupage,'trimestre')
                ? str_contains($nomDecoupage,'3')
                : (str_contains($nomDecoupage,'semestre') ? str_contains($nomDecoupage,'2') : false);
            $recap_annuelle = null;
            if ($dernierDecoupage) {
                $elevesClasseIds = Inscription::where('classe_id',$idclasse)
                    ->where('annee_id',$inscription->annee_id)->pluck('id');

                $decoupagesIds = Decoupage::where('annee_id',$decoupage->annee_id)->pluck('id');

                $moyennesAnnClasse = Moyenne::whereIn('inscription_id',$elevesClasseIds)
                    ->whereIn('decoupage_id',$decoupagesIds)
                    ->selectRaw('inscription_id, AVG(moyenne) as moyenne_annuelle')
                    ->groupBy('inscription_id')
                    ->get();

                $moyenneAnnuelle = round(
                    $moyennesAnnClasse->where('inscription_id',$inscription->id)->avg('moyenne_annuelle'),
                    2
                );

                $appAnnuelle = match (true) {
                    $moyenneAnnuelle >= 17 => "Excellent",
                    $moyenneAnnuelle >= 16 => "T.Bien",
                    $moyenneAnnuelle >= 14 => "Bien",
                    $moyenneAnnuelle >= 12 => "A.Bien",
                    $moyenneAnnuelle >= 10 => "Passable",
                    $moyenneAnnuelle >= 8  => "Insuffisant",
                    $moyenneAnnuelle >= 6  => "T.Insuffisant",
                    default                => "Médiocre",
                };

                $moyennesAnnClasse = $moyennesAnnClasse->sortByDesc('moyenne_annuelle')->values();
                $rangAnnuel = $moyennesAnnClasse->search(fn($m) => $m->inscription_id == $inscription->id) + 1;

                $recap_annuelle = [
                    'moyenne' => $moyenneAnnuelle,
                    'appreciation' => $appAnnuelle,
                    'rang' => $rangAnnuel,
                ];
            }


            // 15️⃣ Préparer recap pour Blade
            $recapEleve = Moyenne::query()
                ->where('moyennes.inscription_id',$inscription->id)
                ->join('inscriptions','inscriptions.id','=','moyennes.inscription_id')
                ->join('moyennes_generales', function($join) use ($anneeId) {
                    $join->on('moyennes.decoupage_id','=','moyennes_generales.decoupage_id')
                        ->on('inscriptions.classe_id','=','moyennes_generales.classe_id')
                        ->where('moyennes_generales.annee_id','=',$anneeId);
                })
                ->join('decoupages','decoupages.id','=','moyennes.decoupage_id')
                ->select(
                    'decoupages.nom as periode',
                    'moyennes.moyenne as moyenne_eleve',
                    'moyennes.rang',
                    'moyennes.appreciation',
                    'moyennes_generales.moyenne_classe',
                    'moyennes_generales.moyenne_forte',
                    'moyennes_generales.moyenne_faible',
                )
                ->orderBy('moyennes.decoupage_id')
                ->get();

            $notesParMatiere = $notesParMatiere ?? [];
            $maxDevoirs = collect($allBulletins)
                ->flatMap(fn($b) => $b['notesParMatiere'])
                ->pluck('devoirs')
                ->map(fn($d) => count($d))
                ->max() ?? 0;



            $allBulletins[] = [
                'inscription' => $inscription,
                'evaluations' => $evaluations,
                'rangsMatieres' => $rangsMatieres,
                'statsMatieres' => $statsMatieres,
                'moyenneEleve' => $moyenneEleve,
                'rangGeneral' => $rangGeneral,
                'appreciation' => $app,
                'effectif' => $effectif,
                'plusForte' => $plusForte,
                'plusFaible' => $plusFaible,
                'moyenneGeneraleClasse' => $moyenneGeneraleClasse,
                'recap' => $recapEleve,
                'notesParMatiere' => $notesParMatiere,
                'recap_annuelle' => $recap_annuelle,
            ];
        }
//exit(0);

        logAction(
            'Consultation bulletins par découpage',
            "Consultation du bulletin collectif pour le découpage {$decoupage->nom} de l'année {$decoupage->annee->nom}"
        );

        return view($view, compact('decoupage', 'ecole', 'allBulletins', 'maxDevoirs'));
    }


    public function proclamation($classeId, $decoupageId)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // ✅ recalcul auto AVANT affichage
        $this->calculerEtSauvegarderMoyennes($classeId, $decoupageId);

        $classe    = \App\Models\Classe::with('inscriptions.eleve')->findOrFail($classeId);
        $decoupage = \App\Models\Decoupage::findOrFail($decoupageId);

        // 🔹 Vérifier si on est au dernier découpage (3ème trimestre ou 2ème semestre)
        $nomDecoupage = strtolower(trim($decoupage->nom));
        $estDernierDecoupage = false;

        if(str_contains($nomDecoupage, 'trimestre')) {
            $estDernierDecoupage = str_contains($nomDecoupage, '3'); // 3ème trimestre
        } elseif(str_contains($nomDecoupage, 'semestre')) {
            $estDernierDecoupage = str_contains($nomDecoupage, '2'); // 2ème semestre
        }

        // ✅ récupérer les moyennes
        $moyennes = \App\Models\Moyenne::with('inscription.eleve')
            ->whereHas('inscription', function($q) use ($classeId){
                $q->where('classe_id', $classeId);
            })
            ->where('decoupage_id', $decoupageId)
            ->orderBy('rang')
            ->get();

        return view('bulletins.proclamation', compact(
            'classe',
            'decoupage',
            'moyennes',
            'estDernierDecoupage'
        ));
    }



    public function bulletinParEleve($inscriptionId)
    {
        $ecole = \App\Models\Ecole::first();

        $inscription = \App\Models\Inscription::with(['eleve','classe','annee'])
            ->findOrFail($inscriptionId);

        $anneeId  = $inscription->annee_id;
        $idclasse = $inscription->classe_id;

        // ===========================
        // EFFECTIF CLASSE
        // ===========================
        $inscriptionsClasse = \App\Models\Inscription::where('classe_id', $idclasse)
            ->where('annee_id',$anneeId)
            ->pluck('id');

        $effectif = $inscriptionsClasse->count();

        // ===========================
        // MATIÈRES AFFECTÉES À LA CLASSE
        // ===========================
        $matieresClasse = \App\Models\Affectation::with('matiere')
            ->where('classe_id',$idclasse)
            ->where('annee_id',$anneeId)
            ->get()
            ->pluck('matiere');

        $decoupages = \App\Models\Decoupage::all();

        $groupedEvaluations = [];
        $rangsMatieres = [];
        $statsMatieres = [];

        foreach ($decoupages as $decoupage) {

            // ===========================
            // NOTES DE L'ÉLÈVE
            // ===========================
            $evaluationsEleve = \App\Models\Evaluation::with(['matiere','typeEvaluation'])
                ->where('inscription_id',$inscriptionId)
                ->where('decoupage_id',$decoupage->id)
                ->get();

            // On crée un tableau avec **toutes les matières** et note = 0 si absent
            $evaluationsComplete = $matieresClasse->map(function ($matiere) use ($evaluationsEleve) {
                $evalMatiere = $evaluationsEleve->where('matiere_id', $matiere->id);

                $devoir = $evalMatiere->firstWhere('typeEvaluation.nom','Devoir')?->note ?? 0;
                $compo  = $evalMatiere->firstWhere('typeEvaluation.nom','Composition')?->note ?? 0;

                return [
                    'matiere' => $matiere,
                    'devoir'  => $devoir,
                    'compo'   => $compo,
                    'moyenne'=> ($devoir + $compo)/2,
                    'coef'   => $matiere->coefficient ?? 1
                ];
            });

            $groupedEvaluations[$decoupage->id] = $evaluationsComplete;

            // ===========================
            // TOUTES NOTES DE LA CLASSE
            // ===========================
            $evaluationsClasse = \App\Models\Evaluation::with(['matiere','typeEvaluation'])
                ->whereIn('inscription_id',$inscriptionsClasse)
                ->where('decoupage_id',$decoupage->id)
                ->get()
                ->groupBy('inscription_id');

            // ===========================
            // MOYENNES CLASSE
            // ===========================
            $moyennesClasse = collect();

            foreach ($inscriptionsClasse as $inscId) {

                $totalPoints = 0;
                $totalCoef   = 0;

                foreach ($matieresClasse as $matiere) {

                    $coef = $matiere->coefficient ?? 1;

                    // ✅ CORRECTION: éviter Undefined array key
                    $evalsEleve = $evaluationsClasse->get($inscId, collect())
                        ->where('matiere_id', $matiere->id);

                    $devoir = $evalsEleve->firstWhere('typeEvaluation.nom','Devoir')?->note ?? 0;
                    $compo  = $evalsEleve->firstWhere('typeEvaluation.nom','Composition')?->note ?? 0;

                    $moy = ($devoir + $compo)/2;

                    $totalPoints += $moy * $coef;
                    $totalCoef   += $coef;
                }


                $moyennesClasse[$inscId] = $totalCoef > 0 ? round($totalPoints/$totalCoef,2) : 0;
            }

            if ($moyennesClasse->isEmpty()) continue;

            // ===========================
            // MOYENNE ET RANG DE L'ÉLÈVE
            // ===========================
            $moyenneEleve = $moyennesClasse[$inscriptionId] ?? 0;

            $sortedClasse = $moyennesClasse->sortDesc()->values();

            $rangGeneral = $sortedClasse->search($moyenneEleve);
            $rangGeneral = $rangGeneral !== false ? $rangGeneral + 1 : null;

            // ===========================
            // STATS CLASSE
            // ===========================
            $moyenneGeneraleClasse = round($moyennesClasse->avg(),2);
            $plusForte  = round($moyennesClasse->max(),2);
            $plusFaible = round($moyennesClasse->min(),2);

            // ===========================
            // APPRÉCIATION
            // ===========================
            $app = match(true) {
                $moyenneEleve >= 16 => 'Très Bien',
                $moyenneEleve >= 14 => 'Bien',
                $moyenneEleve >= 12 => 'Assez Bien',
                $moyenneEleve >= 10 => 'Passable',
                default => 'Insuffisant',
            };

            // ===========================
            // SAUVEGARDES ROW ÉLÈVE
            // ===========================
            Moyenne::updateOrCreate(
                [
                    'inscription_id' => $inscriptionId,
                    'decoupage_id'   => $decoupage->id,
                ],
                [
                    'moyenne'      => $moyenneEleve,
                    'rang'         => $rangGeneral,
                    'appreciation' => $app,
                ]
            );

            // ===========================
            // SAUVEGARDES CLASSE
            // ===========================
            MoyenneGenerale::updateOrCreate(
                [
                    'classe_id'    => $idclasse,
                    'annee_id'     => $anneeId,
                    'decoupage_id' => $decoupage->id,
                ],
                [
                    'moyenne_classe'   => $moyenneGeneraleClasse,
                    'moyenne_forte'    => $plusForte,
                    'moyenne_faible'   => $plusFaible,
                    'moyenne_annuelle' => null,
                ]
            );
        }

        // ===========================
        // RECAP BULLETIN
        // ===========================
        $recap = Moyenne::query()
            ->where('moyennes.inscription_id', $inscriptionId)
            ->join('inscriptions', 'inscriptions.id','=','moyennes.inscription_id')
            ->join('moyennes_generales', function ($join) use ($anneeId) {
                $join->on('moyennes.decoupage_id','=','moyennes_generales.decoupage_id')
                    ->on('inscriptions.classe_id','=','moyennes_generales.classe_id')
                    ->where('moyennes_generales.annee_id',$anneeId);
            })
            ->join('decoupages','decoupages.id','=','moyennes.decoupage_id')
            ->select(
                'decoupages.nom as periode',
                'moyennes.moyenne as moyenne_eleve',
                'moyennes.rang',
                'moyennes.appreciation',
                'moyennes_generales.moyenne_classe',
                'moyennes_generales.moyenne_forte',
                'moyennes_generales.moyenne_faible',
                'moyennes_generales.moyenne_annuelle'
            )
            ->orderBy('moyennes.decoupage_id')
            ->get();

        // ===========================
        // LOG
        // ===========================
        logAction(
            'Consultation bulletin élève',
            "Bulletin consulté pour {$inscription->eleve->nom} {$inscription->eleve->prenom} |
         Classe: {$inscription->classe->nom} |
         Année: {$inscription->annee->nom}"
        );

        // ===========================
        // VIEW
        // ===========================
        return view('bulletins.par_eleve', compact(
            'inscription',
            'ecole',
            'effectif',
            'groupedEvaluations',
            'rangsMatieres',
            'statsMatieres',
            'recap'
        ));
    }
}




