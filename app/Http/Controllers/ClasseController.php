<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Classe;
use App\Models\Decoupage;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\AnneesScolaire;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Niveau;
use Illuminate\Http\Request;
class ClasseController extends Controller
{


    public function index()
    {
        // 📌 Année active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $enseignants = Enseignant::all();
        $annees = AnneesScolaire::all();

        // ✅ Récupérer tous les niveaux dynamiques
        $niveaux = Niveau::orderBy('nom')->get();

        // 📌 Classes de l'année active
        $classes = Classe::with(['enseignant', 'annee'])
            ->withCount('inscriptions')
            ->when($anneeActive, function ($query) use ($anneeActive) {
                $query->where('annee_id', $anneeActive->id);
            })
            ->get();


        logAction(
            'Consultation des classes',
            "Affichage des classes pour l'année scolaire {$anneeActive->nom}"
        );

        return view('classes.index', compact(
            'classes',
            'enseignants',
            'annees',
            'anneeActive',
            'niveaux',
        ));
    }


    public function listeclasses()
    {
        // ✅ Récupérer l'année scolaire active : session ou active par défaut
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $anneeId = $anneeActive ? $anneeActive->id : null;

        // ✅ Charger uniquement les classes de cette année
        $classes = Classe::with(['enseignant', 'annee'])
            ->withCount('inscriptions')
            ->when($anneeId, function ($query) use ($anneeId) {
                $query->where('annee_id', $anneeId);
            })
            ->get();
        logAction('Consultation', "Liste des classes affichée pour l'année {$anneeActive->nom}");

        return view('classes.list', compact('classes', 'anneeActive'));
    }


    public function store(Request $request)
    {
        // 📌 Récupérer l'année active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $validated = $request->validate([
            'nom'           => 'required|string|max:50',
            'niveau_id' => 'required|exists:niveaux,id',
            'type_decoupage' => 'nullable|string'


        ]);
        $validated['annee_id']= $anneeActive->id;
        $classe =  Classe::create($validated);
        logAction('Création', "Nouvelle classe créée : {$classe->nom} ({$classe->niveau}) - Année {$classe->annee->nom}");

        return redirect()->route('classes.index')->with('success', 'Classe ajoutée avec succès');
    }

    public function update(Request $request, Classe $classe)
    {

        $before = $classe->toArray();

        $validated = $request->validate([
            'nom'      => 'required|string|max:50',
            'niveau_id' => 'required|exists:niveaux,id',
            'enseignant_id' => 'nullable|exists:enseignants,id',
            'decoupage_id' => 'nullable|exists:decoupages,id',
            'type_decoupage' => 'nullable|string'

        ]);

        // Ignorer les champs null (sécurité)
        $validated = array_filter($validated, fn($v) => $v !== null);

        $classe->update($validated);

        $after = $classe->fresh()->toArray();

        logAction('Mise à jour', "Classe modifiée : {$classe->nom} ({$classe->niveau}) - Avant: " . json_encode($before) . " Après: " . json_encode($after));

        return redirect()->route('classes.index')->with('success', 'Classe mise à jour avec succès');
    }


    public function destroy(Classe $classe)
    {
        // Vérifier si la classe a des inscriptions
        if ($classe->inscriptions()->exists()) {
            return redirect()->route('classes.index')
                ->with('error', 'Impossible de supprimer cette classe car elle contient encore des élèves inscrits.');
        }

        $nomClasse = $classe->nom;

        // ✅ Si aucune inscription, supprimer
        $classe->delete();
        logAction('Suppression', "Classe supprimée : {$nomClasse}");

        return redirect()->route('classes.index')
            ->with('success', 'Classe supprimée avec succès.');
    }

    public function show(Classe $classe)
    {
        // Supprimer les doublons basés sur le nom de la matière
        $affectationsUniques = $classe->affectations->unique(function ($item) {
            return strtolower(trim($item->matiere->nom));
        });
        logAction('Consultation', "Détails de la classe affichés : {$classe->nom}");

        return view('classes.show', [
            'classe' => $classe,
            'affectations' => $affectationsUniques
        ]);
    }

    public function affecterEnseignant(Classe $classe)
    {
        logAction('Consultation', "Ouverture de la page d’affectation d’un enseignant à la classe {$classe->nom}");

        // 1️⃣ Récupérer les enseignants déjà affectés à une matière
        $enseignantsDejaAffectes = Affectation::pluck('enseignant_id');

        // 2️⃣ Récupérer les enseignants déjà responsables d’une classe
        $enseignantsResponsables = Classe::whereNotNull('enseignant_id')->pluck('enseignant_id');

        // 3️⃣ Fusionner les deux collections
        $enseignantsExclus = $enseignantsDejaAffectes->merge($enseignantsResponsables)->unique();

        // 4️⃣ Récupérer uniquement les enseignants non exclus
        $enseignants = Enseignant::whereNotIn('id', $enseignantsExclus)->get();

        return view('classes.affecterEnseignant', compact('classe', 'enseignants'));
    }

    public function affecterProfesseurs(Classe $classe)
    {
        // Récupérer tous les enseignants
        $professeurs = Enseignant::whereNotIn('id', function ($query) {
            $query->select('enseignant_id')
                ->from('classes')
                ->whereNotNull('enseignant_id'); // On exclut uniquement les enseignants déjà responsables
        })->get();

        // Matières déjà affectées (basé sur nom)
        $matieresDejaAttribuees = $classe->affectations->map(function ($aff) {
            return strtolower(trim($aff->matiere->nom));
        })->unique();

        // On filtre les matières à proposer
        $matieres = Matiere::all()->filter(function ($matiere) use ($matieresDejaAttribuees) {
            return !$matieresDejaAttribuees->contains(strtolower(trim($matiere->nom)));
        });
        logAction('Consultation', "Affectation des professeurs à la classe {$classe->nom} ({$classe->annee->nom}).");

        return view('classes.affecterProfesseurs', compact('classe', 'matieres', 'professeurs'));
    }



    public function inscrireEleves($id)
    {
        $classe = Classe::findOrFail($id);

        // Année en cours de la classe
        $anneeId = $classe->annee_id;

        // Récupérer les élèves déjà inscrits pour cette année
        $elevesDejaInscrits = Inscription::where('annee_id', $anneeId)
            ->pluck('eleve_id')
            ->toArray();

        // Récupérer uniquement les élèves non inscrits cette année
        $eleves = Eleve::whereNotIn('id', $elevesDejaInscrits)->get();
        logAction('Consultation', "Préparation à l’inscription d’élèves dans la classe {$classe->nom} ({$classe->annee->nom}).");

        return view('classes.inscrireEleves', compact('classe', 'eleves'));
    }

    /**
     * ✅ Affecter un enseignant responsable (primaire)
     */
    public function storeEnseignant(Request $request, $id)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
        ]);

        $classe = Classe::findOrFail($id);
        $classe->enseignant_id = $request->enseignant_id;
        $classe->save();

        $enseignant = Enseignant::find($request->enseignant_id);

        logAction('Affectation', "Enseignant {$enseignant->nom} affecté à la classe {$classe->nom}");

        return redirect()->route('classes.show', $classe->id)
            ->with('success', 'Enseignant affecté avec succès à la classe.');
    }

    /**
     * ✅ Affecter des professeurs aux matières (collège/lycée)
     */
    public function storeProfesseurs(Request $request, $id)
    {
        $request->validate([
            'matieres'    => 'required|array',
            'professeurs' => 'required|array',
        ]);

        $classe = Classe::findOrFail($id);

        $classe->professeurs()->detach();

        $details = [];

        foreach ($request->matieres as $index => $matiereId) {
            $profId = $request->professeurs[$index] ?? null;
            if ($profId) {
                $classe->professeurs()->attach($profId, [
                    'matiere_id' => $matiereId
                ]);

                $matiere = Matiere::find($matiereId);
                $prof = Enseignant::find($profId);
                $details[] = "{$matiere->nom} → {$prof->nom} {$prof->prenom}";
            }
        }

        logAction('Affectation', "Professeurs affectés à la classe {$classe->nom} : " . implode(', ', $details));

        return redirect()->route('classes.show', $classe->id)
            ->with('success', 'Professeurs affectés avec succès.');
    }


    /**
     * ✅ Inscrire des élèves dans une classe
     */
    public function storeEleves(Request $request, $id)
    {
        $request->validate([
            'eleves' => 'required|array',
        ]);

        $classe = Classe::findOrFail($id);

        // ✅ Récupération de l'année scolaire active (via ta relation classe → année)
        $anneeId = $classe->annee_id;

        $messagesErreur = [];

        foreach ($request->eleves as $eleveId) {
            $eleve = \App\Models\Eleve::find($eleveId); // ✅ Toujours défini

            // Vérifier si l'élève est déjà inscrit dans CETTE année scolaire
            $existe = Inscription::where('eleve_id', $eleveId)
                ->where('annee_id', $anneeId)
                ->exists();

            if ($existe) {
                $messagesErreur[] = "{$eleve->nom} {$eleve->prenom} est déjà inscrit dans une autre classe pour l'année en cours.";
                continue; // passer au suivant
            }

            // ✅ Si pas encore inscrit, on enregistre
            Inscription::create([
                'classe_id' => $classe->id,
                'eleve_id'  => $eleveId,
                'annee_id'  => $anneeId,
                'date_inscription' => now(),
            ]);

            logAction('Inscription', "Élève {$eleve->nom} {$eleve->prenom} inscrit dans la classe {$classe->nom}");
        }


        // Retour avec messages
        if (!empty($messagesErreur)) {
            return back()->with('error', implode('<br>', $messagesErreur));
        }

        return back()->with('success', 'Inscription effectueé avec succès.');
    }


    public function retirerEnseignant(Classe $classe)
    {
        $enseignant = $classe->enseignant?->nom ?? 'Aucun';

        $classe->enseignant_id = null; // libérer le champ
        $classe->save();
        logAction('Retrait', "Enseignant {$enseignant} retiré de la classe {$classe->nom}");

        return back()->with('success', 'Enseignant responsable retiré avec succès.');
    }

    public function retirerProfesseur(Classe $classe, Affectation $affectation)
    {
        $matiereNom = $affectation->matiere->nom ?? 'Inconnue';
        $enseignantNom = $affectation->enseignant->nom ?? 'Inconnu';
        $affectation->delete();
        logAction('Retrait', "Professeur {$enseignantNom} retiré de la matière {$matiereNom} dans la classe {$classe->nom} ({$classe->annee->nom}).");

        return redirect()->route('classes.show', $classe->id)
            ->with('success', 'Professeur retiré de la classe avec succès.');
    }

    public function affecterElevesfromclass(Request $request, $id)
    {
        $classe = Classe::findOrFail($id);
        $anneeId = session('annee_id') ?? \App\Models\AnneesScolaire::where('active', 1)->value('id');

        $request->validate([
            'eleves' => 'required|array',
            'eleves.*' => 'exists:eleves,id'
        ]);

        $details = [];

        foreach ($request->eleves as $eleveId) {
            $exists = \App\Models\Inscription::where('eleve_id', $eleveId)
                ->where('annee_id', $anneeId)
                ->exists();

            if (!$exists) {
                \App\Models\Inscription::create([
                    'eleve_id' => $eleveId,
                    'classe_id' => $classe->id,
                    'annee_id' => $anneeId,
                ]);

                $eleve = Eleve::find($eleveId);
                $details[] = "{$eleve->nom} {$eleve->prenom}";
            }
        }

        logAction('Affectation', "Élèves affectés à la classe {$classe->nom} ({$classe->annee->nom}) : " . implode(', ', $details));

        return back()->with('success', 'Élèves affectés avec succès.');
    }

    public function imprimer($id)
    {
        $classe = Classe::findOrFail($id);

        // ⚡ Récupérer les élèves inscrits à cette classe triés par nom
        $eleves = $classe->inscriptions()
            ->with('eleve')
            ->join('eleves', 'inscriptions.eleve_id', '=', 'eleves.id') // jointure sur table élèves
            ->orderBy('eleves.nom', 'asc') // tri par nom (A → Z)
            ->get()
            ->pluck('eleve'); // garder uniquement les élèves

        logAction('Impression', "Liste des élèves imprimée pour la classe {$classe->nom}");

        return view('classes.imprimer', compact('classe', 'eleves'));
    }

    public function imprimercomplete($id)
    {
        $classe = Classe::findOrFail($id);

        // ⚡ Récupérer les élèves inscrits à cette classe triés par nom
        $eleves = $classe->inscriptions()
            ->with('eleve')
            ->join('eleves', 'inscriptions.eleve_id', '=', 'eleves.id') // jointure sur table élèves
            ->orderBy('eleves.nom', 'asc') // tri par nom (A → Z)
            ->get()
            ->pluck('eleve'); // garder uniquement les élèves

        logAction('Impression', "Liste des élèves imprimée pour la classe {$classe->nom}");

        return view('classes.imprimercomplete', compact('classe', 'eleves'));
    }

    public function retirerEleve($classeId, $eleveId)
    {
        // Récupérer l'inscription
        $inscription = \App\Models\Inscription::where('classe_id', $classeId)
            ->where('eleve_id', $eleveId)
            ->first();

        if (!$inscription) {
            return back()->with('error', 'Élève non trouvé dans cette classe.');
        }

        $eleve = $inscription->eleve;
        $classe = $inscription->classe;

        // 1️⃣ Supprimer les paiements liés à cet élève
        $paiements = \App\Models\Paiement::where('eleve_id', $eleveId)->get();
        if ($paiements->count() > 0) {
            foreach ($paiements as $paiement) {
                $paiement->delete();
            }
        }

        // 2️⃣ Supprimer les attributions de bourse si elles existent
        $attributions = \App\Models\AttributionBourse::where('inscription_id', $inscription->id)->get();
        if ($attributions->count() > 0) {
            foreach ($attributions as $attrib) {
                $attrib->delete();
            }
        }

        // 3️⃣ Supprimer l'inscription
        $inscription->delete();

        // 4️⃣ Log
        logAction('Retrait élève', "Élève {$eleve->nom} {$eleve->prenom} retiré de la classe {$classe->nom}");

        return back()->with('success', 'Élève retiré de la classe avec toutes ses lignes de paiements et bourses supprimées.');
    }


    public function removeEnseignant($classeId)
    {
        $classe = Classe::findOrFail($classeId);
        $enseignantNom = $classe->enseignant->nom ?? 'Aucun';


        $classe->enseignant_id = null;
        $classe->save();
        logAction('Retrait', "Enseignant {$enseignantNom} retiré de la classe {$classe->nom} ({$classe->annee->nom}).");

        return redirect()->route('matieres.dansclasse', $classeId)
            ->with('success', 'Enseignant retiré de la classe avec succès.');
    }


}
