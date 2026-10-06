<?php
namespace App\Http\Controllers;

use App\Models\AnneesScolaire;
use App\Models\Affectation;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Decoupage;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{

    public function evaluations()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Récupérer l'année active (session en priorité, sinon active)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        // Vérifier si l'utilisateur est admin, directeur ou secrétaire
        if (in_array($user->role, ['admin', 'directeur', 'secretaire'])) {
            // On envoie toutes les classes pour cette année
            $classes = Classe::withCount('affectations')
                ->where('annee_id', $anneeActive->id)
                ->get();
        } else {
            // Sinon, on récupère les classes où l'enseignant se trouve
            $enseignant = Enseignant::where('matricule', $user->matricule)->first();

            if ($enseignant) {
                // On récupère les classes liées aux affectations de cet enseignant
                $classes = Classe::where('annee_id', $anneeActive->id)
                    ->whereHas('affectations', function ($query) use ($enseignant) {
                        $query->where('enseignant_id', $enseignant->id);
                    })
                    ->withCount('affectations')
                    ->get();
            } else {
                // Aucun enseignant trouvé, on retourne une collection vide
                $classes = collect();
            }
        }
         logAction('Consultation', 'Affichage de la liste des évaluations.');
//dd($data);
        return view('evaluations.index', compact('classes', 'anneeActive'));
    }


    public function createByClasse($classeId)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        abort_if(!$anneeActive, 404, 'Aucune année scolaire active.');

        $classe = Classe::with(['affectations.matiere', 'affectations.enseignant'])
            ->where('annee_id', $anneeActive->id)
            ->findOrFail($classeId);
        $this->assertCanAccessEvaluationClass($classe->id);

        $anneeId = $anneeActive->id;
        $typesEvaluations = TypeEvaluation::all();


        $inscriptions = Inscription::with('eleve')
            ->where('classe_id', $classeId)
            ->where('annee_id', $anneeId)
            ->get();

        $decoupages = Decoupage::where('annee_id', $anneeId)
            ->when($classe->type_decoupage, function($query, $typeDecoupage) {
                return $query->where('type', $typeDecoupage);
            })
            ->get();
      //  dd($decoupages);

        // 🔹 Utilisateur connecté
        $user = auth()->user();

        // 🔹 Si c’est un admin, directeur ou secrétaire → tout voir
        if (in_array($user->role, ['admin', 'directeur', 'secretaire'])) {
            $matieres = $classe->affectations
                ->map(fn($affectation) => $affectation->matiere)
                ->unique('id')
                ->values();

            $evaluations = Evaluation::with(['inscription.eleve', 'matiere', 'typeEvaluation', 'decoupage'])
                ->whereIn('inscription_id', $inscriptions->pluck('id'))
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            // 🔹 Si c’est un enseignant → restreindre
            $enseignant = Enseignant::where('matricule', $user->matricule)->first();

            if ($enseignant) {
                // Récupérer les matières de cet enseignant pour cette classe
                $matieres = $classe->affectations
                    ->filter(fn($affectation) => $affectation->enseignant_id == $enseignant->id)
                    ->map(fn($affectation) => $affectation->matiere)
                    ->unique('id')
                    ->values();

                $matiereIds = $matieres->pluck('id');

                // ⚠️ L’enseignant ne voit que les évaluations de ses matières
                $evaluations = Evaluation::with(['inscription.eleve', 'matiere', 'typeEvaluation', 'decoupage'])
                    ->whereIn('inscription_id', $inscriptions->pluck('id'))
                    ->whereIn('matiere_id', $matiereIds)
                    ->orderBy('updated_at', 'desc')
                    ->get();
            } else {
                $matieres = collect();
                $evaluations = collect();
            }
        }

        logAction('Accès création', "Ouverture du formulaire de création d’évaluation pour la classe : {$classe->nom}");

        return view('evaluations.create', compact(
            'classe',
            'inscriptions',
            'matieres',
            'typesEvaluations',
            'decoupages',
            'evaluations'
        ));
    }




    public function store(Request $request)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $request->validate([
            'matiere_id' => 'required|exists:matieres,id',
            'type_evaluation_id' => 'required|exists:types_evaluations,id',
            'decoupage_id' => 'required|exists:decoupages,id',
            'date_eval' => 'required|date',
            'notes' => 'required|array',
            'notes.*.inscription_id' => 'required|exists:inscriptions,id',
            'notes.*.note' => 'nullable|numeric|min:0|max:20',
        ]);

        $decoupage = Decoupage::findOrFail($request->decoupage_id);
        foreach ($request->notes as $noteData) {
            $inscription = Inscription::findOrFail($noteData['inscription_id']);
            abort_unless(
                (int) $inscription->annee_id === (int) $decoupage->annee_id,
                422,
                'Le découpage ne correspond pas à l’année de l’inscription.'
            );
            $this->assertCanManageEvaluationAssignment($inscription->classe_id, (int) $request->matiere_id);
        }

        foreach ($request->notes as $noteData) {

            // Une note à 0 est une vraie note et doit être enregistrée.
            // Les champs vides restent ignorés pour préserver les notes déjà saisies.
            $noteValue = $noteData['note'] ?? null;
            if ($noteValue !== null && $noteValue !== '') {
                $evaluation = Evaluation::updateOrCreate(
                    [
                        'inscription_id' => $noteData['inscription_id'],
                        'matiere_id' => $request->matiere_id,
                        'type_evaluation_id' => $request->type_evaluation_id,
                        'decoupage_id' => $request->decoupage_id,
                    ],
                    [
                        'note' => $noteValue,
                        'date_eval' => $request->date_eval,
                    ]
                );
                logAction('Création réussie', "Évaluation créée avec succès : ID {$evaluation->id}");
            }

        }


        return redirect()->back()->with('success', 'Notes enregistrées/modifiées ✅');
    }



    public function update(Request $request, $id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $request->validate([
            'note' => 'required|numeric|min:0|max:20',
            'date_eval' => 'required|date',
        ]);

        $evaluation = Evaluation::with(['inscription.eleve', 'inscription.classe', 'matiere'])->findOrFail($id);
        $this->assertCanManageEvaluationAssignment($evaluation->inscription->classe_id, $evaluation->matiere_id);

        // 🔹 Informations avant la mise à jour
        $infosAvant = sprintf(
            "Évaluation avant mise à jour : Élève : %s | Classe : %s | Matière : %s | Note : %s | Date : %s",
            $evaluation->inscription->eleve->nom ?? 'Inconnu',
            $evaluation->inscription->classe->nom ?? 'Inconnue',
            $evaluation->matiere->nom ?? 'Inconnue',
            $evaluation->note,
            $evaluation->date_eval
        );

        // 🔹 Mise à jour effective
        $evaluation->update([
            'note' => $request->note,
            'date_eval' => $request->date_eval,
        ]);

        // 🔹 Log de l’action
        logAction('Mise à jour réussie', $infosAvant);

        return redirect()->back()->with('success', 'Note mise à jour avec succès ✅');
    }



    public function destroy($id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $evaluation = Evaluation::with(['inscription.eleve', 'inscription.classe', 'matiere'])->findOrFail($id);
        $this->assertCanManageEvaluationAssignment($evaluation->inscription->classe_id, $evaluation->matiere_id);

        // 🔹 Sauvegarde des infos avant suppression
        $eleveNom   = $evaluation->inscription->eleve->nom ?? 'Inconnu';
        $classeNom  = $evaluation->inscription->classe->nom ?? 'Inconnue';
        $matiereNom = $evaluation->matiere->nom ?? 'Inconnue';
        $note       = $evaluation->note ?? 'N/A';

        // 🔹 Suppression
        $evaluation->delete();

        // 🔹 Log avec détails complets
        logAction(
            'Suppression d’évaluation',
            "Évaluation supprimée pour l’élève {$eleveNom} (Classe : {$classeNom}, Matière : {$matiereNom}, Note : {$note})"
        );

        return redirect()->back()->with('success', 'Évaluation supprimée ✅');
    }



    public function notes($classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        $classe = Classe::findOrFail($classe_id);
        $teacherMatterIds = $this->assignedMatterIdsForClass($classe->id);

        // ✅ Récupérer toutes les évaluations des élèves inscrits dans cette classe
        $evaluations = Evaluation::whereHas('inscription', function($q) use ($classe_id) {
            $q->where('classe_id', $classe_id);
        })
            ->when($teacherMatterIds !== null, fn($query) => $query->whereIn('matiere_id', $teacherMatterIds))
            ->with(['inscription.eleve', 'matiere', 'decoupage', 'typeEvaluation'])
            ->get();

        // ✅ Extraire uniquement les découpages présents dans ces évaluations
        $decoupages = \App\Models\Decoupage::whereIn(
            'id',
            $evaluations->pluck('decoupage_id')->unique()
        )->get();
        logAction('Consultation', "Affichage des notes pour la classe : {$classe->nom}");

        return view('evaluations.notes', compact('classe', 'evaluations', 'decoupages'));
    }

    private function assertCanAccessEvaluationClass(int $classeId): void
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'directeur', 'secretaire'], true)) {
            return;
        }

        abort_unless($user->role === 'professeur', 403);

        $enseignant = Enseignant::where('matricule', $user->matricule)->first();
        abort_unless(
            $enseignant && Affectation::where('enseignant_id', $enseignant->id)
                ->where('classe_id', $classeId)
                ->exists(),
            403,
            'Cette classe ne vous est pas attribuée.'
        );
    }

    private function assertCanManageEvaluationAssignment(int $classeId, int $matiereId): void
    {
        $this->assertCanAccessEvaluationClass($classeId);

        $user = auth()->user();
        $affectations = Affectation::where('classe_id', $classeId)
            ->where('matiere_id', $matiereId);

        if ($user->role === 'professeur') {
            $enseignant = Enseignant::where('matricule', $user->matricule)->firstOrFail();
            $affectations->where('enseignant_id', $enseignant->id);
        }

        abort_unless($affectations->exists(), 403, 'Cette matière n’est pas attribuée pour cette classe.');
    }

    private function assignedMatterIdsForClass(int $classeId): ?array
    {
        $this->assertCanAccessEvaluationClass($classeId);

        if (in_array(auth()->user()->role, ['admin', 'directeur', 'secretaire'], true)) {
            return null;
        }

        $enseignant = Enseignant::where('matricule', auth()->user()->matricule)->firstOrFail();

        return Affectation::where('enseignant_id', $enseignant->id)
            ->where('classe_id', $classeId)
            ->pluck('matiere_id')
            ->map(fn($id) => (int) $id)
            ->all();
    }





}
