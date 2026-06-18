<?php
namespace App\Http\Controllers;

use App\Models\Ecole;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    /**
     * Affiche la page des paramètres de l'école
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
        $ecole = Ecole::with('annee')->first(); // Charger la relation année

        // Si l'école a déjà une année liée, on l'utilise, sinon on prend l'année active
        $anneeActive = $ecole->annee ?? AnneesScolaire::where('active', 1)->first();

        logAction('Consultation', 'Consultation de la page des paramètres de l’école');

        return view('ecoles.settings', [
            'ecole' => $ecole,
            'annee' => $anneeActive
        ]);
    }

    /**
     * Met à jour les informations de l'école
     */
    public function update(Request $request)
    {
        // Récupérer la seule école existante
        $ecole = Ecole::first();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'directeur' => 'nullable|string',
            'directeur_primaire' => 'nullable|string',
            'annee_id' => 'nullable|exists:annees_scolaires,id',
            'site_web' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // 🔹 Sauvegarde du logo si nouveau fichier
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
            logAction('Mise à jour', "Logo de l’école modifié par l’utilisateur.");
        }

        if ($ecole) {
            // ✅ Mise à jour si l’école existe
            $ecole->update($validated);
            logAction('Modification', "Informations de l’école {$ecole->nom} mises à jour");
        } else {
            // ✅ Création si aucune école n’existe
            $ecole = Ecole::create($validated);
            logAction('Création', "Nouvelle école créée : {$ecole->nom}");
        }

        // 🔹 Gestion de l'année active si nécessaire
        if (!empty($validated['annee_id'])) {
            $ancienneAnnee = AnneesScolaire::where('active', 1)->first();
            $nouvelleAnnee = AnneesScolaire::find($validated['annee_id']);

            if ($ancienneAnnee?->id !== $nouvelleAnnee?->id) {
                AnneesScolaire::where('id', '!=', $validated['annee_id'])->update(['active' => 0]);
                AnneesScolaire::where('id', $validated['annee_id'])->update(['active' => 1]);
                logAction('Modification', "Changement d’année active : de {$ancienneAnnee?->nom} à {$nouvelleAnnee?->nom}");
            }
        }

        return back()->with('success', 'Configuration de l’école sauvegardée ✅');
    }

}
