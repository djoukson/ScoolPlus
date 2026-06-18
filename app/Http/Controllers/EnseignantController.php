<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnseignantController extends Controller
{
    /**
     * Liste des enseignants
     */
    public function index(Request $request)
    {
        // Créer automatiquement un compte user pour les enseignants manquants
        $enseignantsSansUser = Enseignant::whereNotIn('matricule', function($q) {
            $q->select('matricule')->from('users');
        })->get();

        foreach ($enseignantsSansUser as $enseignant) {
            $user = User::create([
                'name'      => $enseignant->nom . ' ' . $enseignant->prenom,
                'email' => !empty($enseignant->email) ? $enseignant->email : null,
                'phone'     => $enseignant->tel ?? null,
                'username'  => strtolower($enseignant->prenom) . '.' . strtolower($enseignant->nom),
                'role'      => 'professeur',
                'matricule' => $enseignant->matricule ? : null,
                'password'  => Hash::make('sp12345$'),
            ]);

            logAction('Création automatique de compte', "Un compte utilisateur a été créé pour l'enseignant {$enseignant->nom} {$enseignant->prenom} (Matricule : {$enseignant->matricule}, Email : {$enseignant->email}).");
        }

        $enseignants = Enseignant::with('niveau')
            ->orderBy('created_at', 'desc')
            ->paginate(100);

        logAction('Consultation', "Affichage de la liste des enseignants (page {$request->get('page', 1)}).");

        return view('enseignants.index', compact('enseignants'));
    }



    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'specialite' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'tel'        => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:150|unique:users,email',
            'niveau_id'  => 'nullable|exists:niveaux,id',
            'salaire_mensuel' => 'nullable|numeric|min:0',
            'groupesanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',

        ]);

        // Génération du matricule
        $matricule = User::generateMatricule('ENS');
        $validated['matricule'] = $matricule;

        // Création de l'enseignant avec niveau
        $enseignant = Enseignant::create($validated);

        // Création du compte utilisateur associé
        User::create([
            'name'      => "{$validated['nom']} {$validated['prenom']}",
            'email'     => $validated['email'] ?? null,
            'phone'     => $validated['tel'] ?? null,
            'username'  => strtolower($validated['prenom']) . '.' . strtolower($validated['nom']),
            'role'      => 'professeur',
            'matricule' => $matricule,
            'password'  => Hash::make('sp12345$'),
        ]);

        logAction(
            'Création',
            "Nouvel enseignant ajouté : {$enseignant->nom} {$enseignant->prenom} " .
            "(Matricule : {$matricule}, Spécialité : {$enseignant->specialite}, " .
            "Téléphone : {$enseignant->tel}, Email : {$enseignant->email}, " .
            "Niveau : " . ($enseignant->niveau ? $enseignant->niveau->nom : 'Non défini') . ")."
        );

        return redirect()->route('enseignants.index')->with('success', "Enseignant ajouté avec succès ✅ (Matricule: $matricule)");
    }


    /**
     * Met à jour un enseignant existant
     */
    public function update(Request $request, Enseignant $enseignant)
    {
        $validated = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'specialite' => 'nullable|string|max:100',
            'tel'        => 'nullable|string|max:30',
            'type'       => 'nullable|string|max:100',
            'niveau_id'  => 'nullable|exists:niveaux,id', // <-- nouveau champ
            'email'      => 'nullable|email|max:150',
            'salaire_mensuel' => 'nullable|numeric|min:0',
            'groupesanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',

        ]);

        $enseignant->update($validated);

        logAction('Modification', "Informations mises à jour pour l'enseignant {$enseignant->nom} {$enseignant->prenom} (Matricule : {$enseignant->matricule}). Nouvelles données : " . json_encode($validated, JSON_UNESCAPED_UNICODE));

        return redirect()->route('enseignants.index')->with('success', 'Enseignant mis à jour avec succès');
    }
    public function show($id)
    {
        $enseignant = Enseignant::with('niveau')->findOrFail($id);

        return view('enseignants.show', compact('enseignant'));
    }

    /**
     * Supprime un enseignant
     */
    public function destroy(Enseignant $enseignant)
    {
        $matricule = $enseignant->matricule;
        $nomComplet = "{$enseignant->nom} {$enseignant->prenom}";

        $enseignant->delete();

        logAction('Suppression', "L’enseignant {$nomComplet} (Matricule : {$matricule}) a été supprimé du système.");

        return redirect()->route('enseignants.index')->with('success', 'Enseignant supprimé avec succès');
    }

    /**
     * Active ou désactive le statut d’un enseignant
     */
    public function toggleStatus($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $ancienStatut = $enseignant->statut ? 'actif' : 'inactif';
        $enseignant->statut = !$enseignant->statut;
        $enseignant->save();
        $nouveauStatut = $enseignant->statut ? 'actif' : 'inactif';

        logAction('Changement de statut', "Le statut de l’enseignant {$enseignant->nom} {$enseignant->prenom} (Matricule : {$enseignant->matricule}) est passé de {$ancienStatut} à {$nouveauStatut}.");

        return redirect()->route('enseignants.index')->with('success', 'Statut de l’enseignant modifié avec succès.');
    }

    /**
     * Liste des classes et affectations pour l’année scolaire
     */
    public function enseignantclasse()
    {
        // 🔹 Année scolaire courante
        $annee_courante = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        // 🔹 Charger les classes avec inscriptions, enseignant et affectations
        $classes = Classe::withCount('inscriptions')
            ->with([
                'enseignant',
                'affectations' => function ($query) use ($annee_courante) {
                    if ($annee_courante) {
                        $query->where('annee_id', $annee_courante->id)
                            ->with('enseignant');
                    }
                },
                // Relation titulaire pour cette année
                'titulaire' => function ($query) use ($annee_courante) {
                    if ($annee_courante) {
                        $query->where('annee_id', $annee_courante->id)
                            ->with('enseignant');
                    }
                }
            ])
            ->when($annee_courante, function ($query) use ($annee_courante) {
                $query->where('annee_id', $annee_courante->id);
            })
            ->get();

        // 🔹 Tous les enseignants (pour le modal), on peut filtrer primaire côté Blade
        $enseignants = Enseignant::all();

        // 🔹 Nom année pour log
        $annees = $annee_courante->nom ?? 'Non défini';

        // 🔹 Log
        logAction('Consultation', "Affichage des classes et affectations pour l’année scolaire {$annees}.");

        return view('enseignants.enseignantclasse', compact('classes', 'enseignants', 'annees', 'annee_courante'));
    }


    /**
     * Affectation des enseignants à une classe donnée
     */
    public function enseignantclasseaffectation($classeId)
    {
        $classe = Classe::with([
            'annee',
            'inscriptions',
            'enseignant',
            'affectations.enseignant',
        ])->findOrFail($classeId);

        $enseignants = Enseignant::where('niveau_id', 1) // Primaire
        ->whereNotIn('id', function ($query) {
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



        $professeurs = Enseignant::where('niveau_id', '!=', 1) // Collège
        ->whereNotIn('id', function ($query) {
            $query->select('enseignant_id')
                ->from('classes')
                ->whereNotNull('enseignant_id');
        })
            ->get();


        $matieresDejaAttribuees = $classe->affectations->map(function ($aff) {
            return strtolower(trim($aff->matiere->nom));
        })->unique();

        $matieres = Matiere::all()->filter(function ($matiere) use ($matieresDejaAttribuees) {
            return !$matieresDejaAttribuees->contains(strtolower(trim($matiere->nom)));
        });

        logAction('Consultation', "Ouverture de la page d’affectation des enseignants pour la classe {$classe->nom} (ID : {$classe->id}, Année : {$classe->annee->nom}).");

        return view('enseignants.enseignantclasseaffectation', compact('classe', 'enseignants', 'matieres', 'professeurs', 'matieres'));
    }

    /**
     * Enregistre une affectation depuis la fiche enseignant
     */
    public function affectationsdepuisenseignant(Request $request)
    {
        $anneeId = session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id');
        $classe = Classe::findOrFail($request->classe_id);


        if ($classe->niveau->nom === 'Primaire') {

            $validated = $request->validate([
                'enseignant_id' => 'required|exists:enseignants,id',
                'classe_id'     => 'required|exists:classes,id',
            ]);

            $classe->update([
                'enseignant_id' => $validated['enseignant_id'],
            ]);

            $enseignant = Enseignant::find($validated['enseignant_id']);
            logAction('Affectation', "L’enseignant {$enseignant->nom} {$enseignant->prenom} a été désigné responsable de la classe {$classe->nom} (Niveau primaire, Année ID {$anneeId}).");
        } else {
            $validated = $request->validate([
                'enseignant_id'     => 'required|exists:enseignants,id',
                'classe_id'         => 'required|exists:classes,id',
                'matiere_id'        => 'required|exists:matieres,id',
                'heures_attribuees' => 'nullable|integer|min:0',
                'note'              => 'nullable|string|max:255',
            ]);

            $data = array_merge($validated, [
                'annee_id' => $anneeId,
            ]);

            Affectation::create($data);

            $enseignant = Enseignant::find($validated['enseignant_id']);
            $matiere = Matiere::find($validated['matiere_id']);

            logAction('Affectation', "L’enseignant {$enseignant->nom} {$enseignant->prenom} a été affecté à la classe {$classe->nom} pour la matière {$matiere->nom} ({$validated['heures_attribuees']} h/semaine, Année ID {$anneeId}).");
        }

        return back()->with('success', 'Affectation enregistrée avec succès.');
    }

    public function listeParNiveau($niveau)
    {
        // Vérification valide des niveaux
        $niveauxValides = ['primaire', 'college', 'lycee'];

        if (!in_array($niveau, $niveauxValides)) {
            abort(404, "Niveau invalide");
        }

        // Mapping si la table 'niveaux' utilise d'autres noms (optionnel)
        $niveauMapping = [
            'primaire' => 1,
            'college'  => 2,
            'lycee'    => 3
        ];

        $niveauId = $niveauMapping[$niveau];

        // Récupération des enseignants du niveau demandé
        $enseignants = Enseignant::where('niveau_id', $niveauId)->get();

        return view('enseignants.print', [
            'enseignants' => $enseignants,
            'niveau' => ucfirst($niveau)
        ]);
    }



}
