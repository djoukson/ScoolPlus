<?php

namespace App\Http\Controllers;

use App\Models\AttributionBourse;
use App\Models\Bourse;
use App\Models\Inscription;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;

class AttributionController extends Controller
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

        // 🔹 1. Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // 🔹 2. Récupérer les bourses disponibles
        $bourses = Bourse::orderBy('nom')->get();

        // 🔹 3. Récupérer les inscriptions pour l'année active
        $inscriptions = Inscription::with(['eleve', 'classe', 'annee'])
            ->where('annee_id', $anneeActive->id)
            ->get();

        // 🔹 4. Récupérer les attributions de bourses pour l'année active
        $attributions = AttributionBourse::with(['bourse', 'inscription.eleve', 'inscription.classe'])
            ->whereHas('inscription', function ($q) use ($anneeActive) {
                $q->where('annee_id', $anneeActive->id);
            })
            ->latest()
            ->get();

        // 🔹 5. Retourner la vue
        return view('bourses.attributions', compact('bourses', 'inscriptions', 'attributions', 'anneeActive'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'inscription_id' => 'required|exists:inscriptions,id',
            'bourse_id' => 'required|exists:bourses,id',
            'date_attribution' => 'nullable|date',
        ]);

        AttributionBourse::create([
            'inscription_id' => $request->inscription_id,
            'bourse_id' => $request->bourse_id,
            'date_attribution' => $request->date_attribution ?? now(),
        ]);

        return redirect()->route('attributions.index')->with('success', 'Bourse attribuée avec succès.');
    }

    public function destroy($id)
    {
        AttributionBourse::findOrFail($id)->delete();
        return redirect()->route('attributions.index')->with('success', 'Attribution supprimée avec succès.');
    }
    public function toggleEtat($id)
    {
        $attribution = AttributionBourse::findOrFail($id);
        $attribution->etat = $attribution->etat === 'active' ? 'desactive' : 'active';
        $attribution->save();

        return redirect()->back()->with('success', "La bourse a été {$attribution->etat} avec succès !");
    }

}
