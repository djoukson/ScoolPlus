<?php
namespace App\Http\Controllers;

use App\Models\ParentEleve;
use Illuminate\Http\Request;

class ParentEleveController extends Controller
{
    public function update(Request $request, $eleveId)
    {
        $data = $request->validate([
            'pere_nom' => 'nullable|string|max:255',
            'pere_tel' => 'nullable|string|max:30',
            'pere_profession' => 'nullable|string|max:255',
            'pere_email' => 'nullable|email|max:255',
            'pere_adresse' => 'nullable|string|max:255',

            'mere_nom' => 'nullable|string|max:255',
            'mere_tel' => 'nullable|string|max:30',
            'mere_profession' => 'nullable|string|max:255',
            'mere_email' => 'nullable|email|max:255',
            'mere_adresse' => 'nullable|string|max:255',
        ]);

        ParentEleve::updateOrCreate(
            ['eleve_id' => $eleveId],
            $data
        );

        return back()->with('success', 'Informations des parents mises à jour avec succès.');
    }
}
