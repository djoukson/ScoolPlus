<?php

namespace App\Http\Controllers;

use App\Models\AnneesScolaire;
use App\Models\Decoupage;
use Illuminate\Http\Request;

class DecoupageController extends Controller
{
    /**
     * Affiche la liste des découpages.
     */
    public function index()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // 🔹 Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // 🔹 Récupérer uniquement les découpages liés à l'année active
        $decoupages = Decoupage::where('annee_id', $anneeActive->id)
            ->orderBy('created_at', 'desc')
            ->get();
//dd($decoupages);
        // 🔹 Enregistrer l'action dans les logs
        logAction('Consultation', "Consultation de la liste des découpages pour l'année {$anneeActive->nom}");

        // 🔹 Retourner la vue avec l'année active
        return view('decoupages.index', compact('decoupages', 'anneeActive'));
    }


    /**
     * Formulaire de création.
     */
    public function create()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        logAction('Consultation', 'Ouverture du formulaire de création d’un découpage');
        return view('decoupages.create');
    }

    /**
     * Sauvegarde un découpage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'  => 'required|string|max:50',
            'type' => 'required|in:trimestre,semestre',
        ]);

        // 📌 Récupérer l'année active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        // Vérifier si un découpage existe déjà avec ce nom dans la même année
        $exists = Decoupage::where('annee_id', $anneeActive?->id)
            ->where('nom', $request->nom)
            ->exists();

        if ($exists) {
            logAction('Erreur', "Tentative de création d’un découpage déjà existant ({$request->nom}) pour l’année {$anneeActive?->nom}");
            return redirect()->back()
                ->withInput()
                ->with('error', "⚠️ Ce découpage existe déjà pour l'année scolaire sélectionnée.");
        }

        // Création
        $decoupage = Decoupage::create([
            'nom'      => $request->nom,
            'type'     => $request->type,
            'annee_id' => $anneeActive?->id,
        ]);

        logAction('Création', "Découpage {$decoupage->nom} ({$decoupage->type}) créé pour l’année {$anneeActive?->nom}");

        return redirect()->route('decoupages.index')
            ->with('success', '✅ Découpage créé avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Decoupage $decoupage)
    {
        logAction('Consultation', "Ouverture du formulaire d’édition du découpage {$decoupage->nom}");
        return view('decoupages.edit', compact('decoupage'));
    }

    /**
     * Mise à jour d’un découpage.
     */
    public function update(Request $request, Decoupage $decoupage)
    {
        $request->validate([
            'nom'  => 'required|string|max:50',
            'type' => 'required|in:trimestre,semestre',
        ]);

        $exists = Decoupage::where('annee_id', $decoupage->annee_id)
            ->where('nom', $request->nom)
            ->where('id', '!=', $decoupage->id)
            ->exists();

        if ($exists) {
            logAction('Erreur', "Tentative de mise à jour : découpage {$request->nom} déjà existant pour cette année");
            return redirect()->back()
                ->withInput()
                ->with('error', "⚠️ Ce découpage existe déjà pour l'année scolaire sélectionnée.");
        }

        $ancienNom = $decoupage->nom;
        $ancienType = $decoupage->type;

        $decoupage->update([
            'nom'  => $request->nom,
            'type' => $request->type,
        ]);

        logAction(
            'Modification',
            "Découpage mis à jour : {$ancienNom} ({$ancienType}) → {$decoupage->nom} ({$decoupage->type})"
        );

        return redirect()->route('decoupages.index')
            ->with('success', '✅ Découpage mis à jour avec succès.');
    }

    /**
     * Suppression d’un découpage.
     */
    public function destroy(Decoupage $decoupage)
    {
        $nom = $decoupage->nom;
        $type = $decoupage->type;
        $annee = $decoupage->annee?->nom;

        $decoupage->delete();

        logAction('Suppression', "Découpage supprimé : {$nom} ({$type}) de l’année {$annee}");

        return redirect()->route('decoupages.index')
            ->with('success', '🗑️ Découpage supprimé avec succès.');
    }

    public function copierDepuisAnneePrecedente()
{
    // Vérifier la connexion
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    // Vérifier les autorisations
    if (!in_array(auth()->user()->role, ['admin', 'directeur', 'secretaire'])) {
        return redirect()->back()
            ->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
    }

    // Année actuellement sélectionnée
    $anneeActuelle = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    if (!$anneeActuelle) {
        return back()->with('error', 'Aucune année scolaire active trouvée.');
    }

    // Chercher l'année précédente
    $anneePrecedente = AnneesScolaire::where('id', '<', $anneeActuelle->id)
        ->orderBy('id', 'desc')
        ->first();

    if (!$anneePrecedente) {
        return back()->with(
            'error',
            'Aucune année scolaire précédente trouvée.'
        );
    }

    // Récupérer les découpages de l'année précédente
    $decoupagesPrecedents = Decoupage::where(
        'annee_id',
        $anneePrecedente->id
    )->get();

    if ($decoupagesPrecedents->isEmpty()) {
        return back()->with(
            'warning',
            "L'année {$anneePrecedente->nom} ne contient aucun découpage à copier."
        );
    }

    $nombreAjoutes = 0;
    $nombreExistants = 0;

    foreach ($decoupagesPrecedents as $decoupage) {

        // Vérifier si ce découpage existe déjà dans l'année actuelle
        $existe = Decoupage::where('annee_id', $anneeActuelle->id)
            ->where('type', $decoupage->type)
            ->where('nom', $decoupage->nom)
            ->exists();

        if ($existe) {
            $nombreExistants++;
            continue;
        }

        // Copier le découpage
        Decoupage::create([
            'annee_id' => $anneeActuelle->id,
            'type'     => $decoupage->type,
            'nom'      => $decoupage->nom,
        ]);

        $nombreAjoutes++;
    }

    // Log
    logAction(
        'Copie',
        "Copie des découpages de l'année {$anneePrecedente->nom} vers l'année {$anneeActuelle->nom}"
    );

    if ($nombreAjoutes === 0) {
        return back()->with(
            'info',
            "Tous les découpages de {$anneePrecedente->nom} existent déjà dans {$anneeActuelle->nom}."
        );
    }

    $message = "{$nombreAjoutes} découpage(s) copié(s) depuis {$anneePrecedente->nom}.";

    if ($nombreExistants > 0) {
        $message .= " {$nombreExistants} déjà présent(s) ont été ignoré(s).";
    }

    return back()->with('success', $message);
}
}
