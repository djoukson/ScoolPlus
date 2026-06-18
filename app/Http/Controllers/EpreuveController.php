<?php
namespace App\Http\Controllers;

use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Epreuve;
use App\Models\Evaluation;
use App\Models\Matiere;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpreuveController extends Controller
{

    public function index()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 📌 Récupérer l'année scolaire active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? \App\Models\AnneesScolaire::find(session('annee_id'))
            : \App\Models\AnneesScolaire::where('active', 1)->first();

//        // 📌 Récupérer les classes de l'année active dont le niveau n'est pas 'Primaire'
//        $classes = \App\Models\Classe::where('annee_id', $anneeActive->id)
//            ->whereHas('niveau', function($query) {
//                $query->where('nom', '!=', 'Primaire');
//            })
//            ->get();
        // 📌 Récupérer les classes de l'année active dont le niveau n'est pas 'Primaire'
        $classes = \App\Models\Classe::where('annee_id', $anneeActive->id)
            ->whereHas('niveau', function($query) {
                $query->where('nom', '!=', 'Primaire');
            })
            ->get();

        // 📌 Récupérer toutes les matières liées aux classes non primaires
        $niveauIds = $classes->pluck('niveau_id')->unique();
        $matieres = \App\Models\Matiere::whereIn('niveau_id', $niveauIds)->get();

        // 📌 Récupérer tous les types d'évaluation
        $typesEvaluation = \App\Models\TypeEvaluation::all();


// 📌 Récupérer l'utilisateur connecté
        $user = auth()->user();

// 📌 Requête de base
        $epreuvesQuery = \App\Models\Epreuve::whereIn('classe_id', $classes->pluck('id'))
            ->where('annee_id', $anneeActive->id)
            ->with(['classe', 'matiere', 'typeEvaluation', 'users', 'decoupage']);

// 📌 Filtre selon le rôle
        if (!in_array($user->role, [ 'admin','directeur', 'secretaire'])) {
            // Si ce n'est PAS admin / directeur / secrétaire → seulement ses épreuves
            $epreuvesQuery->where('uploaded_by', $user->id);
        }

// 📌 Récupération finale
        $epreuves = $epreuvesQuery
            ->orderBy('id', 'desc')
            ->get();

        $decoupages = \App\Models\Decoupage::where('annee_id', $anneeActive->id)->get();


        return view('epreuves.index', compact('classes','decoupages', 'matieres', 'typesEvaluation', 'epreuves', 'anneeActive'));
    }



    public function upload(Request $request)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 📌 Récupérer l'année scolaire active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? \App\Models\AnneesScolaire::find(session('annee_id'))
            : \App\Models\AnneesScolaire::where('active', 1)->first();


        $request->validate([
            'epreuve' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'classe_id' => 'required|exists:classes,id',
            'matiere_id' => 'required|exists:matieres,id',
            'type_evaluation_id' => 'required|exists:types_evaluations,id',
            'decoupage_id' => 'required|exists:decoupages,id',
        ]);

        $file = $request->file('epreuve');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->storeAs('epreuves', $filename, 'public');

        Epreuve::create([
            'nom_fichier' => $filename,
            'chemin_fichier' => 'storage/epreuves/'.$filename,
            'uploaded_by' => auth()->id(),
            'classe_id' => $request->classe_id,
            'matiere_id' => $request->matiere_id,
            'type_evaluation_id' => $request->type_evaluation_id,
            'annee_id' => $anneeActive->id, // <-- ici
            'decoupage_id' => $request->decoupage_id, // <-- ici


        ]);

        return back()->with('success', 'Épreuve envoyée avec succès.');
    }


    public function destroy($id)
    {
        // 1️⃣ Vérifier l’authentification
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // 2️⃣ Récupérer l’épreuve
        $epreuve = \App\Models\Epreuve::with(['classe','matiere','typeEvaluation','decoupage','annee'])
            ->findOrFail($id);

        $user = auth()->user();

        // 3️⃣ Vérifier les permissions
        if (
            $user->id !== $epreuve->uploaded_by
            && !in_array($user->role, ['admin','directeur','secretaire'])
        ) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette épreuve.');
        }

        // ✅ SUPPRESSION DU FICHIER PHYSIQUE
        $filePath = public_path('storage/epreuves/'.$epreuve->nom_fichier);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // 5️⃣ LOG de l’action
        logAction(
            'Suppression épreuve',
            "Suppression de l’épreuve '{$epreuve->nom_fichier}'
        | Classe: {$epreuve->classe->nom}
        | Matière: {$epreuve->matiere->nom}
        | Type: {$epreuve->typeEvaluation->nom}
        | Découpage: " . optional($epreuve->decoupage)->nom . "
        | Année: {$epreuve->annee->nom}
        | Supprimée par: {$user->name} ({$user->role})"
        );

        // 6️⃣ Suppression BDD
        $epreuve->delete();

        // 7️⃣ Message retour
        return back()->with('success', 'Épreuve supprimée avec succès.');
    }
    public function update(Request $request, $id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $epreuve = \App\Models\Epreuve::findOrFail($id);

        $user = auth()->user();

        // Vérification permissions
        if ($user->id !== $epreuve->uploaded_by && !in_array($user->role, ['admin','directeur','secretaire'])) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à modifier cette épreuve.');
        }

        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'matiere_id' => 'required|exists:matieres,id',
            'type_evaluation_id' => 'required|exists:types_evaluations,id',
            'decoupage_id' => 'required|exists:decoupages,id',
            'epreuve' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Si un nouveau fichier est uploadé
        if ($request->hasFile('epreuve')) {
            // Supprimer l'ancien
            $oldFile = public_path('storage/epreuves/' . $epreuve->nom_fichier);
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            $file = $request->file('epreuve');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('epreuves', $filename, 'public');

            $epreuve->nom_fichier = $filename;
            $epreuve->chemin_fichier = 'storage/epreuves/'.$filename;
        }

        $epreuve->classe_id = $request->classe_id;
        $epreuve->matiere_id = $request->matiere_id;
        $epreuve->type_evaluation_id = $request->type_evaluation_id;
        $epreuve->decoupage_id = $request->decoupage_id;
        $epreuve->save();

        return back()->with('success', 'Épreuve modifiée avec succès.');
    }
    public function updateEtat(Request $request, $id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $epreuve = Epreuve::findOrFail($id);
        $epreuve->etat = $request->etat;
        $epreuve->save();

        return redirect()->back()->with('success', 'État mis à jour !');
    }



}
