<?php

namespace App\Http\Controllers;

use App\Models\Bourse;
use App\Models\Frais;
use Illuminate\Http\Request;

class BourseController extends Controller
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

        $bourses = Bourse::with('frais')->latest()->get();
        $frais = Frais::orderBy('libelle')->get();

        return view('bourses.index', compact('bourses', 'frais'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $bourse = Bourse::create($validated);

        // Vérifier si des frais ont été cochés
        if ($request->has('frais')) {
            foreach ($request->frais as $fraisId => $data) {
                // On vérifie si la case a bien été cochée
                if (isset($data['checked'])) {
                    $pourcentage = isset($data['pourcentage']) ? (float)$data['pourcentage'] : 0;

                    // On attache le frais avec le pourcentage
                    $bourse->frais()->attach($fraisId, [
                        'pourcentage' => $pourcentage
                    ]);
                }
            }
        }

        return redirect()->route('bourses.index')->with('success', 'Bourse créée avec succès.');
    }



    public function update(Request $request, $id)
    {
        $bourse = Bourse::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frais' => 'required|array',
            'pourcentage' => 'required|array',
        ]);

        $bourse->update($request->only('nom', 'description'));

        $data = [];
        foreach ($request->frais as $index => $fraisId) {
            $data[$fraisId] = ['pourcentage' => $request->pourcentage[$index] ?? 0];
        }
        $bourse->frais()->sync($data);

        return redirect()->route('bourses.index')->with('success', 'Bourse mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $bourse = Bourse::findOrFail($id);
        $bourse->delete();

        return redirect()->route('bourses.index')->with('success', 'Bourse supprimée avec succès.');
    }
}
