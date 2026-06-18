<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use App\Models\Personnel;
use App\Models\Enseignant;
use App\Models\Affectation;
use App\Models\PresenceEnseignant;
use App\Models\TauxHoraire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaireController extends Controller
{
    public function index()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        return view('salaires.index', [
            'personnel' => Personnel::all(),
            'enseignants' => Enseignant::with('niveau')->get(),
        ]);
    }

    public function calculer(Request $request)
    {
        // 1️⃣ Sécurité : utilisateur connecté
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Validation
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);

        /* =========================
         * Personnel administratif
         * ========================= */
        $personnel = Personnel::all()->map(function ($p) {
            return [
                'nom'     => $p->nom,
                'prenom'  => $p->prenom,
                'poste'   => $p->poste,
                'salaire' => $p->salaire,
            ];
        });

        /* =========================
         * Enseignants Primaire
         * ========================= */
        $enseignantsPrimaire = Enseignant::with('niveau')
            ->whereHas('niveau', fn ($q) => $q->where('nom', 'Primaire'))
            ->get()
            ->map(function ($e) {
                return [
                    'nom'     => $e->nom,
                    'prenom'  => $e->prenom,
                    'niveau'  => $e->niveau->nom,
                    'salaire' => $e->salaire_mensuel,
                ];
            });

        $enseignantsCollegeLycee = Enseignant::with('niveau')
            ->whereHas('niveau', fn ($q) => $q->where('nom', '!=', 'Primaire'))
            ->get()
            ->map(function ($e) use ($dateDebut, $dateFin) {

                // 🔹 Taux horaire
                $taux = TauxHoraire::where('niveau_id', $e->niveau_id)->value('taux') ?? 0;

                // 🔹 Affectations
                $affectations = Affectation::where('enseignant_id', $e->id)->get();

                $heuresTotalesPlanifiees = 0;

                foreach ($affectations as $aff) {
                    $emplois = EmploiDuTemps::where('affectation_id', $aff->id)->get();

                    if ($emplois->isEmpty()) continue;

                    $dateTemp = $dateDebut->copy();
                    while ($dateTemp->lte($dateFin)) {
                        $jourNom = ucfirst($dateTemp->locale('fr')->dayName);
                        $heuresJour = $emplois->where('jour', $jourNom)->count();
                        $heuresTotalesPlanifiees += $heuresJour;
                        $dateTemp->addDay();
                    }
                }

                // 🔹 Heures manquées dans presences_enseignants
                $heuresManquees = PresenceEnseignant::where('enseignant_id', $e->id)
                    ->whereBetween('date', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
                    ->sum('nombre_heures');

                // 🔹 Heures réellement payées
                $heuresPayees = max($heuresTotalesPlanifiees - $heuresManquees, 0);

                // 🔹 Salaire final
                $salaire = $heuresPayees * $taux;

                return [
                    'nom'     => $e->nom,
                    'prenom'  => $e->prenom,
                    'niveau'  => $e->niveau->nom,
                    'heures'  => $heuresPayees,
                    'heures_manquees' => $heuresManquees,
                    'taux'    => $taux,
                    'salaire' => $salaire,
                ];
            });

        $totalPersonnel = $personnel->sum('salaire'); // mensuel
        $totalPrimaire  = $enseignantsPrimaire->sum('salaire'); // mensuel
        $totalCollegeLycee = $enseignantsCollegeLycee->sum('salaire'); // calculé

        $grandTotalGlobal = $totalPersonnel + $totalPrimaire + $totalCollegeLycee;


//        dd($grandTotalGlobal);

        return view('salaires.index', compact(
            'personnel',
            'enseignantsPrimaire',
            'enseignantsCollegeLycee',
            'dateDebut',
            'dateFin',
            'grandTotalGlobal'
        ));
    }



    public function pdf(Request $request)
    {
        // 1️⃣ Vérifie si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Validation des dates
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);

        /* =========================
         * Personnel administratif
         * ========================= */
        $personnel = Personnel::all()->map(function($p) {
            return [
                'id' => $p->id,
                'nom' => $p->nom,
                'prenom' => $p->prenom,
                'poste' => $p->poste,
                'salaire' => $p->salaire,
            ];
        });

        /* =========================
         * Enseignants Primaire
         * ========================= */
        $enseignantsPrimaire = Enseignant::with('niveau')
            ->whereHas('niveau', fn($q) => $q->where('nom', 'Primaire'))
            ->get()
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'nom' => $e->nom,
                    'prenom' => $e->prenom,
                    'niveau' => $e->niveau->nom,
                    'salaire' => $e->salaire_mensuel,
                ];
            });

        /* ==================================
         * Enseignants Collège & Lycée
         * ================================== */
        $enseignantsCollegeLycee = Enseignant::with('niveau')
            ->whereHas('niveau', fn ($q) => $q->where('nom', '!=', 'Primaire'))
            ->get()
            ->map(function ($e) use ($dateDebut, $dateFin) {

                // 🔹 Taux horaire
                $taux = TauxHoraire::where('niveau_id', $e->niveau_id)->value('taux') ?? 0;

                // 🔹 Affectations
                $affectations = Affectation::where('enseignant_id', $e->id)->get();

                $heuresTotalesPlanifiees = 0;

                foreach ($affectations as $aff) {
                    $emplois = EmploiDuTemps::where('affectation_id', $aff->id)->get();

                    if ($emplois->isEmpty()) continue;

                    $dateTemp = $dateDebut->copy();
                    while ($dateTemp->lte($dateFin)) {
                        $jourNom = ucfirst($dateTemp->locale('fr')->dayName);
                        $heuresJour = $emplois->where('jour', $jourNom)->count();
                        $heuresTotalesPlanifiees += $heuresJour;
                        $dateTemp->addDay();
                    }
                }

                // 🔹 Heures manquées dans presences_enseignants
                $heuresManquees = PresenceEnseignant::where('enseignant_id', $e->id)
                    ->whereBetween('date', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
                    ->sum('nombre_heures');

                // 🔹 Heures réellement payées
                $heuresPayees = max($heuresTotalesPlanifiees - $heuresManquees, 0);

                // 🔹 Salaire final
                $salaire = $heuresPayees * $taux;

                return [
                    'id' => $e->id,
                    'nom' => $e->nom,
                    'prenom' => $e->prenom,
                    'niveau' => $e->niveau->nom,
                    'heures' => $heuresPayees,
                    'heures_manquees' => $heuresManquees,
                    'taux' => $taux,
                    'salaire' => $salaire,
                ];
            });

        /* =========================
         * Stockage dans salaire_remunerations
         * ========================= */
        // 1️⃣ Personnel administratif
        foreach ($personnel as $p) {
            \App\Models\SalaireRemuneration::updateOrCreate(
                [
                    'personnel_id' => $p['id'],
                    'date_debut'   => $dateDebut->format('Y-m-d'),
                    'date_fin'     => $dateFin->format('Y-m-d'),
                ],
                [
                    'type'    => 'personnel',
                    'nom'     => $p['nom'],
                    'prenom'  => $p['prenom'],
                    'salaire' => $p['salaire'] ?? 0,
                ]
            );
        }

        // 2️⃣ Enseignants Primaire
        foreach ($enseignantsPrimaire as $e) {
            \App\Models\SalaireRemuneration::updateOrCreate(
                [
                    'enseignant_id' => $e['id'],
                    'date_debut'    => $dateDebut->format('Y-m-d'),
                    'date_fin'      => $dateFin->format('Y-m-d'),
                ],
                [
                    'type'    => 'enseignant',
                    'nom'     => $e['nom'],
                    'prenom'  => $e['prenom'],
                    'salaire' => $e['salaire'] ?? 0,
                ]
            );
        }

        // 3️⃣ Enseignants Collège & Lycée
        foreach ($enseignantsCollegeLycee as $e) {
            \App\Models\SalaireRemuneration::updateOrCreate(
                [
                    'enseignant_id' => $e['id'],
                    'date_debut'    => $dateDebut->format('Y-m-d'),
                    'date_fin'      => $dateFin->format('Y-m-d'),
                ],
                [
                    'type'    => 'enseignant',
                    'nom'     => $e['nom'],
                    'prenom'  => $e['prenom'],
                    'salaire' => $e['salaire'] ?? 0,
                ]
            );
        }

        /* =========================
         * Totaux
         * ========================= */
        $totalPersonnel = $personnel->sum('salaire');
        $totalPrimaire  = $enseignantsPrimaire->sum('salaire');
        $totalCollegeLycee = $enseignantsCollegeLycee->sum('salaire');
        $grandTotalGlobal = $totalPersonnel + $totalPrimaire + $totalCollegeLycee;

        /* =========================
         * Générer PDF
         * ========================= */
        return Pdf::loadView('salaires.pdf_global', [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'personnel' => $personnel,
            'enseignantsPrimaire' => $enseignantsPrimaire,
            'enseignantsCollegeLycee' => $enseignantsCollegeLycee,
            'grandTotalGlobal' => $grandTotalGlobal,
        ])->stream("salaires_global_{$dateDebut->format('Y-m-d')}_{$dateFin->format('Y-m-d')}.pdf");
    }




}
