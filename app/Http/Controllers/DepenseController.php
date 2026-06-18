<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Depense::query();

        if ($request->filled('date_start')) {
            $query->where('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->where('created_at', '<=', $request->date_end);
        }

        $depenses = $query->orderBy('created_at', 'desc')->get();

        return view('depenses.index', compact('depenses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
        ]);

        Depense::create($request->all());

        return redirect()->route('depenses.index')->with('success', 'Dépense ajoutée avec succès');
    }

    public function update(Request $request, Depense $id)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
        ]);
        //dd($id->getAttributes());

        $des = $id->update($data);

      //  dd($des);
        return redirect()
            ->route('depenses.index')
            ->with('success', 'Dépense modifiée avec succès');
    }


    public function destroy(Depense $id)
    {
        $id->delete();

        return redirect()->route('depenses.index')->with('success', 'Dépense supprimée avec succès');
    }

    public function print(Request $request)
    {
        // Validation des dates
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        $dateDebut = \Carbon\Carbon::parse($request->date_start);
        $dateFin   = \Carbon\Carbon::parse($request->date_end);

        // Récupérer les dépenses filtrées
        $depenses = Depense::whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->orderBy('created_at', 'asc')
            ->get();

        // Total général
        $grandTotal = $depenses->sum('montant');

        // Tu peux ajouter ici des infos école
        $ecole = \App\Models\Ecole::first();

        return view('depenses.print', compact('depenses', 'dateDebut', 'dateFin', 'grandTotal', 'ecole'));
    }

}
