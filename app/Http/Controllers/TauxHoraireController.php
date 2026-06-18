<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TauxHoraire;
use App\Models\Niveau;

class TauxHoraireController extends Controller
{
    public function index()
    {
        $tauxHoraires = TauxHoraire::with('niveau')
            ->whereHas('niveau', function ($query) {
                $query->where('nom', '!=', 'Primaire');
            })
            ->get();

        return view('taux_horaires.index', compact('tauxHoraires'));
    }

    public function create()
    {
        $niveaux = Niveau::all();
        return view('taux_horaires.create', compact('niveaux'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id|unique:taux_horaires,niveau_id',
            'taux'      => 'required|numeric|min:0',
        ]);

        TauxHoraire::create($request->all());

        return redirect()->route('taux_horaires.index')->with('success', 'Taux horaire ajouté avec succès.');
    }

    public function edit(TauxHoraire $tauxHoraire)
    {
        $niveaux = Niveau::all();
        return view('taux_horaires.edit', compact('tauxHoraire', 'niveaux'));
    }

    public function update(Request $request, TauxHoraire $tauxHoraire)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id|unique:taux_horaires,niveau_id,' . $tauxHoraire->id,
            'taux'      => 'required|numeric|min:0',
        ]);

        $tauxHoraire->update($request->all());

        return redirect()->route('taux_horaires.index')->with('success', 'Taux horaire mis à jour avec succès.');
    }

    public function destroy(TauxHoraire $tauxHoraire)
    {
        $tauxHoraire->delete();
        return redirect()->route('taux_horaires.index')->with('success', 'Taux horaire supprimé avec succès.');
    }
}
