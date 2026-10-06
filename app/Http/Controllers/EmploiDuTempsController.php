<?php

namespace App\Http\Controllers;

use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Enseignant;
use App\Models\HeureCours;
use App\Models\Affectation;
use App\Models\EmploiDuTemps;
use Illuminate\Http\Request;

class EmploiDuTempsController extends Controller
{
    public function index()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $annee_courante = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $classes = Classe::withCount('inscriptions')
            ->with([
                'enseignant',
                'affectations' => function ($query) use ($annee_courante) {
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

        $enseignants = Enseignant::all();
        $annees = $annee_courante->nom;

        logAction('Consultation', "Affichage de la liste des emplois du temps pour l’année scolaire {$annee_courante->nom}.");

        return view('emplois.index', compact('classes', 'enseignants', 'annees','annee_courante'));
    }

    public function show($classeId)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
$annee_courante = session('annee_id')
    ? AnneesScolaire::find(session('annee_id'))
    : AnneesScolaire::where('active', 1)->first();
        $classe = Classe::findOrFail($classeId);
        $heures = HeureCours::orderBy('id')->get();
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $emplois = EmploiDuTemps::where('classe_id', $classeId)->get();
        
$affectations = Affectation::where('classe_id', $classe->id)
    ->where('annee_id', $annee_courante->id)
    ->with(['matiere', 'enseignant'])
    ->get();
        logAction('Consultation', "Affichage de l’emploi du temps pour la classe {$classe->nom}.");

        return view('emplois.show', compact('classe', 'heures', 'jours', 'emplois', 'affectations'));
    }

    public function storeMultiple(Request $request, $classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
$annee_courante = session('annee_id')
    ? AnneesScolaire::find(session('annee_id'))
    : AnneesScolaire::where('active', 1)->first();
        $data = $request->input('emplois', []);
        $changementDetecte = false; // ✅ Indicateur de changement réel

        foreach ($data as $jour => $heures) {
            foreach ($heures as $heure_id => $affectation_id) {

                // Créneau existant dans la base
                $existant = \App\Models\EmploiDuTemps::where('classe_id', $classe_id)
                    ->where('jour', $jour)
                    ->where('heure_cours_id', $heure_id)
                    ->first();

                if (!empty($affectation_id)) {
                    $affectation = \App\Models\Affectation::with(['enseignant', 'matiere'])
                        ->find($affectation_id);

                    if (!$affectation) continue;

                    $enseignant = $affectation->enseignant;
                    $matiere = $affectation->matiere;

                    // 🔹 Vérification du nombre d’heures max
                    $heuresPlanifiees = \App\Models\EmploiDuTemps::where('affectation_id', $affectation->id)->count();
                    if (!$existant && $heuresPlanifiees >= $affectation->heures_attribuees) {
                        return redirect()->back()
                            ->with('error', "❌ L'enseignant {$enseignant->nom} a déjà atteint le nombre d'heures attribuées ({$affectation->heures_attribuees}) pour la matière {$matiere->nom}.");
                    }

                    // 🔹 Conflits
                    $conflitClasse = \App\Models\EmploiDuTemps::where('classe_id', $classe_id)
                        ->where('jour', $jour)
                        ->where('heure_cours_id', $heure_id)
                        ->where('id', '!=', optional($existant)->id)
                        ->exists();

                    if ($conflitClasse) {
                        return redirect()->back()
                            ->with('error', "⚠️ Ce créneau ($jour, heure #$heure_id) est déjà occupé dans cette classe.");
                    }

                    $conflitEnseignant = \App\Models\EmploiDuTemps::whereHas('affectation', function ($q) use ($enseignant) {
                        $q->where('enseignant_id', $enseignant->id);
                    })
                        ->where('jour', $jour)
                        ->where('heure_cours_id', $heure_id)
                        ->where('classe_id', '!=', $classe_id)
                        ->exists();

                    if ($conflitEnseignant) {
                        return redirect()->back()
                            ->with('error', "🚫 Conflit détecté : l'enseignant {$enseignant->nom} a déjà un cours le {$jour} à ce créneau horaire dans une autre classe.");
                    }

                    // ✅ Vérifier si le créneau a vraiment changé
                    if (!$existant || $existant->affectation_id != $affectation_id) {
                        \App\Models\EmploiDuTemps::updateOrCreate(
                            [
                                'classe_id' => $classe_id,
                                'jour' => $jour,
                                'heure_cours_id' => $heure_id,
                            ],
                            [
                                'affectation_id' => $affectation_id,
                            ]
                        );

                        $changementDetecte = true;
                        logAction('Affectation', "Créneau ajouté/modifié : {$jour} - Heure #{$heure_id} - {$matiere->nom} avec {$enseignant->nom} {$enseignant->prenom} pour la classe ID {$classe_id}.");
                    }
                } else {
                    // ✅ Suppression uniquement si un créneau existait avant
                    if ($existant) {
                        \App\Models\EmploiDuTemps::where('id', $existant->id)->delete();
                        $changementDetecte = true;
                        logAction('Suppression', "Créneau supprimé pour la classe ID {$classe_id} : {$jour}, heure #{$heure_id}.");
                    }
                }
            }
        }

        // ✅ Log global uniquement s’il y a eu de vrais changements
        if ($changementDetecte) {
            logAction('Mise à jour', "Mise à jour de l’emploi du temps de la classe ID {$classe_id} avec des modifications réelles détectées.");
        }

        return redirect()->route('emplois.create', $classe_id)
            ->with('success', '✅ Emploi du temps mis à jour avec succès.');
    }


    public function create($classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
$annee_courante = session('annee_id')
    ? AnneesScolaire::find(session('annee_id'))
    : AnneesScolaire::where('active', 1)->first();
        $classe = \App\Models\Classe::findOrFail($classe_id);
        $heures = \App\Models\HeureCours::all();

       $affectations = Affectation::where('classe_id', $classe->id)
    ->where('annee_id', $annee_courante->id)
    ->with(['matiere', 'enseignant'])
    ->get();

        $emploisExistants = \App\Models\EmploiDuTemps::where('classe_id', $classe->id)
            ->with(['heure', 'affectation.matiere', 'affectation.enseignant'])
            ->get()
            ->groupBy(fn($e) => $e->jour . '_' . $e->heure_cours_id);

        $emploisGlobaux = \App\Models\EmploiDuTemps::with(['affectation.enseignant'])
            ->get()
            ->groupBy(fn($e) => $e->jour . '_' . $e->heure_cours_id);

        $enseignantsOccupes = [];
        foreach ($emploisGlobaux as $key => $emplois) {
            $enseignantsOccupes[$key] = $emplois->pluck('affectation.enseignant.id')->unique()->toArray();
        }

        logAction('Consultation', "Ouverture du formulaire de création/modification d’emploi du temps pour la classe {$classe->nom}.");

        return view('emplois.create', compact('classe', 'heures', 'affectations', 'emploisExistants', 'enseignantsOccupes'));
    }

    
public function generateSmart($classe_id)
{
    // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    $annee_courante = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

$classe = \App\Models\Classe::with('niveau')
    ->where('id', $classe_id)
    ->where('annee_id', $annee_courante->id)
    ->firstOrFail();
    
    $jours  = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    $heures = \App\Models\HeureCours::where('libelle', '!=', 'Pause')->get()->values();

    $affectations = Affectation::where('classe_id', $classe->id)
        ->where('annee_id', $annee_courante->id)
        ->with(['matiere', 'enseignant'])
        ->get();

    // 🔹 Nettoyer uniquement l'emploi du temps de l'ANNÉE EN COURS pour cette classe
    // (avant : where('classe_id', ...)->delete() supprimait aussi les autres années)
    \App\Models\EmploiDuTemps::where('classe_id', $classe_id)
        ->whereHas('affectation', function ($q) use ($annee_courante) {
            $q->where('annee_id', $annee_courante->id);
        })
        ->delete();

    // 🔹 Normalisation des noms (pour gérer accents, espaces, tirets)
    $normalize = fn($string) => preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', strtolower(trim($string))));

    // 🔹 Définition des matières
    $matieresLourdes = [
        'Mathématiques',
        'Physique-Chimie-Technologie',
        'Sciences de la Vie et de la Terre',
        'Français',
        'Histoire-Géographie',
        'Éducation Civique et Morale',
    ];

    $matieresFacultatives = [
        'Dessin',
        'Arabe',
        'Anglais',
        'Musique',
        'Latin',
        'Allemand',
        'Espagnol',
        'Informatique',
    ];

    $matiereSport = [
        'Éducation Physique et Sportive',
    ];

    // 🔹 Niveau de la classe
    $niveauNom = $normalize($classe->niveau->nom ?? '');
    $isCollege = str_contains($niveauNom, 'college');
    $isLycee   = str_contains($niveauNom, 'lycee');

    $maxHeuresJourProf = $isCollege ? 2 : 3;
    $maxConsecutives   = $isCollege ? 2 : 3;

    // 🔹 Génération
    foreach ($affectations as $aff) {

        $heuresRestantes = $aff->heures_attribuees;
        $matiereNomNorm  = $normalize($aff->matiere->nom);

        // 🔹 Répartition par blocs selon type de matière (inchangé)
        $repartition = [];
        if (in_array($matiereNomNorm, $matieresFacultatives) && !in_array($matiereNomNorm, $matiereSport)) {
            if ($heuresRestantes == 2) $repartition = [1, 1];
            elseif ($heuresRestantes == 3) $repartition = [2, 1];
            elseif ($heuresRestantes == 4) $repartition = [2, 2];
            elseif ($heuresRestantes == 5) $repartition = [2, 2, 1];
            elseif ($heuresRestantes == 6) $repartition = [2, 2, 2];
            else $repartition = array_fill(0, $heuresRestantes, 1);
        } elseif (in_array($matiereNomNorm, $matieresLourdes)) {
            if ($heuresRestantes % 2 == 0) {
                $repartition = array_fill(0, $heuresRestantes / 2, 2);
            } else {
                $pairs = intdiv($heuresRestantes, 2);
                $repartition = array_fill(0, $pairs, 2);
                $repartition[] = 1;
            }
        } elseif (in_array($matiereNomNorm, $matiereSport)) {
            $repartition = array_fill(0, $heuresRestantes, 1);
        } else {
            $repartition = array_fill(0, $heuresRestantes, 1);
        }

        $repIndex = 0;

        // 🔹 Fonction de placement réutilisable pour une journée donnée,
        //    limitée à un index maximum (inclus) : $indexMax
        //    -> Phase 1 : indexMax = avant-dernière heure (on exclut la 8e heure)
        //    -> Phase 2 (repli) : indexMax = dernière heure (8e heure autorisée)
        $placerDansJour = function ($jour, $indexMax) use (
            &$repIndex, $repartition, $heures, $classe_id, $aff, $matiereNomNorm,
            $isCollege, $classe, $matiereSport, $maxHeuresJourProf,
            $maxConsecutives, $annee_courante, $normalize
        ) {
            $index = 0;

            while ($index <= $indexMax && $repIndex < count($repartition)) {

                $blocSize = $repartition[$repIndex];

                // Le bloc dépasserait la limite autorisée pour cette phase/journée
                if ($index + $blocSize - 1 > $indexMax || $index + $blocSize > $heures->count()) {
                    break;
                }

                $slotsPourBloc = [];
                $ok = true;

                for ($k = 0; $k < $blocSize; $k++) {
                    $pos   = $index + $k;
                    $heure = $heures[$pos];

                    // 🔹 1. Conflit classe
                    if (\App\Models\EmploiDuTemps::where([
                        'classe_id' => $classe_id,
                        'jour' => $jour,
                        'heure_cours_id' => $heure->id,
                    ])->exists()) {
                        $ok = false;
                        break;
                    }

                    // 🔹 2. Conflit enseignant (année en cours uniquement)
                    if (\App\Models\EmploiDuTemps::whereHas('affectation', function ($q) use ($aff, $annee_courante) {
                        $q->where('enseignant_id', $aff->enseignant_id)
                          ->where('annee_id', $annee_courante->id);
                    })->where('jour', $jour)->where('heure_cours_id', $heure->id)->exists()) {
                        $ok = false;
                        break;
                    }

                    // 🔹 4. EPS jamais après une pause
                    if ($pos > 0 && strtolower($heures[$pos - 1]->libelle) == 'pause' && in_array($matiereNomNorm, $matiereSport)) {
                        $ok = false;
                        break;
                    }

                    // 🔹 7. Éviter la 8ᵉ heure pour collège 5ᵉ/6ᵉ (garde-fou même en phase de repli)
                    if ($pos == 7 && $isCollege && in_array($classe->niveau_id, [5, 6])) {
                        $ok = false;
                        break;
                    }

                    $slotsPourBloc[] = $heure;
                }

                if (!$ok) {
                    $index++;
                    continue;
                }

                // 🔹 3. Max heures/jour prof (année en cours uniquement)
                $heuresJourProf = \App\Models\EmploiDuTemps::whereHas('affectation', function ($q) use ($aff, $annee_courante) {
                    $q->where('enseignant_id', $aff->enseignant_id)
                      ->where('annee_id', $annee_courante->id);
                })->where('jour', $jour)->count();

                if ($heuresJourProf + $blocSize > $maxHeuresJourProf) {
                    $index++;
                    continue;
                }

                // 🔹 8. Pas plus de maxConsecutives heures consécutives de la même matière
                $planningJour = \App\Models\EmploiDuTemps::where('classe_id', $classe_id)
                    ->where('jour', $jour)
                    ->with('affectation.matiere')
                    ->get();

                $consec = 0;
                for ($i = $planningJour->count() - 1; $i >= 0; $i--) {
                    if ($planningJour[$i]->affectation && $normalize($planningJour[$i]->affectation->matiere->nom) == $matiereNomNorm) {
                        $consec++;
                    } else {
                        break;
                    }
                }
                if ($consec + $blocSize > $maxConsecutives) {
                    $index++;
                    continue;
                }

                // 🔹 9. Placer le bloc en entier (toutes les heures du bloc)
                foreach ($slotsPourBloc as $heure) {
                    \App\Models\EmploiDuTemps::create([
                        'classe_id' => $classe_id,
                        'jour' => $jour,
                        'heure_cours_id' => $heure->id,
                        'affectation_id' => $aff->id,
                    ]);
                }

                $repIndex++;
                $index += $blocSize;
            }
        };

        $derniereHeureIndex = $heures->count() - 1; // ex: index 7 pour une 8e heure

        // 🔹 Phase 1 : on parcourt les 5 jours en n'utilisant QUE les heures 1 à 7
        foreach ($jours as $jour) {
            if ($repIndex >= count($repartition)) break;
            $placerDansJour($jour, $derniereHeureIndex - 1);
        }

        // 🔹 Phase 2 (repli) : s'il reste des blocs non placés, on autorise la 8e heure
        if ($repIndex < count($repartition)) {
            foreach ($jours as $jour) {
                if ($repIndex >= count($repartition)) break;
                $placerDansJour($jour, $derniereHeureIndex);
            }
        }
    }

    return redirect()->back()->with('success', 'Emploi du temps généré avec succès.');
}


    public function reset($classeId)
{
    // Vérification connexion
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    // Récupération de la classe
    $classe = Classe::findOrFail($classeId);

    // Année scolaire active
    $anneeActive = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    if (!$anneeActive) {
        return back()->with('error', 'Aucune année scolaire active.');
    }

    // Suppression uniquement des emplois
    // de cette classe et de cette année scolaire
    EmploiDuTemps::where('classe_id', $classe->id)
        ->delete();

    return redirect()
        ->route('emplois.create', $classe->id)
        ->with(
            'success',
            "L'emploi du temps de la classe {$classe->nom} a été réinitialisé avec succès."
        );
}



    public function print($classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $classe = \App\Models\Classe::findOrFail($classe_id);
        $heures = \App\Models\HeureCours::all();

        $emploisExistants = \App\Models\EmploiDuTemps::where('classe_id', $classe->id)
            ->with(['heure', 'affectation.matiere', 'affectation.enseignant'])
            ->get();

        logAction('Impression', "Préparation à l’impression de l’emploi du temps pour la classe {$classe->nom}.");

        return view('emplois.print', compact('classe', 'heures', 'emploisExistants'));
    }

    public function monEmploi()
{
    // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    // 2️⃣ Vérifie que l'utilisateur est professeur
    $user = auth()->user();

    if ($user->role !== 'professeur') {
        abort(403, 'Accès non autorisé');
    }

    // 3️⃣ Récupère l'année scolaire sélectionnée ou l'année active
    $anneeActive = session('annee_id')
        ? AnneesScolaire::find(session('annee_id'))
        : AnneesScolaire::where('active', 1)->first();

    if (!$anneeActive) {
        return redirect()->back()
            ->with('error', 'Aucune année scolaire active.');
    }

    // 4️⃣ Récupère l'enseignant lié au compte connecté
    $enseignant = Enseignant::where(
        'matricule',
        $user->matricule
    )->first();

    if (!$enseignant) {
        return redirect()->back()
            ->with('error', 'Aucun enseignant associé à votre compte.');
    }

    // 5️⃣ Récupère uniquement les affectations
    //    de cet enseignant pour l'année scolaire sélectionnée
    $affectations = Affectation::where('enseignant_id', $enseignant->id)
        ->where('annee_id', $anneeActive->id)
        ->pluck('id');

    if ($affectations->isEmpty()) {
        return redirect()->back()
            ->with('error', 'Aucune affectation trouvée pour cet enseignant dans cette année scolaire.');
    }

    // 6️⃣ Récupère l'emploi du temps correspondant
    $emplois = EmploiDuTemps::with([
            'classe',
            'matiere',
            'heure'
        ])
        ->whereIn('affectation_id', $affectations)
        ->orderBy('jour')
        ->get();

    // 7️⃣ Informations de l'école
    $ecole = Ecole::first();

    // 8️⃣ Journalisation
    logAction(
        'Consultation',
        "L’enseignant {$enseignant->nom} {$enseignant->prenom} " .
        "a consulté son emploi du temps personnel " .
        "(année scolaire : {$anneeActive->nom})."
    );

    // 9️⃣ Retour vers la vue
    return view(
        'emplois.mon_emploi',
        compact(
            'emplois',
            'enseignant',
            'ecole',
            'anneeActive'
        )
    );
}
}
