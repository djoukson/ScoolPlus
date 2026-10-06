<?php

namespace App\Http\Controllers;

use App\Models\AttributionBourse;
use App\Models\Ecole;
use App\Models\Inscription;
use App\Models\MontantFrais;
use App\Models\MontantScolarite;
use App\Models\Paiement;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Frais;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // 📌 Récupérer l'année active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $query = Paiement::with(['eleve', 'classe', 'annee', 'frais'])->where('annee_id', $anneeActive->id);

        // 🔹 Recherche par nom d'élève ou nom de classe
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('eleve', fn($q) => $q->where('nom', 'like', "%$search%"))
                ->orWhereHas('classe', fn($q) => $q->where('nom', 'like', "%$search%"));
        }

        // 🔹 Tri par colonne
        if ($request->filled('sort') && in_array($request->direction, ['asc', 'desc'])) {
            $sort = $request->sort;
            $direction = $request->direction;
            if ($sort === 'nom') {
                $query->join('eleves', 'paiements.eleve_id', '=', 'eleves.id')
                    ->orderBy('eleves.nom', $direction)
                    ->select('paiements.*');
            } elseif ($sort === 'classe') {
                $query->join('classes', 'paiements.classe_id', '=', 'classes.id')
                    ->orderBy('classes.nom', $direction)
                    ->select('paiements.*');
            } else {
                $query->orderBy($sort, $direction);
            }
        }

        // 🔹 Récupérer les paiements regroupés par élève
        $paiementsGrouped = $query->get()->groupBy('eleve_id');

        $data = $paiementsGrouped->map(function ($paiementsEleve) {
            $eleve = $paiementsEleve->first()->eleve;
            $classe = $paiementsEleve->first()->classe;
            $annee = $paiementsEleve->first()->annee;

            // 🔹 Récupérer l'inscription directement
            $inscription = \App\Models\Inscription::where('eleve_id', $eleve->id)
                ->where('classe_id', $classe->id)
                ->where('annee_id', $annee->id)
                ->first();

            // 🔹 Montants définis pour cette classe/année
            $montantsFrais = MontantFrais::where('classe_id', $classe->id)
                ->where('annee_id', $annee->id)
                ->get();

            // 🔹 Total par type de frais
            $totalInscription = $inscription?->isReinscrit()
                ? 0
                : $montantsFrais->filter(fn($m) => $m->frais->libelle === "Frais d'Inscription")->sum('montant');
            $totalScolarite = $montantsFrais->filter(fn($m) => $m->frais->libelle === "Scolarité")->sum('montant');

            // 🔹 Total payé par type
            $totalPayéInscription = $paiementsEleve->filter(fn($p) => $p->frais->libelle === "Frais d'Inscription")->sum('montant_paye');
            $totalPayéScolarite = $paiementsEleve->filter(fn($p) => $p->frais->libelle === "Scolarité")->sum('montant_paye');

            // 🔹 Vérifier s’il a une bourse active
            $attribution = $inscription
                ? AttributionBourse::with('bourse.frais')
                    ->where('inscription_id', $inscription->id)
                    ->where('etat', 'active')
                    ->latest('date_attribution')
                    ->first()
                : null;

            $bourse = $attribution?->bourse;
            $reduction = 0;

            if ($bourse) {
                foreach ($bourse->frais as $fraisBourse) {
                    if ($inscription?->isReinscrit() && $fraisBourse->libelle === "Frais d'Inscription") {
                        continue;
                    }
                    $montantFrais = $montantsFrais->firstWhere('frais_id', $fraisBourse->id)?->montant ?? 0;
                    $pourcentage = $fraisBourse->pivot->pourcentage ?? 0;
                    $reduction += $montantFrais * $pourcentage / 100;
                }
            }

            // 🔹 Calculs finaux
            $totalPayé = $totalPayéInscription + $totalPayéScolarite;
            $resteTotal = ($totalInscription + $totalScolarite - $reduction) - $totalPayé;

            return [
                'eleve' => $eleve,
                'classe' => $classe,
                'annee' => $annee,
                'paiements' => $paiementsEleve,
                'bourse' => $bourse,
                'type_inscription' => $inscription?->type_inscription ?? 'Nouveau',
                'total_paye' => $totalPayé,
                'reste_total' => $resteTotal,
            ];
        });

        logAction('Consultation', 'Affichage de la liste complète des paiements avec bourses.');

        return view('paiements.index', compact('data'));
    }

    public function listeeleve($id)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // 🔹 1️⃣ Récupérer l’année active (depuis la session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', "Aucune année scolaire active trouvée.");
        }

        // 🔹 2️⃣ Récupérer l'élève avec ses paiements et inscriptions
        $eleve = Eleve::with(['paiements.frais', 'inscriptions.classe', 'inscriptions.annee'])
            ->findOrFail($id);

        // 🔹 3️⃣ Récupérer l’inscription correspondant à l’année active
        $inscription = $eleve->inscriptions()
            ->where('annee_id', $anneeActive->id)
            ->first();

        if (!$inscription) {
            return back()->with('error', "Cet élève n’a pas d’inscription pour l’année scolaire {$anneeActive->annee}.");
        }

        $classe_id = $inscription->classe_id;
        $annee_id  = $anneeActive->id;

        // 🔹 4️⃣ Récupérer les montants de frais pour cette classe et année
        $montantsFrais = MontantFrais::with('frais')
            ->where('classe_id', $classe_id)
            ->where('annee_id', $annee_id)
            ->get();

        $fraisInscriptionMontant = $inscription->isReinscrit()
            ? 0
            : $montantsFrais->filter(fn($m) => $m->frais->libelle === "Frais d'Inscription")->sum('montant');
        $scolariteMontant        = $montantsFrais->filter(fn($m) => $m->frais->libelle === "Scolarité")->sum('montant');

        // 🔹 5️⃣ Vérifier s’il a une bourse active
        $attribution = \App\Models\AttributionBourse::with('bourse.frais')
            ->where('inscription_id', $inscription->id)
            ->where('etat', 'active')
            ->latest('date_attribution')
            ->first();

        $bourse = $attribution?->bourse;
        $reductionInscription = 0;
        $reductionScolarite = 0;

        if ($bourse) {
            foreach ($bourse->frais as $fraisBourse) {
                $montantFrais = $montantsFrais->firstWhere('frais_id', $fraisBourse->id)?->montant ?? 0;
                $pourcentage = $fraisBourse->pivot->pourcentage ?? 0;

                if ($fraisBourse->libelle === "Frais d'Inscription") {
                    if ($inscription->isReinscrit()) {
                        continue;
                    }
                    $reductionInscription += $montantFrais * $pourcentage / 100;
                } elseif ($fraisBourse->libelle === "Scolarité") {
                    $reductionScolarite += $montantFrais * $pourcentage / 100;
                }
            }
        }

        // 🔹 6️⃣ Calculer les montants payés par type de frais pour l’année active
        $totalPayeInscription = $eleve->paiements
            ->where('annee_id', $annee_id)
            ->where('frais.libelle', "Frais d'Inscription")
            ->sum('montant_paye');

        $totalPayeScolarite = $eleve->paiements
            ->where('annee_id', $annee_id)
            ->where('frais.libelle', "Scolarité")
            ->sum('montant_paye');

        // 🔹 7️⃣ Calculer le reste à payer après réduction
        $restantInscription = $inscription->isReinscrit()
            ? 0
            : ($fraisInscriptionMontant - $reductionInscription) - $totalPayeInscription;
        $restantScolarite   = ($scolariteMontant - $reductionScolarite) - $totalPayeScolarite;

        logAction('Consultation', "Affichage du détail des paiements de l'élève {$eleve->nom} {$eleve->prenom}, classe : {$inscription->classe->nom}, année : {$anneeActive->annee}.");

        return view('paiements.listepareleve', [
            'eleve' => $eleve,
            'totalFrais' => $fraisInscriptionMontant,
            'totalPayé'  => $totalPayeInscription,
            'restant'    => $restantInscription,
            'totalScolarite' => $scolariteMontant,
            'totalPayeScolarite' => $totalPayeScolarite,
            'restantScolarite' => $restantScolarite,
            'bourse' => $bourse,
            'typeInscription' => $inscription->type_inscription,
            'anneeActive' => $anneeActive,
        ]);
    }


    public function create()
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        // 🔹 1. Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $anneeId = $anneeActive ? $anneeActive->id : null;

        // 🔹 2. Récupérer uniquement les élèves inscrits dans l'année active
        $inscriptions = Inscription::with(['eleve', 'classe', 'annee'])
            ->when($anneeId, function ($query) use ($anneeId) {
                $query->where('annee_id', $anneeId);
            })
            ->get();

        // 🔹 3. Récupérer la liste des frais disponibles
        $frais = Frais::all();

        // 🔹 4. Journalisation de l’action
        logAction('Consultation', 'Ouverture du formulaire de création d’un nouveau paiement.');

        // 🔹 5. Retour à la vue avec les données
        return view('paiements.create', compact('inscriptions', 'frais', 'anneeActive'));
    }


    public function infosFrais($eleveId, $fraisId)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // 🔹 1️⃣ Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return response()->json(['error' => 'Aucune année scolaire active trouvée.'], 404);
        }

        // 🔹 2️⃣ Vérifier que l’élève existe
        $eleve = Eleve::findOrFail($eleveId);

        // 🔹 3️⃣ Récupérer l’inscription correspondant à l’année active
        $inscription = Inscription::where('eleve_id', $eleveId)
            ->where('annee_id', $anneeActive->id)
            ->first();

        if (!$inscription) {
            return response()->json(['error' => 'Cet élève n\'est pas inscrit pour l’année scolaire active.'], 404);
        }

        $classeId = $inscription->classe_id;
        $anneeId  = $inscription->annee_id;

        $frais = Frais::findOrFail($fraisId);
        if ($inscription->isReinscrit() && $frais->libelle === "Frais d'Inscription") {
            $dejaPaye = Paiement::where('eleve_id', $eleveId)
                ->where('classe_id', $classeId)
                ->where('annee_id', $anneeId)
                ->where('frais_id', $fraisId)
                ->sum('montant_paye');

            return response()->json([
                'eleve' => $eleve->nom . ' ' . $eleve->prenom,
                'classe' => $inscription->classe->nom ?? '',
                'annee' => $anneeActive->nom ?? '',
                'frais' => $frais->libelle,
                'total' => 0,
                'reduction_bourse' => 0,
                'deja_paye' => $dejaPaye,
                'reste' => 0,
                'exonere' => true,
            ]);
        }

        // 🔹 4️⃣ Montant prévu
        $montantTotal = MontantFrais::where('classe_id', $classeId)
            ->where('annee_id', $anneeId)
            ->where('frais_id', $fraisId)
            ->value('montant') ?? 0;

        // 🔹 5️⃣ Montant déjà payé
        $dejaPaye = Paiement::where('eleve_id', $eleveId)
            ->where('frais_id', $fraisId)
            ->where('annee_id', $anneeId)
            ->sum('montant_paye');

        // 🔹 6️⃣ Vérifier si l’élève a une bourse active pour l’année
        $attribution = AttributionBourse::with('bourse.frais')
            ->where('inscription_id', $inscription->id)
            ->where('etat', 'active')
            ->latest('date_attribution')
            ->first();

        $reduction = 0;
        if ($attribution && $attribution->bourse) {
            foreach ($attribution->bourse->frais as $fraisBourse) {
                if ($fraisBourse->id == $fraisId) {
                    $pourcentage = $fraisBourse->pivot->pourcentage ?? 0;
                    $reduction += $montantTotal * $pourcentage / 100;
                }
            }
        }

        // 🔹 7️⃣ Calcul du reste
        $reste = max(0, $montantTotal - $reduction - $dejaPaye);

        // 🔹 8️⃣ Journalisation
        logAction('Consultation', "Vérification du montant restant pour l’élève {$eleve->nom} {$eleve->prenom}, classe {$inscription->classe->nom}, année {$anneeActive->nom}, frais ID {$fraisId}.");

        // 🔹 9️⃣ Réponse JSON
        return response()->json([
            'eleve' => $eleve->nom . ' ' . $eleve->prenom,
            'classe' => $inscription->classe->nom ?? '',
            'annee' => $anneeActive->nom ?? '',
            'frais' => Frais::find($fraisId)->libelle ?? '',
            'total' => $montantTotal,
            'reduction_bourse' => $reduction,
            'deja_paye' => $dejaPaye,
            'reste' => $reste,
        ]);
    }





    public function store(Request $request)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // ✅ Récupérer le montant attendu pour ce frais précis
        $montantTotal = \App\Models\MontantFrais::where('classe_id', $request->classe_id)
            ->where('annee_id', $request->annee_id)
            ->where('frais_id', $request->frais_id)
            ->value('montant'); // récupère la valeur exacte (pas une collection)

        $validated = $request->validate([
            'eleve_id'      => 'required|exists:eleves,id',
            'annee_id'      => 'required|exists:annees_scolaires,id',
            'classe_id'     => 'required|exists:classes,id',
            'frais_id'      => 'required|exists:frais,id',
            'date_paiement' => 'required|date',
            'montant_paye'  => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($montantTotal) {
                    if ($value > $montantTotal) {
                        $fail("Le montant payé ne peut pas dépasser le montant total autorisé ($montantTotal FCFA).");
                    }
                }
            ],
            'mode_paiement' => ['required', 'in:Espèces,Mobile Money,Chèque,Virement'],
        ]);

        $inscription = Inscription::where('eleve_id', $validated['eleve_id'])
            ->where('classe_id', $validated['classe_id'])
            ->where('annee_id', $validated['annee_id'])
            ->firstOrFail();
        $fraisSelectionne = Frais::findOrFail($validated['frais_id']);
        if ($inscription->isReinscrit() && $fraisSelectionne->libelle === "Frais d'Inscription") {
            return back()->withInput()->withErrors(['frais_id' => 'Les frais d’inscription ne sont pas dus pour un élève réinscrit.']);
        }

        Paiement::create($validated + ['inscription_id' => $inscription->id]);
        // 🔹 Log enregistrement paiement
        $eleve = Eleve::find($request->eleve_id);
        logAction('Création', "Nouveau paiement enregistré pour l’élève {$eleve->nom} {$eleve->prenom}, montant : {$request->montant_paye} FCFA, mode : {$request->mode_paiement}, année : {$request->annee_id}, classe : {$request->classe_id}.");

        return redirect()->route('paiements.index')->with('success', 'Paiement enregistré avec succès.');
    }


    public function show(Paiement $paiement)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $paiement->load(['eleve', 'classe', 'frais', 'annee']);
        logAction('Consultation', "Affichage du paiement ID {$paiement->id} de l’élève {$paiement->eleve->nom} {$paiement->eleve->prenom} ({$paiement->montant_paye} FCFA).");

        return view('paiements.show', compact('paiement'));
    }

    public function edit(Paiement $paiement)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // Toutes les inscriptions (avec élève, classe et année)
        $inscriptions = Inscription::with(['eleve', 'classe', 'annee'])->get();

        // Tous les frais disponibles
        $frais = Frais::all();
        logAction('Consultation', "Ouverture du formulaire d’édition du paiement ID {$paiement->id}, élève : {$paiement->eleve->nom} {$paiement->eleve->prenom}.");

        // Pas besoin de $eleves ici
        return view('paiements.edit', compact('paiement', 'inscriptions', 'frais'));
    }

    public function update(Request $request, Paiement $paiement)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $ancienMontant = $paiement->montant_paye;

        $validated = $request->validate([
            'eleve_id'      => 'required|exists:eleves,id',
            'annee_id'      => 'required|exists:annees_scolaires,id',
            'classe_id'     => 'required|exists:classes,id',
            'frais_id'      => 'required|exists:frais,id',
            'montant_paye'  => 'required|numeric|min:0',
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|string|max:50',
        ]);

        $inscription = Inscription::where('eleve_id', $validated['eleve_id'])
            ->where('classe_id', $validated['classe_id'])
            ->where('annee_id', $validated['annee_id'])
            ->firstOrFail();
        $fraisSelectionne = Frais::findOrFail($validated['frais_id']);
        if ($inscription->isReinscrit() && $fraisSelectionne->libelle === "Frais d'Inscription") {
            return back()->withInput()->withErrors(['frais_id' => 'Les frais d’inscription ne sont pas dus pour un élève réinscrit.']);
        }

        $paiement->update($validated + ['inscription_id' => $inscription->id]);
        logAction('Modification', "Paiement ID {$paiement->id} modifié pour l’élève {$paiement->eleve->nom} {$paiement->eleve->prenom}. Ancien montant : {$ancienMontant} FCFA, nouveau montant : {$paiement->montant_paye} FCFA.");

        // Rediriger vers la page des paiements de l'élève
        return redirect()->route('paiements.listeeleve', $paiement->eleve_id)
            ->with('success', 'Paiement mis à jour avec succès.');
    }


    public function destroy(Paiement $paiement)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $details = "Paiement supprimé pour l’élève {$paiement->eleve->nom} {$paiement->eleve->prenom} ({$paiement->classe->nom}, {$paiement->annee->annee}), Frais : {$paiement->frais->libelle}, Montant : {$paiement->montant_paye} FCFA.";

        $paiement->delete();
        logAction('Suppression', $details);

        return redirect()->route('paiements.listeeleve', $paiement->eleve_id)->with('success', 'Paiement supprimé.');
    }

    // Imprimer un paiement spécifique
    public function print($id)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();


        $paiement = Paiement::with(['eleve', 'classe', 'annee', 'frais'])->findOrFail($id);

        // Récupère les infos de l'école (première ligne de la table)
        $ecole = \App\Models\Ecole::first();
        $annee = AnneesScolaire::FindOrFail($paiement->annee->id);
        $anneenom = $annee->nom;
        //dd($anneenom);
        logAction('Impression', "Impression du reçu du paiement ID {$paiement->id} ({$paiement->eleve->nom} {$paiement->eleve->prenom}, {$paiement->classe->nom}, {$paiement->annee->annee}, Montant : {$paiement->montant_paye} FCFA).");

        return view('paiements.print', compact('paiement', 'ecole','anneenom'));
    }


// Imprimer tout l'état d'un élève
    public function printAll($id)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // ✅ Vérification d'authentification
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        // 🔹 1️⃣ Récupérer l’année active (depuis la session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // 🔹 2️⃣ Récupérer l'élève avec ses paiements et inscriptions
        $eleve = Eleve::with(['paiements.frais', 'inscriptions.classe', 'inscriptions.annee'])->findOrFail($id);

        // 🔹 3️⃣ Récupérer l’inscription pour l’année active
        $inscription = $eleve->inscriptions()->where('annee_id', $anneeActive->id)->first();
        if (!$inscription) {
            return redirect()->back()->with('error', 'Cet élève n’a pas d’inscription pour l’année scolaire active.');
        }

        $classe_id = $inscription->classe_id;
        $annee_id  = $anneeActive->id;

        // 🔹 4️⃣ Récupérer les montants pour frais d’inscription et scolarité
        $montantsFrais = MontantFrais::with('frais')
            ->where('classe_id', $classe_id)
            ->where('annee_id', $annee_id)
            ->get();

        $fraisInscriptionMontant = $inscription->isReinscrit()
            ? 0
            : $montantsFrais->filter(fn($m) => $m->frais->libelle === "Frais d'Inscription")->sum('montant');
        $scolariteMontant        = $montantsFrais->filter(fn($m) => $m->frais->libelle === "Scolarité")->sum('montant');

        // 🔹 5️⃣ Vérifier s’il a une bourse active
        $attribution = AttributionBourse::with('bourse.frais')
            ->where('inscription_id', $inscription->id)
            ->where('etat', 'active')
            ->latest('date_attribution')
            ->first();

        $bourse = $attribution?->bourse;
        $reductionInscription = 0;
        $reductionScolarite = 0;

        if ($bourse) {
            foreach ($bourse->frais as $fraisBourse) {
                $montantFrais = $montantsFrais->firstWhere('frais_id', $fraisBourse->id)?->montant ?? 0;
                $pourcentage = $fraisBourse->pivot->pourcentage ?? 0;

                if ($fraisBourse->libelle === "Frais d'Inscription") {
                    if ($inscription->isReinscrit()) {
                        continue;
                    }
                    $reductionInscription += $montantFrais * $pourcentage / 100;
                } elseif ($fraisBourse->libelle === "Scolarité") {
                    $reductionScolarite += $montantFrais * $pourcentage / 100;
                }
            }
        }

        // 🔹 6️⃣ Calcul du total déjà payé pour chaque type de frais (année filtrée)
        $totalPayeInscription = $eleve->paiements
            ->where('annee_id', $annee_id)
            ->where('frais.libelle', "Frais d'Inscription")
            ->sum('montant_paye');

        $totalPayeScolarite = $eleve->paiements
            ->where('annee_id', $annee_id)
            ->where('frais.libelle', "Scolarité")
            ->sum('montant_paye');

        // 🔹 7️⃣ Calcul du reste à payer après application de la bourse
        $restantInscription = $inscription->isReinscrit()
            ? 0
            : ($fraisInscriptionMontant - $reductionInscription) - $totalPayeInscription;
        $restantScolarite   = ($scolariteMontant - $reductionScolarite) - $totalPayeScolarite;

        // 🔹 8️⃣ Infos de l’école
        $ecole = Ecole::first();
        $annee = $anneeActive;
        $anneenom = $annee->nom ?? ($annee->libelle ?? 'Année non précisée');

        // 🔹 9️⃣ Journalisation
        logAction('Impression', "Impression de l’état complet des paiements de {$eleve->nom} {$eleve->prenom} ({$inscription->classe->nom}, {$annee->nom}).");

        // 🔹 🔟 Retour à la vue
        return view('paiements.print_all', [
            'eleve' => $eleve,
            'ecole' => $ecole,
            'annee' => $annee,
            'anneenom' => $anneenom,
            'totalFrais' => $fraisInscriptionMontant,
            'totalPayé'  => $totalPayeInscription,
            'restant'    => $restantInscription,
            'totalScolarite' => $scolariteMontant,
            'totalPayeScolarite' => $totalPayeScolarite,
            'restantScolarite' => $restantScolarite,
            'bourse' => $bourse,
            'typeInscription' => $inscription->type_inscription,
        ]);
    }




    public function etatScolarites()
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // 📌 Récupérer l'année active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        // Récupérer toutes les classes de cette année avec effectif et montants_frais
        $classes = Classe::where('annee_id', $anneeActive->id)
            ->withCount(['inscriptions' => function($query) use ($anneeActive) {
                $query->where('annee_id', $anneeActive->id);
            }])
            ->with(['montantsFrais' => function($query) use ($anneeActive) {
                $query->where('annee_id', $anneeActive->id);
            }])
            ->get();

        foreach ($classes as $classe) {
            $classe->effectif = $classe->inscriptions_count;
            $montantInscription = $classe->montantsFrais
                ->filter(fn($montant) => $montant->frais?->libelle === "Frais d'Inscription")
                ->sum('montant');
            $montantScolarite = $classe->montantsFrais
                ->filter(fn($montant) => $montant->frais?->libelle !== "Frais d'Inscription")
                ->sum('montant');
            $nouveaux = Inscription::where('classe_id', $classe->id)
                ->where('annee_id', $anneeActive->id)
                ->where('type_inscription', 'Nouveau')
                ->count();
            $classe->total_frais = $montantScolarite + ($classe->effectif > 0 ? $montantInscription * $nouveaux / $classe->effectif : 0);
            $classe->prevision = $montantScolarite * $classe->effectif + $montantInscription * $nouveaux;

            // Somme totale déjà payée pour cette classe
            $classe->montant_paye = Paiement::where('classe_id', $classe->id)
                ->where('annee_id', $anneeActive->id)
                ->sum('montant_paye');

            // Pourcentage de paiement réel
            $classe->pourcentage_paye = $classe->prevision > 0
                ? round(($classe->montant_paye / $classe->prevision) * 100)
                : 0;
        }
        // 🔹 Infos de l'école
        $ecole = Ecole::first(); // ou Auth::user()->ecole si applicable
        $anneenom = $ecole->annee ?? AnneesScolaire::where('active', 1)->first();


        logAction('Consultation', 'Consultation de l’état de la scolarité (liste des classes)');

        return view('paiements.etat_scolarite', compact('classes','anneenom','ecole'));
    }

    public function etatPaiementParClasse($classeId, $anneeId = null)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','comptable'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // ===========================
        // 📌 Année scolaire active
        // ===========================
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $classe = Classe::findOrFail($classeId);

        $annee   = $anneeActive->nom;
        $anneeId = $anneeActive->id;


        // ===========================
        // 📌 Filtres reçus
        // ===========================
        $statut = request('statut');        // impayes | soldes | null
        $seuil  = request('montant_max');   // montant max payé


        // ===========================
        // 📌 Récupération inscriptions
        // ===========================
        $inscriptions = Inscription::with('eleve')
            ->where('classe_id', $classeId)
            ->when($anneeId, fn($q) => $q->where('annee_id', $anneeId))
            ->get();


        // ===========================
        // 📌 CALCUL DE L’ÉTAT DE PAIEMENT
        // ===========================
        $data = $inscriptions->map(function ($inscription) use ($classeId, $anneeId) {

            // ---- Frais totaux de la classe
            $montantsFrais = MontantFrais::with('frais')
                ->where('classe_id', $classeId)
                ->when($anneeId, fn($q) => $q->where('annee_id', $anneeId))
                ->get();

            $montantTotal = $montantsFrais
                ->reject(fn($montant) => $inscription->isReinscrit() && $montant->frais?->libelle === "Frais d'Inscription")
                ->sum('montant');


            // ---- Payé par l'élève
            $montantPaye = Paiement::where('inscription_id', $inscription->id)
                ->sum('montant_paye');


            // ---- Bourse
            $attribution = AttributionBourse::with('bourse.frais')
                ->where('inscription_id', $inscription->id)
                ->where('etat', 'active')
                ->latest('date_attribution')
                ->first();

            $bourse = $attribution?->bourse;


            // ---- Réduction sur frais concernés
            $reduction = 0;

            if ($bourse) {
                foreach ($bourse->frais as $fraisBourse) {
                    if ($inscription->isReinscrit() && $fraisBourse->libelle === "Frais d'Inscription") {
                        continue;
                    }
                    $montantFrais = $montantsFrais->firstWhere('frais_id', $fraisBourse->id)?->montant ?? 0;
                    $pourcentage  = $fraisBourse->pivot->pourcentage ?? 0;

                    $reduction += $montantFrais * $pourcentage / 100;
                }
            }


            // ---- Reste à payer
            $reste = ($montantTotal - $reduction) - $montantPaye;


            return [
                'eleve'               => $inscription->eleve,
                'type_inscription' => $inscription->type_inscription,
                'montant_total'      => $montantTotal,
                'montant_paye'       => $montantPaye,
                'reste'              => $reste,
                'bourse'             => $bourse,
                'bourse_pourcentages'=> $bourse
                    ? $bourse->frais->pluck('pivot.pourcentage')->toArray()
                    : [],
            ];
        });




        // ===========================
        // ✅ APPLICATION DES FILTRES
        // ===========================

        // Filtres statut
        if ($statut === 'impayes') {
            $data = $data->filter(fn($item) => $item['reste'] > 0);
        }

        if ($statut === 'soldes') {
            $data = $data->filter(fn($item) => $item['reste'] <= 0);
        }

        // Filtres montant payé < X
        if ($seuil !== null && is_numeric($seuil)) {
            $data = $data->filter(
                fn($item) => $item['montant_paye'] < (float) $seuil
            );
        }



        // ===========================
        // 📌 Log + Vue
        // ===========================
        logAction('Consultation', "État des paiements de la classe {$classe->nom}");

        return view(
            'paiements.etat_paiement_scolarite_par_classe',
            compact('classe','data','annee','statut','seuil')
        );
    }

}
