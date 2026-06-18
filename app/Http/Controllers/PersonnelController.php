<?php

namespace App\Http\Controllers;

use App\Models\Ecole;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PersonnelController extends Controller
{
    public function index()
    {
        $personnel = Personnel::orderBy('id', 'desc')->paginate(10);
        return view('personnel.index', compact('personnel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'poste' => 'required|string|max:255',
            'salaire' => 'required|numeric|min:0',
            'tel' => 'nullable|digits:8',
            'email' => 'nullable|email|unique:personnel,email',
            'groupesanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
        ]);

        Personnel::create($request->all());

        return redirect()->back()->with('success', 'Membre ajouté avec succès');
    }

    public function update(Request $request, $id)
    {
        $person = Personnel::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'poste' => 'required|string|max:255',
            'salaire' => 'required|numeric|min:0',
            'tel' => 'nullable|digits:8',
            'email' => 'nullable|email|unique:personnel,email,' . $person->id,
            'groupesanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
        ]);

        $person->update($request->all());

        return redirect()->back()->with('success', 'Membre mis à jour avec succès');
    }
    public function show(Personnel $personnel)
    {
        return view('personnel.show', compact('personnel'));
    }

    public function destroy($id)
    {
        $person = Personnel::findOrFail($id);
        $person->delete();

        return redirect()->back()->with('success', 'Membre supprimé avec succès');
    }

    public function toggle($id)
    {
        $person = Personnel::findOrFail($id);
        $person->statut = !$person->statut;
        $person->save();

        return redirect()->back()->with('success', 'Statut mis à jour avec succès');
    }

    public function generatePdf()
    {
        $ecole = Ecole::first(); // 👈 infos de l'école
        $personnel = Personnel::all();

        $pdf = Pdf::loadView('personnel.pdf', compact('personnel', 'ecole'));

        // 👉 Affiche le PDF dans le navigateur
        return $pdf->stream('liste_personnel.pdf');
    }
}
