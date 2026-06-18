<?php

namespace App\Http\Controllers;

use App\Models\Frais;
use Illuminate\Http\Request;

class FraisController extends Controller
{
    public function index()
    {
        $frais = Frais::latest()->get();

        // 🔹 Log d’accès à la page
        logAction('Consultation', "Affichage de la liste complète des frais. Total : " . $frais->count() . " enregistrements.");

        return view('frais.index', compact('frais'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $frais = Frais::create($validated);

        // 🔹 Log création
        logAction(
            'Ajout',
            "Création d’un nouveau frais : [Libellé : {$frais->libelle}]"
            . (!empty($frais->description) ? " - Description : {$frais->description}" : "")
        );

        return redirect()->route('frais.index')->with('success', '✅ Frais ajouté avec succès.');
    }

    public function edit(Frais $frais)
    {
        // 🔹 Log consultation d’un frais spécifique
        logAction(
            'Consultation',
            "Ouverture du formulaire d’édition pour le frais [ID : {$frais->id}] - Libellé : {$frais->libelle}."
        );

        return response()->json($frais);
    }

    public function update(Request $request, Frais $frais)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // 🔹 Comparaison avant mise à jour
        $champsModifies = [];
        foreach ($validated as $champ => $valeur) {
            if ($frais->$champ !== $valeur) {
                $ancien = $frais->$champ ?? '—';
                $nouveau = $valeur ?? '—';
                $champsModifies[] = "{$champ}: '{$ancien}' → '{$nouveau}'";
            }
        }

        $frais->update($validated);

        // 🔹 Log mise à jour uniquement si modification réelle
        if (!empty($champsModifies)) {
            logAction(
                'Modification',
                "Mise à jour du frais [ID : {$frais->id}] - Libellé : {$frais->libelle}. Détails des changements : " . implode(', ', $champsModifies)
            );
        } else {
            logAction(
                'Aucune modification',
                "Aucune donnée n’a été modifiée pour le frais [ID : {$frais->id}] - Libellé : {$frais->libelle}."
            );
        }

        return redirect()->route('frais.index')->with('success', '✅ Frais mis à jour avec succès.');
    }

    public function destroy(Frais $frais)
    {
        $libelle = $frais->libelle;
        $description = $frais->description;

        $frais->delete();

        // 🔹 Log suppression
        logAction(
            'Suppression',
            "Frais supprimé : [Libellé : {$libelle}]" . (!empty($description) ? " - Description : {$description}" : "")
        );

        return redirect()->route('frais.index')->with('success', '🗑️ Frais supprimé avec succès.');
    }
}
