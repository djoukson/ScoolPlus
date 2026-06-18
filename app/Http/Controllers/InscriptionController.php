<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    /**
     * Affiche la liste de toutes les inscriptions
     */
    public function index()
    {
        $inscriptions = Inscription::with(['eleve', 'classe', 'annee'])->get();
        $eleves = Eleve::all();
        $classes = Classe::all();
        $annees = AnneesScolaire::all();

        // 🔹 Log détaillé
        logAction(
            'Consultation',
            "L’utilisateur a consulté la liste complète des inscriptions. Total : {$inscriptions->count()} enregistrements chargés."
        );

        return view('inscriptions.index', compact('inscriptions', 'eleves', 'classes', 'annees'));
    }

    /**
     * Enregistre de nouveaux élèves dans une classe pour l'année scolaire active
     */
    public function store(Request $request, Classe $classe)
    {
        $request->validate([
            'eleves' => 'required|array',
        ]);

        $anneeEnCours = AnneesScolaire::where('active', 1)->first();

        if (!$anneeEnCours) {
            return back()->with('error', 'Aucune année scolaire active.');
        }

        $nbInscriptions = 0;
        $erreurs = [];

        foreach ($request->eleves as $eleveId) {
            $existe = Inscription::where('eleve_id', $eleveId)
                ->where('annee_id', $anneeEnCours->id)
                ->exists();

            if ($existe) {
                $eleve = Eleve::find($eleveId);
                $erreurs[] = "L'élève {$eleve->nom} {$eleve->prenom} est déjà inscrit pour l'année en cours.";
                continue;
            }

            Inscription::create([
                'eleve_id' => $eleveId,
                'classe_id' => $classe->id,
                'annee_id' => $anneeEnCours->id,
                'date_inscription' => now(),
            ]);

            $nbInscriptions++;
        }

        // 🔹 Log d’inscription
        $erreursTxt = !empty($erreurs) ? implode(' | ', $erreurs) : 'Aucun élève ignoré.';
        logAction(
            'Ajout',
            "Inscription de {$nbInscriptions} élève(s) dans la classe [{$classe->nom}] pour l’année [{$anneeEnCours->nom}]. Détails : {$erreursTxt}"
        );

        if (!empty($erreurs)) {
            return back()->with('warning', implode('<br>', $erreurs));
        }

        return redirect()->route('classes.show', $classe->id)
            ->with('success', 'Élèves inscrits avec succès.');
    }

    /**
     * Met à jour une inscription existante
     */
    public function update(Request $request, Inscription $inscription)
    {
        $validated = $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'classe_id' => 'required|exists:classes,id',
            'annee_id' => 'required|exists:annees_scolaires,id',
            'date_inscription' => 'required|date',
            'statut' => 'required|in:actif,transfere,abandon,termine',
        ]);

        $inscription->update($validated);

        // 🔹 Log de mise à jour
        logAction(
            'Modification',
            "Mise à jour de l’inscription [ID : {$inscription->id}] — Élève : {$inscription->eleve->nom} {$inscription->eleve->prenom} | Classe : {$inscription->classe->nom} | Année : {$inscription->annee->nom} | Nouveau statut : {$validated['statut']}."
        );

        return redirect()->route('inscriptions.index')->with('success', 'Inscription mise à jour avec succès.');
    }

    /**
     * Supprime une inscription
     */
    public function destroy(Inscription $inscription)
    {
        $eleveNom = $inscription->eleve->nom ?? 'Inconnu';
        $classeNom = $inscription->classe->nom ?? 'Inconnue';
        $anneeNom = $inscription->annee->nom ?? 'N/A';

        $inscription->delete();

        // 🔹 Log de suppression
        logAction(
            'Suppression',
            "Suppression de l’inscription [ID : {$inscription->id}] — Élève : {$eleveNom} | Classe : {$classeNom} | Année : {$anneeNom}."
        );

        return redirect()->route('inscriptions.index')->with('success', 'Inscription supprimée avec succès.');
    }

    public function toggleStatusinscription($eleveId)
    {
        $anneeEnCours = AnneesScolaire::where('active', 1)->first();
        $anneeId = $anneeEnCours->id;
        $inscription = Inscription::where('eleve_id', $eleveId)
            ->where('annee_id', $anneeId)
            ->firstOrFail();

        $inscription->status_eleve = $inscription->status_eleve === 'Nouveau'
            ? 'Redoublant'
            : 'Nouveau';

        $inscription->save();

        return back()->with('success', 'Statut de l’inscription mis à jour avec succès.');
    }


}
