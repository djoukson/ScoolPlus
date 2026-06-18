<?php

namespace App\Http\Controllers;

use App\Models\MontantFrais;
use App\Models\Frais;
use App\Models\Classe;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;

class MontantFraisController extends Controller
{
    public function index()
    {
        // 📌 Récupérer l'année active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $montants = MontantFrais::with(['frais', 'classe', 'annee'])->where('annee_id',$anneeActive->id)->latest()->get();
        $frais = Frais::all();
        $classes = Classe::with('annee')->where('annee_id', $anneeActive->id)->get();
        $annees = AnneesScolaire::all();

        logAction('Consultation', "Affichage de la liste des montants de frais pour l’année scolaire {$anneeActive->nom}.");

        return view('montants.index', compact('montants', 'frais', 'classes', 'annees','anneeActive'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'annee_id' => 'required|exists:annees_scolaires,id',
            'classe_id' => 'required|exists:classes,id',
            'frais_id'  => 'required|exists:frais,id',
            'montant'   => 'required|numeric|min:0',
        ]);

        // ⚡ Vérifier si l'enregistrement existe déjà
        $exists = MontantFrais::where('annee_id', $validated['annee_id'])
            ->where('classe_id', $validated['classe_id'])
            ->where('frais_id', $validated['frais_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Ce montant existe déjà pour cette classe, cette année et ce frais.');
        }

        $montant = MontantFrais::create($validated);

        // 🔍 Détails pour le log
        $annee = AnneesScolaire::find($request->annee_id);
        $classe = Classe::find($request->classe_id);
        $frais = Frais::find($request->frais_id);

        logAction(
            'Création',
            "Ajout du montant de frais : {$frais->libelle} ({$montant->montant} FCFA) pour la classe {$classe->nom}, année scolaire {$annee->nom}."
        );

        return redirect()->back()->with('success', 'Montant ajouté avec succès ✅');
    }


    public function edit(MontantFrais $montant)
    {
        logAction(
            'Consultation',
            "Ouverture du formulaire d’édition pour le montant ID {$montant->id} : Frais = {$montant->frais->libelle}, Classe = {$montant->classe->nom}, Montant actuel = {$montant->montant} FCFA."
        );

        return response()->json($montant->load(['frais', 'classe', 'annee']));
    }

    public function update(Request $request, MontantFrais $montant)
    {
        $validated = $request->validate([
            'annee_id' => 'required|exists:annees_scolaires,id',
            'classe_id' => 'required|exists:classes,id',
            'frais_id'  => 'required|exists:frais,id',
            'montant'   => 'required|numeric|min:0',
        ]);

        $montant->update($validated);

        // 🔍 Détails pour le log
        $annee = AnneesScolaire::find($request->annee_id);
        $classe = Classe::find($request->classe_id);
        $frais = Frais::find($request->frais_id);

        logAction(
            'Modification',
            "Modification du montant ID {$montant->id} : Frais = {$frais->libelle}, Classe = {$classe->nom}, Montant mis à jour = {$montant->montant} FCFA, Année = {$annee->nom}."
        );

        return redirect()->back()->with('success', 'Montant modifié avec succès ✍️');
    }

    public function destroy(MontantFrais $montant)
    {
        // 🔍 Détails avant suppression
        $details = "Suppression du montant ID {$montant->id} : Frais = {$montant->frais->libelle}, Classe = {$montant->classe->nom}, Montant = {$montant->montant} FCFA, Année = {$montant->annee->nom}.";

        $montant->delete();

        logAction('Suppression', $details);

        return redirect()->back()->with('success', 'Montant supprimé 🗑️');
    }
}
