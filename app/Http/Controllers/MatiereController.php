<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Cour;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatiereController extends Controller
{
    public function index()
    {
        $matieres = Matiere::all();
        $niveaux = Niveau::all();

        $petitsMots = ['de', 'la', 'et', 'du', 'des', 'le', 'les', 'à', 'au'];

        foreach ($matieres as $matiere) {
            // Séparer le nom de la matière sur tout séparateur non alphanumérique
            $mots = preg_split('/[^\p{L}\p{N}]+/u', trim($matiere->nom));

            // Filtrer les petits mots
            $motsSignificatifs = array_filter($mots, fn($mot) => !in_array(mb_strtolower($mot), $petitsMots) && $mot !== '');

            if (count($motsSignificatifs) >= 2) {
                $sigle = collect($motsSignificatifs)
                    ->map(function($m) {
                        // Prendre la première lettre
                        $lettre = mb_substr($m, 0, 1);
                        // Supprimer les accents avec iconv
                        $lettre = iconv('UTF-8', 'ASCII//TRANSLIT', $lettre);
                        // Garder uniquement les lettres A-Z
                        $lettre = preg_replace('/[^A-Za-z]/', '', $lettre);
                        return strtoupper($lettre);
                    })
                    ->implode('');

                $matiere->sigle = $sigle;
            } else {
                $matiere->sigle = null;
            }

            $matiere->save();
        }



        logAction('Consultation', "Affichage de la liste des matières.");

        return view('matieres.index', compact('matieres','niveaux'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100',
            'niveau_id'      => 'required|integer|max:50',
            'coefficient'   => 'nullable|integer|min:1|max:20',
        ]);

        $matiere = Matiere::create($validated);

        logAction('Création', "Ajout d'une nouvelle matière : {$matiere->nom} (Niveau : {$matiere->niveau}).");

        return redirect()->route('matieres.index')->with('success', 'Matière ajoutée avec succès');
    }

    public function update(Request $request, Matiere $matiere)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100',
            'niveau_id'      => 'required|integer|max:50',
            'coefficient'   => 'nullable|integer|min:1|max:20',
        ]);

        $matiere->update($validated);

        logAction('Modification', "Matière mise à jour : {$matiere->nom} (ID : {$matiere->id}).");

        return redirect()->route('matieres.index')->with('success', 'Matière mise à jour avec succès');
    }

    public function destroy(Matiere $matiere)
    {
        // Vérifier si la matière est affectée à une classe
        $isAffectee = DB::table('affectations')
            ->where('matiere_id', $matiere->id)
            ->exists();

        if ($isAffectee) {
            return redirect()->route('matieres.index')
                ->with('error', 'Impossible de supprimer cette matière car elle est déjà affectée à une classe.');
        }

        // Log et suppression
        logAction('Suppression', "Matière supprimée : {$matiere->nom} (ID : {$matiere->id}).");
        $matiere->delete();

        return redirect()->route('matieres.index')->with('success', 'Matière supprimée avec succès');
    }

    public function storeProfesseursdansclasse(Request $request, $classeId)
    {
        $request->validate([
            'matieres'     => 'required|array',
            'matieres.*'   => 'required|exists:matieres,id',
            'professeurs'  => 'required|array',
            'professeurs.*'=> 'required|exists:enseignants,id',
            'heures'       => 'nullable|array',
            'heures.*'     => 'nullable|integer|min:0',
        ]);

        $classe = Classe::findOrFail($classeId);

        // Récupérer l'année active (session en priorité, sinon active)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->withErrors(['annee' => 'Aucune année scolaire active trouvée.']);
        }

        foreach ($request->matieres as $key => $matiereId) {
            $profId = $request->professeurs[$key] ?? null;
            $heures = $request->heures[$key] ?? null;

            if (!$profId) continue;

            // Supprimer l'ancienne affectation pour la même classe+matière+année
            Affectation::where('classe_id', $classe->id)
                ->where('matiere_id', $matiereId)
                ->where('annee_id', $anneeActive->id)
                ->delete();

            Affectation::create([
                'classe_id'        => $classe->id,
                'matiere_id'       => $matiereId,
                'enseignant_id'    => $profId,
                'annee_id'         => $anneeActive->id,
                'heures_attribuees'=> $heures,
            ]);
        }

        logAction('Affectation', "Affectation de professeurs dans la classe {$classe->nom} pour l’année {$anneeActive->nom}.");

        return redirect()->route('classes.show', $classe->id)
            ->with('success', 'Professeurs affectés avec succès.');
    }

    public function matiereparclasse()
    {
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $classes = Classe::withCount('affectations')
            ->where('annee_id', $anneeActive->id)
            ->get();

        logAction('Consultation', "Consultation de la liste des matières par classe pour l’année {$anneeActive->nom}.");

        return view('matieres.matiereparclasse', compact('classes', 'anneeActive'));
    }

    public function matieredansclasse($id)
    {
        $classe = Classe::with(['affectations.matiere', 'affectations.enseignant'])->findOrFail($id);

        $matieres = $classe->affectations->map(fn($affectation) => $affectation->matiere);

        $nomsMatieresDejaAffectees = $classe->affectations
            ->map(fn($affectation) => $affectation->matiere->nom)
            ->unique()
            ->toArray();

        $toutesMatieres = Matiere::whereNotIn('nom', $nomsMatieresDejaAffectees)->get();

        $enseignants = Enseignant::whereNotIn('id', function ($query) {
            $query->select('enseignant_id')
                ->from('affectations')
                ->whereNotNull('enseignant_id');
        })
            ->whereNotIn('id', function ($query) {
                $query->select('enseignant_id')
                    ->from('classes')
                    ->whereNotNull('enseignant_id');
            })
            ->get();

        $professeurs = Enseignant::whereNotIn('id', function ($query) {
            $query->select('enseignant_id')->from('classes')->whereNotNull('enseignant_id');
        })
            ->get();

        logAction('Consultation', "Consultation des matières dans la classe {$classe->nom}.");

        return view('matieres.matieredansclasse', compact('classe','professeurs', 'matieres', 'toutesMatieres', 'enseignants'));
    }

    public function affecterMatieres(Request $request, $classeId)
    {
        $classe = Classe::findOrFail($classeId);

        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if ($classe->niveau == 'primaire') {
            $request->validate([
                'enseignant_id' => 'required|exists:enseignants,id',
            ]);

            $classe->update([
                'enseignant_id' => $request->enseignant_id,
            ]);

            logAction('Affectation', "Affectation de l’enseignant ID {$request->enseignant_id} à la classe {$classe->nom} (niveau primaire).");
        } else {
            $request->validate([
                'matieres' => 'required|array',
                'enseignant_id' => 'required|array',
                'heures_attribuees' => 'required|array|min:1',
            ]);

            foreach ($request->matieres as $index => $matiereId) {
                Affectation::firstOrCreate([
                    'classe_id'         => $classe->id,
                    'matiere_id'        => $matiereId,
                    'annee_id'          => $anneeActive->id,
                    'enseignant_id'     => $request->enseignant_id[$index],
                    'heures_attribuees' => $request->heures_attribuees[$index],
                ]);
                 logAction('Affectation', "Affectation de l’enseignant ID {$request->enseignant_id[$index]} aux matières de la classe {$classe->nom} pour l’année {$anneeActive->nom}.");

            }

        }

        return redirect()->route('matieres.dansclasse', $classe->id)
            ->with('success', 'Affectation réalisée avec succès.');
    }

    public function mesMatieres()
    {
        $matricule = auth()->user()->matricule;

        $enseignant = Enseignant::where('matricule', $matricule)->firstOrFail();

        $affectations = Affectation::with(['classe', 'matiere'])
            ->withCount('emploisDuTemps')
            ->where('enseignant_id', $enseignant->id)
            ->get();

        logAction('Consultation', "Consultation des matières assignées à l’enseignant {$enseignant->nom} (Matricule : {$enseignant->matricule}).");

        return view('matieres.mes_matieres', compact('enseignant', 'affectations'));
    }
}
