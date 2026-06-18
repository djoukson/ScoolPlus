<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnneesScolaire;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AnneeController extends Controller
{
    public function index()
    {
        $annees = AnneesScolaire::orderByDesc('date_debut')->get();
        $anneeActive = AnneesScolaire::active()->first();

       // dd($anneeActive->id);
        return view('annees_scolaires.index', compact('annees', 'anneeActive'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);

        $anneeDebut = $dateDebut->year;
        $anneeFin   = $dateFin->year;

        // ❌ Même année (2025-2025)
        if ($anneeDebut === $anneeFin) {
            return back()->withErrors([
                'date_fin' => 'Une année scolaire ne peut pas commencer et finir la même année.'
            ])->withInput();
        }

        // ❌ Durée anormale
        $dureeMois = $dateDebut->diffInMonths($dateFin);
        if ($dureeMois < 8 || $dureeMois > 13) {
            return back()->withErrors([
                'date_fin' => 'La durée d’une année scolaire doit être comprise entre 8 et 13 mois.'
            ])->withInput();
        }

        // ✅ Nom automatique et cohérent
        $nom = $anneeDebut . '-' . $anneeFin;

        // ❌ Nom déjà existant (VALIDATION UNIQUE)
        if (AnneesScolaire::where('nom', $nom)->exists()) {
            return back()->withErrors([
                'nom' => "L’année scolaire $nom existe déjà."
            ])->withInput();
        }

        // ❌ Chevauchement
        $chevauchement = AnneesScolaire::where(function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_debut', [$dateDebut, $dateFin])
                ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                    $q2->where('date_debut', '<=', $dateDebut)
                        ->where('date_fin', '>=', $dateFin);
                });
        })->exists();

        if ($chevauchement) {
            return back()->withErrors([
                'date_debut' => 'Cette période chevauche une année scolaire existante.'
            ])->withInput();
        }

        // ✅ Création
        AnneesScolaire::create([
            'nom' => $nom,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'active' => false
        ]);

        return back()->with('success', "Année scolaire $nom ajoutée avec succès.");
    }



    public function destroy(AnneesScolaire $annee)
    {
        // 🔐 Sécurité accès
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return back()->with('error', 'Accès refusé.');
        }

        // ❌ Année active
        if ($annee->active) {
            return back()->with('error', 'Impossible de supprimer une année scolaire active.');
        }

        // ❌ Inscriptions existantes
        if ($annee->inscriptions()->exists()) {
            return back()->with('error', 'Suppression impossible : des inscriptions existent pour cette année.');
        }

        // ❌ Classes existantes
        if ($annee->classes()->exists()) {
            return back()->with('error', 'Suppression impossible : des classes sont liées à cette année.');
        }

        // ❌ Affectations existantes
        if ($annee->affectations()->exists()) {
            return back()->with('error', 'Suppression impossible : des affectations existent pour cette année.');
        }

        // ✅ Suppression autorisée
        $annee->delete();

        logAction(
            'Suppression année scolaire',
            "Année supprimée : {$annee->nom} (ID {$annee->id})"
        );

        return back()->with('success', 'Année scolaire supprimée avec succès.');
    }



    public function change($id)
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        if (!in_array(auth()->user()->role, ['admin', 'directeur'])) {
            return redirect()->back()
                ->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $annees = AnneesScolaire::findOrFail($id);

        // ❌ Si c’est déjà l’année active
        if ($annees->active) {
            return back()->with('info', 'Cette année est déjà active.');
        }

        // 🔒 Vérifier qu’il existe AU MOINS une autre année
        if (!AnneesScolaire::where('id', '!=', $annees->id)->exists()) {
            return back()->with('error', 'Impossible : au moins une année scolaire doit être active.');
        }

        // 🔹 Désactiver toutes les années
        AnneesScolaire::where('active', 1)->update(['active' => 0]);

        // 🔹 Activer l'année choisie
        $annee = AnneesScolaire::findOrFail($id);
        $annee->update(['active' => 1]);

        // 🔹 Stocker en session
        session(['annee_id' => $annee->id]);

        logAction(
            'Changement d’année scolaire',
            "L’utilisateur a changé l’année active pour : {$annee->nom} (ID : {$annee->id})."
        );

        return redirect()->back()
            ->with('success', 'Année scolaire active : ' . $annee->nom);
    }

}
