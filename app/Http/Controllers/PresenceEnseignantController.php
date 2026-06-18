<?php
namespace App\Http\Controllers;

use App\Models\PresenceEnseignant;
use App\Models\Enseignant;
use App\Models\Affectation;
use Illuminate\Http\Request;

class PresenceEnseignantController extends Controller
{
    public function index()
    {
        $enseignants = Enseignant::orderBy('nom')->get();

        $presences = PresenceEnseignant::with('enseignant')
            ->orderBy('date', 'desc')
            ->get();

        return view('presences.index', compact('presences','enseignants'));
    }

    public function create()
    {
        $enseignants = Enseignant::orderBy('nom')->get();
        $affectations = Affectation::all();

        return view('presences.create', compact('enseignants', 'affectations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enseignant_id'    => 'required|exists:enseignants,id',
            'date'             => 'required|date',
            'nombre_heures'   => 'required|integer|min:0',
            'motif'             => 'nullable|string|max:255', // optionnel ou obligatoire selon ton choix
        ]);

        PresenceEnseignant::create($request->all());

        return redirect()->route('presences-enseignants.index')
            ->with('success', 'Présence enregistrée avec succès');
    }



    public function update(Request $request, PresenceEnseignant $presence)
    {
        $request->validate([
            'enseignant_id'     => 'required|exists:enseignants,id',
            'date'              => 'required|date',
            'nombre_heures'   => 'required|integer|min:0',
            'motif'             => 'nullable|string|max:255', // optionnel ou obligatoire selon ton choix
        ]);
//dd($request->all());
        // Mettre à jour uniquement les colonnes autorisées
        $presence->update([
            'enseignant_id' => $request->enseignant_id,
            'date'          => $request->date,
            'nombre_heures' => $request->nombre_heures,
            'motif'         => $request->motif,
        ]);


        return redirect()->route('presences-enseignants.index')
            ->with('success', 'Présence modifiée avec succès');
    }

    public function destroy(PresenceEnseignant $presence)
    {
        $presence->delete();

        return redirect()->route('presences-enseignants.index')
            ->with('success', 'Présence supprimée');
    }
}
