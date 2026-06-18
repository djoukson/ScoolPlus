<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\AnneesScolaire;
use App\Models\Titulaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffectationController extends Controller
{
    public function index()
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $affectations = Affectation::with(['enseignant', 'class', 'matiere', 'annees_scolaire'])->get();
        $enseignants = Enseignant::all();
        $classes     = Classe::all();
        $matieres    = Matiere::all();
        $annees      = AnneesScolaire::all();

        // 🔹 Log de consultation
        logAction('Consultation', 'Affichage de la liste des affectations.');

        return view('affectations.index', compact('affectations', 'enseignants', 'classes', 'matieres', 'annees'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'enseignant_id'     => 'required|exists:enseignants,id',
            'classe_id'         => 'required|exists:classes,id',
            'matiere_id'        => 'required|exists:matieres,id',
            'annee_id'          => 'required|exists:annees_scolaires,id',
            'heures_attribuees' => 'nullable|integer|min:0',
        ]);

        $affectation = Affectation::create($validated);

        // 🔹 Log d’ajout
        logAction('Création', "Affectation créée pour l'enseignant ID {$affectation->enseignant_id}, classe ID {$affectation->classe_id}, matière ID {$affectation->matiere_id}.");

        return redirect()->route('affectations.index')->with('success', 'Affectation ajoutée avec succès.');
    }

    public function update(Request $request, Affectation $affectation)
    {
        $validated = $request->validate([
            'enseignant_id'     => 'required|exists:enseignants,id',
            'classe_id'         => 'required|exists:classes,id',
            'matiere_id'        => 'required|exists:matieres,id',
            'annee_id'          => 'required|exists:annees_scolaires,id',
            'heures_attribuees' => 'nullable|integer|min:0',
        ]);

        $affectation->update($validated);

        // 🔹 Log de modification
        logAction('Modification', "Affectation ID {$affectation->id} mise à jour (Enseignant: {$affectation->enseignant_id}, Classe: {$affectation->classe_id}, Matière: {$affectation->matiere_id}).");

        return redirect()->route('affectations.index')->with('success', 'Affectation mise à jour avec succès.');
    }

    public function destroy(Affectation $affectation)
    {
        // ✅ Vérification d'authentification
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        $id = $affectation->id;
        $affectation->delete();

        // 🔹 Log de suppression
        logAction('Suppression', "Affectation ID {$id} supprimée.");

        return redirect()->route('affectations.index')->with('success', 'Affectation supprimée avec succès.');
    }

    // 🔸 Supprimer un enseignant d’une classe primaire
    public function removePrimary($classeId)
    {
        $classe = \App\Models\Classe::findOrFail($classeId);
        $classe->update(['enseignant_id' => null]);

        // 🔹 Log de désaffectation
        logAction('Désaffectation', "Enseignant retiré de la classe ID {$classeId} (niveau primaire).");

        return back()->with('success', 'Enseignant retiré de la classe.');
    }

    // 🔸 Supprimer une affectation normale (secondaire/collège/lycée)
    public function destroyaffectation($id)
    {
        $affectation = Affectation::findOrFail($id);
        $affectation->delete();

        // 🔹 Log de suppression spécifique
        logAction('Suppression', "Affectation normale ID {$id} supprimée (secondaire/collège/lycée).");

        return back()->with('success', 'Affectation supprimée avec succès.');
    }

    public function desaffectations($id)
    {
        $affectation = Affectation::findOrFail($id);
        $affectation->delete();

        // 🔹 Log de désaffectation
        logAction('Désaffectation', "Matière retirée de la classe (Affectation ID {$id}).");

        return back()->with('success', 'La matière a été retirée de la classe avec succès.');
    }

    public function storeTitulaire(Request $request)
    {
       // dd($request->all());

        $validated = $request->validate([
            'classe_id'     => 'required|exists:classes,id',
            'enseignant_id' => 'required|exists:enseignants,id',
            'annee_id'      => 'required|exists:annees_scolaires,id',
        ]);

        // Vérifier qu'aucun titulaire n'existe pour cette classe cette année
        if (Titulaire::where('classe_id', $validated['classe_id'])
            ->where('annee_id', $validated['annee_id'])
            ->exists()) {
            return back()->with('error', 'Cette classe a déjà un titulaire pour cette année.');
        }

        // Vérifier que l'enseignant n'est pas titulaire ailleurs cette année
        if (Titulaire::where('enseignant_id', $validated['enseignant_id'])
            ->where('annee_id', $validated['annee_id'])
            ->exists()) {
            return back()->with('error', 'Cet enseignant est déjà titulaire d’une autre classe cette année.');
        }

        Titulaire::create($validated);

        logAction('Création', "Enseignant ID {$validated['enseignant_id']} nommé titulaire de la classe ID {$validated['classe_id']}.");

        return back()->with('success', 'Titulaire enregistré avec succès.');
    }
    public function destroytitulaire($id)
    {
        // 🔹 Récupérer le titulaire par ID
        $titulaire = \App\Models\Titulaire::findOrFail($id);

        // 🔹 Stocker les infos pour le log
        $enseignantNom = $titulaire->enseignant->nom ?? 'N/A';
        $classeNom = $titulaire->classe->nom ?? 'N/A';
        $anneeNom = $titulaire->annee->nom ?? 'N/A';

        // 🔹 Supprimer le titulaire
        $titulaire->delete();

        // 🔹 Log
        logAction('Suppression', "Titulaire supprimé : Enseignant {$enseignantNom}, Classe {$classeNom}, Année {$anneeNom}.");

        // 🔹 Retour
        return back()->with('success', 'Le titulaire a été retiré avec succès.');
    }


}
