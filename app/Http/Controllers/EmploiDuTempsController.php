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

        $classe = Classe::findOrFail($classeId);
        $heures = HeureCours::orderBy('id')->get();
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $emplois = EmploiDuTemps::where('classe_id', $classeId)->get();
        $affectations = Affectation::where('classe_id', $classeId)->with('professeur', 'matiere')->get();

        logAction('Consultation', "Affichage de l’emploi du temps pour la classe {$classe->nom}.");

        return view('emplois.show', compact('classe', 'heures', 'jours', 'emplois', 'affectations'));
    }

    public function storeMultiple(Request $request, $classe_id)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

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

        $classe = \App\Models\Classe::findOrFail($classe_id);
        $heures = \App\Models\HeureCours::all();

        $affectations = \App\Models\Affectation::where('classe_id', $classe->id)
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

        $classe = \App\Models\Classe::with('niveau')->findOrFail($classe_id);
        $jours  = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
        $heures = \App\Models\HeureCours::where('libelle', '!=', 'Pause')->get();

        $affectations = \App\Models\Affectation::where('classe_id', $classe_id)
            ->with(['enseignant', 'matiere'])
            ->get();

        // 🔹 Nettoyer ancien emploi du temps
        \App\Models\EmploiDuTemps::where('classe_id', $classe_id)->delete();

        // 🔹 Normalisation des noms (pour gérer accents, espaces, tirets)
        $normalize = fn($string) => preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', strtolower(trim($string))));

        // 🔹 Définition des matières
        // Matières lourdes (nom ou sigle)
        $matieresLourdes = [
            'Mathématiques',                  // Collège
            'Physique-Chimie-Technologie',    // Collège
            'Sciences de la Vie et de la Terre', // Collège
            'Français',                       // Collège
            'Histoire-Géographie',            // Collège
            'Éducation Civique et Morale'     // Collège / Lycée
        ];

// Matières facultatives (nom ou sigle)
        $matieresFacultatives = [
            'Dessin',    // Lycée
            'Arabe',     // Collège
            'Anglais',   // Collège
            'Musique',
            'Latin',
            'Allemand',
            'Espagnol',
            'Informatique'
        ];

// Matières sport
        $matiereSport = [
            'Éducation Physique et Sportive'  // EPS Lycée
        ];

        // 🔹 Niveau de la classe
        $niveauNom = $normalize($classe->niveau->nom ?? '');
        $isCollege = str_contains($niveauNom, 'college');
        $isLycee   = str_contains($niveauNom, 'lycee');

        $maxHeuresJourProf = $isCollege ? 2 : 3;
        $maxConsecutives  = $isCollege ? 2 : 3;
       // dd($affectations);
        // 🔹 Génération
        foreach ($affectations as $aff) {

            $heuresRestantes = $aff->heures_attribuees;
            $matiereNomNorm = $normalize($aff->matiere->nom);
            echo 'norm :'. $matiereNomNorm;
            // 🔹 Répartition par blocs selon type de matière
            $repartition = [];
            if (in_array($matiereNomNorm, $matieresFacultatives) && !in_array($matiereNomNorm, $matiereSport)) {
                // facultatives max 2h consécutives
                if ($heuresRestantes == 2) $repartition = [1,1];
                elseif ($heuresRestantes == 3) $repartition = [2,1];
                elseif ($heuresRestantes == 4) $repartition = [2,2];
                elseif ($heuresRestantes == 5) $repartition = [2,2,1];
                elseif ($heuresRestantes == 6) $repartition = [2,2,2];
                else $repartition = array_fill(0,$heuresRestantes,1);
echo 'facult :'. $aff->matiere->nom;
            } elseif (in_array($matiereNomNorm, $matieresLourdes)) {
                // lourdes : minimum 2h consécutives, 1h seule seulement si impaire
                if ($heuresRestantes % 2 == 0) {
                    $repartition = array_fill(0, $heuresRestantes / 2, 2);

                    echo 'lourd :'. $aff->matiere->nom;
                } else {
                    $pairs = intdiv($heuresRestantes,2);
                    $repartition = array_fill(0, $pairs, 2);
                    $repartition[] = 1;
                    echo 'autre :'. $aff->matiere->nom;
                }
            } elseif (in_array($matiereNomNorm, $matiereSport)) {
                $repartition = array_fill(0,$heuresRestantes,1);
            } else {
                $repartition = array_fill(0,$heuresRestantes,1);
            }
//dd('fin');
            $repIndex = 0;

            foreach ($jours as $jour) {
                foreach ($heures as $index => $heure) {
                    if ($repIndex >= count($repartition)) break;

                    $toPlace = $repartition[$repIndex];

                    // 🔹 1. Conflit classe
                    if (\App\Models\EmploiDuTemps::where([
                        'classe_id'=>$classe_id,
                        'jour'=>$jour,
                        'heure_cours_id'=>$heure->id
                    ])->exists()) continue;

                    // 🔹 2. Conflit enseignant
                    if (\App\Models\EmploiDuTemps::whereHas('affectation', function($q) use($aff){
                        $q->where('enseignant_id',$aff->enseignant_id);
                    })->where('jour',$jour)->where('heure_cours_id',$heure->id)->exists()) continue;

                    // 🔹 3. Max heures/jour prof
                    $heuresJourProf = \App\Models\EmploiDuTemps::whereHas('affectation', function($q) use($aff){
                        $q->where('enseignant_id',$aff->enseignant_id);
                    })->where('jour',$jour)->count();
                    if ($heuresJourProf >= $maxHeuresJourProf) continue;

                    // 🔹 4. EPS jamais après pause
                    if ($index > 0 && strtolower($heures[$index-1]->libelle)=='pause' && in_array($matiereNomNorm,$matiereSport)) continue;

                    // 🔹 5. Pas d’1h seule pour matière lourde si pair
                    if (in_array($matiereNomNorm,$matieresLourdes) && $toPlace==1 && $heuresRestantes % 2 == 0) continue;

                    // 🔹 6. Pas de 2h entre 5ᵉ et 6ᵉ heure
                    if ($index==5) { // 6ᵉ heure
                        $precedent = \App\Models\EmploiDuTemps::where('classe_id',$classe_id)
                            ->where('jour',$jour)
                            ->where('heure_cours_id',$heures[$index-1]->id)
                            ->with('affectation.matiere')
                            ->first();
                        if ($precedent && $normalize($precedent->affectation->matiere->nom)==$matiereNomNorm) continue;
                    }

                    // 🔹 7. Éviter 8ᵉ heure pour collège 5ᵉ/6ᵉ
                    if ($index==7 && $isCollege && in_array($classe->niveau_id,[5,6])) continue;

                    // 🔹 8. Pas plus de maxConsecutives heures consécutives
                    $planningJour = \App\Models\EmploiDuTemps::where('classe_id',$classe_id)
                        ->where('jour',$jour)
                        ->with('affectation.matiere')->get();
                    $consec = 0;
                    for($i=$planningJour->count()-1;$i>=0;$i--){
                        if ($planningJour[$i]->affectation && $normalize($planningJour[$i]->affectation->matiere->nom)==$matiereNomNorm) $consec++;
                        else break;
                    }
                    if ($consec >= $maxConsecutives) continue;

                    // 🔹 9. Placer la matière
                    \App\Models\EmploiDuTemps::create([
                        'classe_id'=>$classe_id,
                        'jour'=>$jour,
                        'heure_cours_id'=>$heure->id,
                        'affectation_id'=>$aff->id
                    ]);

                    $repIndex++;
                    $heuresRestantes -= $toPlace;
                }
                if ($repIndex >= count($repartition)) break;
            }
        }

        logAction('Génération intelligente', "Emploi du temps généré pour {$classe->nom} avec toutes les règles.");

        return redirect()->route('emplois.create',$classe_id)
            ->with('success','✅ Emploi du temps généré intelligemment avec toutes les règles.');
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
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = auth()->user();

        if ($user->role !== 'professeur') {
            abort(403, 'Accès non autorisé');
        }

        $enseignant = Enseignant::where('matricule', $user->matricule)->first();

        if (!$enseignant) {
            return redirect()->back()->with('error', 'Aucun enseignant associé à votre compte.');
        }

        $affectations = Affectation::where('enseignant_id', $enseignant->id)->pluck('id');

        if ($affectations->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune affectation trouvée pour cet enseignant.');
        }

        $emplois = EmploiDuTemps::with(['classe', 'matiere', 'heure'])
            ->whereIn('affectation_id', $affectations)
            ->orderBy('jour')
            ->get();

        $ecole = Ecole::first();

        logAction('Consultation', "L’enseignant {$enseignant->nom} {$enseignant->prenom} a consulté son emploi du temps personnel.");

        return view('emplois.mon_emploi', compact('emplois', 'enseignant', 'ecole'));
    }
}
