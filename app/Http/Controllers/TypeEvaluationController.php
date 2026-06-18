<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;
//use Psy\Util\Str;
use Illuminate\Support\Str;
class TypeEvaluationController extends Controller
{
    /**
     * Affiche la liste des types d'évaluations
     */
    public function index()
    {
        $typesEvaluations = TypeEvaluation::all();

        // Log de consultation
        logAction('Consultation de la liste des types d’évaluations');

        return view('types_evaluations.index', compact('typesEvaluations'));
    }

    /**
     * Enregistre un nouveau type d’évaluation
     */


    public function store(Request $request)
    {
        // 🔹 Validation de base
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        // 🔹 Normalisation du nom
        $nomOriginal = $request->input('nom');

        // Supprimer accents
        $nomNormalized = Str::ascii($nomOriginal);

        // Supprimer caractères spéciaux et espaces
        $nomNormalized = preg_replace('/[^A-Za-z0-9]/', '', $nomNormalized);

        // Lowercase pour l’unicité
        $nomNormalized = strtolower($nomNormalized);

        // 🔴 Vérification d’unicité (sécurité supplémentaire)
        if (TypeEvaluation::where('nom_normalized', $nomNormalized)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ Ce type d’évaluation existe déjà.');
        }

        // 🔹 Nom affiché (propre)
        $nomAffiche = ucfirst($nomNormalized);

        // ✅ Création
        $type = TypeEvaluation::create([
            'nom' => $nomAffiche,
            'nom_normalized' => $nomNormalized,
        ]);

        // 📝 Log
        logAction('Ajout d’un type d’évaluation : ' . $type->nom);

        return redirect()->back()->with('success', '✅ Type d’évaluation ajouté avec succès.');
    }


    /**
     * Met à jour un type d’évaluation existant
     */

    public function update(Request $request, TypeEvaluation $typeEvaluation)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $ancienNom = $typeEvaluation->nom;

        // 🔹 Normalisation du nom
        $nomOriginal = $request->input('nom');

        // Supprimer accents
        $nomNormalized = Str::ascii($nomOriginal);

        // Supprimer caractères spéciaux et espaces
        $nomNormalized = preg_replace('/[^A-Za-z0-9]/', '', $nomNormalized);

        // Lowercase pour unicité
        $nomNormalized = strtolower($nomNormalized);

        // 🔴 Vérifier doublon (en excluant l’élément en cours)
        $exists = TypeEvaluation::where('nom_normalized', $nomNormalized)
            ->where('id', '!=', $typeEvaluation->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ Un type d’évaluation avec ce nom existe déjà.');
        }

        // 🔹 Nom affiché
        $nomAffiche = ucfirst($nomNormalized);

        // ✅ Mise à jour
        $typeEvaluation->update([
            'nom' => $nomAffiche,
            'nom_normalized' => $nomNormalized,
        ]);

        // 📝 Log
        logAction("Modification du type d’évaluation : {$ancienNom} → {$typeEvaluation->nom}");

        return redirect()->back()->with('success', '✏️ Type d’évaluation mis à jour avec succès.');
    }


    /**
     * Supprime un type d’évaluation
     */
    public function destroy(TypeEvaluation $typeEvaluation)
    {
        $nom = $typeEvaluation->nom;

        // 🔴 Vérifier s'il existe des évaluations liées
        $hasEvaluations = Evaluation::where('type_evaluation_id', $typeEvaluation->id)->exists();

        if ($hasEvaluations) {
            return redirect()->back()->with(
                'error',
                '⛔ Impossible de supprimer ce type d’évaluation : des matières ou évaluations y sont déjà associées.'
            );
        }
        // ✅ Suppression autorisée
        $typeEvaluation->delete();

        // 📝 Log
        logAction("Suppression du type d’évaluation : $nom");

        return redirect()->back()->with('success', '🗑️ Type d’évaluation supprimé avec succès.');
    }
}
