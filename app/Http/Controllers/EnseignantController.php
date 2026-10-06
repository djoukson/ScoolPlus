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
use Illuminate\Support\Facades\DB;
use App\Models\Titulaire;
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
            $temporaryPassword = bin2hex(random_bytes(16));
            $enseignant->statut = false;
            $enseignant->save();

            $user = User::create([
                'name'      => $enseignant->nom . ' ' . $enseignant->prenom,
                'email' => !empty($enseignant->email) ? $enseignant->email : null,
                'phone'     => $enseignant->tel ?? null,
                'username'  => strtolower($enseignant->prenom) . '.' . strtolower($enseignant->nom),
                'role'      => 'professeur',
                'matricule' => $enseignant->matricule ? : null,
                'password'  => Hash::make($temporaryPassword),
                'status' => false,
                'must_change_password' => true,
            ]);
            $enseignant->user_id = $user->id;
            $enseignant->save();

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
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'directeur', 'secretaire'], true),
            403,
            'Vous n’êtes pas autorisé à créer un enseignant.'
        );

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

        // Tout nouvel enseignant reste inactif jusqu'à l'activation explicite.
        $validated['statut'] = false;
        $enseignant = Enseignant::create($validated);

        // Création du compte utilisateur associé
        $user = User::create([
            'name'      => "{$validated['nom']} {$validated['prenom']}",
            'email'     => $validated['email'] ?? null,
            'phone'     => $validated['tel'] ?? null,
            'username'  => strtolower($validated['prenom']) . '.' . strtolower($validated['nom']),
            'role'      => 'professeur',
            'matricule' => $matricule,
            'password'  => Hash::make(bin2hex(random_bytes(16))),
            'status' => false,
            'must_change_password' => true,
        ]);
        $enseignant->user_id = $user->id;
        $enseignant->save();

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
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'directeur'], true),
            403,
            'Seuls les administrateurs peuvent activer ou désactiver un enseignant.'
        );

        $enseignant = Enseignant::findOrFail($id);
        $ancienStatut = $enseignant->statut ? 'actif' : 'inactif';
        $enseignant->statut = !$enseignant->statut;
        $enseignant->save();
        $nouveauStatut = $enseignant->statut ? 'actif' : 'inactif';

        $user = User::where('matricule', $enseignant->matricule)->first();
        $temporaryPassword = null;

        if ($user) {
            $user->status = $enseignant->statut;
            if ($enseignant->statut) {
                $temporaryPassword = bin2hex(random_bytes(16));
                $user->password = Hash::make($temporaryPassword);
                $user->must_change_password = true;
                $user->remember_token = Str::random(60);
                $user->session_version = ((int) $user->session_version) + 1;
            }
            $user->save();
        } elseif ($enseignant->statut) {
            $temporaryPassword = bin2hex(random_bytes(16));
            $user = User::create([
                'name' => $enseignant->nom . ' ' . $enseignant->prenom,
                'email' => $enseignant->email,
                'phone' => $enseignant->tel,
                'username' => strtolower($enseignant->prenom) . '.' . strtolower($enseignant->nom),
                'role' => 'professeur',
                'matricule' => $enseignant->matricule,
                'password' => Hash::make($temporaryPassword),
                'status' => true,
                'must_change_password' => true,
                'remember_token' => Str::random(60),
                'session_version' => 1,
            ]);

            $enseignant->user_id = $user->id;
            $enseignant->save();
        }

        logAction('Changement de statut', "Le statut de l’enseignant {$enseignant->nom} {$enseignant->prenom} (Matricule : {$enseignant->matricule}) est passé de {$ancienStatut} à {$nouveauStatut}.");

        $response = redirect()->route('enseignants.index')->with('success', 'Statut de l’enseignant modifié avec succès.');
        if ($temporaryPassword !== null) {
            $response->with('temporary_password', $temporaryPassword);
        }

        return $response;
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
          // 🔹 Année scolaire courante
        $annee_courante = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();
$annee_id = $annee_courante?->id;


        $classe = Classe::with([
            'annee',
            'inscriptions',
            'enseignant',
            'affectations.enseignant',
        ])->findOrFail($classeId);

      // 🔹 Enseignants disponibles pour l'année scolaire courante
$enseignants = Enseignant::where('niveau_id', 1) // Primaire

    // Non affecté cette année
    ->whereNotIn('id', function ($query) use ($annee_id) {
        $query->select('enseignant_id')
            ->from('affectations')
            ->whereNotNull('enseignant_id')
            ->where('annee_id', $annee_id);
    })

    // Non responsable d'une classe cette année
    ->whereNotIn('id', function ($query) use ($annee_id) {
        $query->select('enseignant_id')
            ->from('classes')
            ->whereNotNull('enseignant_id')
            ->where('annee_id', $annee_id);
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

/**
 * Copier les affectations de l'année précédente
 * vers l'année scolaire actuelle.
 */
public function copierAffectationsAnneePrecedente()
{
    $anneeActuelle = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    if (!$anneeActuelle) {
        return back()->with('error', 'Aucune année scolaire actuelle n\'est définie.');
    }

    // 🔹 Recherche de l'année précédente
    $anneePrecedente = AnneesScolaire::where('id', '<', $anneeActuelle->id)
        ->orderByDesc('id')
        ->first();

    if (!$anneePrecedente) {
        return back()->with(
            'error',
            'Aucune année scolaire précédente n\'a été trouvée.'
        );
    }

    $nombreResponsables = 0;
    $nombreAffectations = 0;
    $nombreTitulaires = 0;

    DB::transaction(function () use (
        $anneeActuelle,
        $anneePrecedente,
        &$nombreResponsables,
        &$nombreAffectations,
        &$nombreTitulaires
    ) {

        // 🔹 Toutes les classes de l'année actuelle
        $classesActuelles = Classe::where(
            'annee_id',
            $anneeActuelle->id
        )->get();

        foreach ($classesActuelles as $classeActuelle) {

            /*
            |--------------------------------------------------------------------------
            | Recherche de la classe correspondante dans l'année précédente
            |--------------------------------------------------------------------------
            */

            $classePrecedente = Classe::where(
                'annee_id',
                $anneePrecedente->id
            )
                ->where('nom', $classeActuelle->nom)
                ->where('niveau_id', $classeActuelle->niveau_id)
                ->first();

            // Aucune correspondance
            if (!$classePrecedente) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | 1. PRIMAIRE
            |--------------------------------------------------------------------------
            */

            if ($classeActuelle->niveau_id == 1) {

                // Ne pas écraser un responsable déjà présent
                if (
                    !$classeActuelle->enseignant_id &&
                    $classePrecedente->enseignant_id
                ) {

                    $classeActuelle->update([
                        'enseignant_id' => $classePrecedente->enseignant_id,
                    ]);

                    $nombreResponsables++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. PROFESSEURS / AFFECTATIONS
            |--------------------------------------------------------------------------
            */

            else {

                $affectationsPrecedentes = Affectation::where(
                    'classe_id',
                    $classePrecedente->id
                )
                    ->where('annee_id', $anneePrecedente->id)
                    ->get();

                foreach ($affectationsPrecedentes as $ancienne) {

                    /*
                    |--------------------------------------------------------------------------
                    | Vérifier si cette affectation existe déjà
                    |--------------------------------------------------------------------------
                    */

                    $existe = Affectation::where(
                        'classe_id',
                        $classeActuelle->id
                    )
                        ->where('annee_id', $anneeActuelle->id)
                        ->where('enseignant_id', $ancienne->enseignant_id)
                        ->where('matiere_id', $ancienne->matiere_id)
                        ->exists();

                    if ($existe) {
                        continue;
                    }

                    Affectation::create([
                        'annee_id' => $anneeActuelle->id,
                        'classe_id' => $classeActuelle->id,
                        'enseignant_id' => $ancienne->enseignant_id,
                        'matiere_id' => $ancienne->matiere_id,
                        'heures_attribuees' => $ancienne->heures_attribuees,
                        'note' => $ancienne->note,
                    ]);

                    $nombreAffectations++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. TITULAIRE
            |--------------------------------------------------------------------------
            */

            $titulairePrecedent = Titulaire::where(
                'classe_id',
                $classePrecedente->id
            )
                ->where('annee_id', $anneePrecedente->id)
                ->first();

            if ($titulairePrecedent) {

                $titulaireExiste = Titulaire::where(
                    'classe_id',
                    $classeActuelle->id
                )
                    ->where('annee_id', $anneeActuelle->id)
                    ->exists();

                if (!$titulaireExiste) {

                    Titulaire::create([
                        'annee_id' => $anneeActuelle->id,
                        'classe_id' => $classeActuelle->id,
                        'enseignant_id' => $titulairePrecedent->enseignant_id,
                    ]);

                    $nombreTitulaires++;
                }
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Journalisation
    |--------------------------------------------------------------------------
    */

    logAction(
        'Affectation',
        "Copie des affectations de {$anneePrecedente->nom} vers {$anneeActuelle->nom}. " .
        "{$nombreResponsables} responsable(s), " .
        "{$nombreAffectations} affectation(s), " .
        "{$nombreTitulaires} titulaire(s)."
    );

    return back()->with(
        'success',
        "Copie terminée : {$nombreResponsables} responsable(s), " .
        "{$nombreAffectations} affectation(s) et " .
        "{$nombreTitulaires} titulaire(s) copiés."
    );
}


public function viderAffectationsAnnee()
{
    $anneeActuelle = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    if (!$anneeActuelle) {
        return back()->with('error', 'Aucune année scolaire actuelle n\'est définie.');
    }

    $nombreAffectations = 0;
    $nombreTitulaires = 0;
    $nombreResponsables = 0;

    DB::transaction(function () use (
        $anneeActuelle,
        &$nombreAffectations,
        &$nombreTitulaires,
        &$nombreResponsables
    ) {

        // 1️⃣ Supprimer les affectations de l'année courante
        $nombreAffectations = Affectation::where(
            'annee_id',
            $anneeActuelle->id
        )->count();

        Affectation::where(
            'annee_id',
            $anneeActuelle->id
        )->delete();


        // 2️⃣ Supprimer les titulaires de l'année courante
        $nombreTitulaires = Titulaire::where(
            'annee_id',
            $anneeActuelle->id
        )->count();

        Titulaire::where(
            'annee_id',
            $anneeActuelle->id
        )->delete();


        // 3️⃣ Vider le responsable des classes primaires
        $classes = Classe::where(
            'annee_id',
            $anneeActuelle->id
        )->whereNotNull('enseignant_id')->get();

        $nombreResponsables = $classes->count();

        Classe::where(
            'annee_id',
            $anneeActuelle->id
        )->update([
            'enseignant_id' => null
        ]);
    });


    logAction(
        'Suppression',
        "Toutes les affectations de l'année scolaire {$anneeActuelle->nom} ont été supprimées. " .
        "{$nombreAffectations} affectation(s), " .
        "{$nombreTitulaires} titulaire(s) et " .
        "{$nombreResponsables} responsable(s) supprimés."
    );


    return back()->with(
        'success',
        "Année {$anneeActuelle->nom} vidée avec succès : " .
        "{$nombreAffectations} affectation(s), " .
        "{$nombreTitulaires} titulaire(s) et " .
        "{$nombreResponsables} responsable(s) supprimés."
    );
}
}
