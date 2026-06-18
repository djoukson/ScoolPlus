<?php

namespace App\Http\Controllers;

use App\Models\MontantScolarite;
use Illuminate\Http\Request;

class ScolariteController extends Controller
{
    /**
     * Affiche la liste des montants de scolarité
     */
    public function typefrais()
    {
        $montants = MontantScolarite::orderBy('created_at', 'desc')->get();

        // 🔒 Log de la consultation
        logAction(
            'Consultation',
            "Affichage de la liste complète des montants de scolarité (" . $montants->count() . " enregistrements affichés)."
        );

        return view('scolarite.typefrais', compact('montants'));
    }

    /**
     * Enregistre un nouveau montant
     */
    public function typefraisstore(Request $request)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $nouveau = MontantScolarite::create($validated);

        // 🔒 Log de la création
        logAction(
            'Ajout',
            "Nouveau montant de scolarité ajouté : {$nouveau->montant} FCFA" .
            (!empty($nouveau->description) ? " | Description : {$nouveau->description}" : "") .
            "."
        );

        return redirect()->route('scolarite.typefrais')->with('success', '💰 Montant ajouté avec succès.');
    }

    /**
     * Récupère un montant pour édition (AJAX)
     */
    public function typefraisupdate(MontantScolarite $montant)
    {
        // 🔒 Log de la récupération pour modification
        logAction(
            'Consultation',
            "Préparation à la modification du montant ID #{$montant->id} | Montant actuel : {$montant->montant} FCFA" .
            (!empty($montant->description) ? " | Description : {$montant->description}" : "") .
            "."
        );

        return response()->json($montant);
    }

    /**
     * Met à jour un montant existant
     */
    public function update(Request $request, MontantScolarite $montant)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $ancienMontant = $montant->montant;
        $ancienneDescription = $montant->description;

        $montant->update($validated);

        // 🔒 Log de la modification
        logAction(
            'Modification',
            "Montant de scolarité modifié (ID #{$montant->id}) : ancien montant = {$ancienMontant} FCFA, " .
            "nouveau montant = {$montant->montant} FCFA" .
            (($ancienneDescription != $montant->description) ?
                " | Ancienne description : {$ancienneDescription} → Nouvelle : {$montant->description}" : "") .
            "."
        );

        return redirect()->route('scolarite.typefrais')->with('success', '✏️ Montant mis à jour avec succès.');
    }

    /**
     * Supprime un montant
     */
    public function typefraisdestroy(MontantScolarite $montant)
    {
        $details = "Montant supprimé (ID #{$montant->id}) : {$montant->montant} FCFA" .
            (!empty($montant->description) ? " | Description : {$montant->description}" : "") . ".";

        $montant->delete();

        // 🔒 Log de la suppression
        logAction('Suppression', $details);

        return redirect()->route('scolarite.typefrais')->with('success', '🗑️ Montant supprimé avec succès.');
    }
}
