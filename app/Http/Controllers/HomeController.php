<?php


namespace App\Http\Controllers;


use App\Models\Affectation;
use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Inscription;
use App\Models\MontantFrais;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // Récupérer l'année scolaire active : session ou active par défaut
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $anneeId = $anneeActive ? $anneeActive->id : null;

        // 🔸 Initialisation
        $totalPayeScolarite = $totalPayeInscription = 0;
        $previsionScolarite = $previsionInscription = 0;
        $statistiquesClasses = collect();
        $totalSouscriptions = 0;
        $souscriptionsParService = [];

        if ($anneeId) {
            // Élèves inscrits pour l'année active
            $inscrits = Inscription::where('annee_id', $anneeId)->count();

            // Élèves non inscrits pour l'année active
            $nonInscrits = Eleve::whereDoesntHave('inscriptions', function($q) use ($anneeId) {
                $q->where('annee_id', $anneeId);
            })->count();

            // ------------------------------
            // 🎓 1. Montants payés (réels)
            // ------------------------------
            $totalPayeScolarite = \App\Models\Paiement::whereHas('frais', fn($q) =>
            $q->where('libelle', 'Scolarité'))
                ->where('annee_id', $anneeId)
                ->sum('montant_paye');

            $totalPayeInscription = \App\Models\Paiement::whereHas('frais', fn($q) =>
            $q->where('libelle', 'Frais d\'Inscription'))
                ->where('annee_id', $anneeId)
                ->sum('montant_paye');


            // --------------------------------
            // 📊 2. Montants prévus (par classe)
            // --------------------------------
            $statistiquesClasses = \App\Models\Classe::where('annee_id', $anneeId)
                ->withCount(['inscriptions as effectif'])
                ->get()
                ->map(function($classe) use ($anneeId) {

                    // Montants unitaires prévus pour la classe
                    $montantScolarite = \App\Models\MontantFrais::whereHas('frais', fn($q) =>
                    $q->where('description', 'scolarite'))
                        ->where('classe_id', $classe->id)
                        ->where('annee_id', $anneeId)
                        ->value('montant') ?? 0;

                    $montantInscription = \App\Models\MontantFrais::whereHas('frais', fn($q) =>
                    $q->where('description', 'inscription'))
                        ->where('classe_id', $classe->id)
                        ->where('annee_id', $anneeId)
                        ->value('montant') ?? 0;

                    // Totaux pour la classe (nombre d’élèves * montant unitaire)
                    $previsionClasseScolarite = $classe->effectif * $montantScolarite;
                    $previsionClasseInscription = $classe->effectif * $montantInscription;
//dd($montantInscription);
                    return [
                        'classe' => $classe->nom,
                        'effectif' => $classe->effectif,
                        'scolarite' => $previsionClasseScolarite,
                        'inscription' => $previsionClasseInscription,
                        'total' => $previsionClasseScolarite + $previsionClasseInscription,
                    ];
                });

            $previsionScolarite = $statistiquesClasses->sum('scolarite');
            $previsionInscription = $statistiquesClasses->sum('inscription');
           // dd($previsionScolarite);

            // ------------------------------
            // 🧾 3. Souscriptions (services)
            // ------------------------------
            if (class_exists(\App\Models\Souscription::class)) {
                $souscriptions = \App\Models\Souscription::with('service')->get();

                $totalSouscriptions = $souscriptions->count();

                $souscriptionsParService = $souscriptions
                    ->groupBy('service.nom')
                    ->map(fn($items) => $items->count());
            }

        } else {
            $inscrits = 0;
            $nonInscrits = 0;
        }

        // Comptes filtrés par année scolaire active via inscriptions
        $totalEleves = $anneeId ? Inscription::where('annee_id', $anneeId)->count() : 0;
        $garcons     = $anneeId ? Inscription::where('annee_id', $anneeId)
            ->whereHas('eleve', fn($q) => $q->where('sexe', 'M'))
            ->count() : 0;
        $filles      = $anneeId ? Inscription::where('annee_id', $anneeId)
            ->whereHas('eleve', fn($q) => $q->where('sexe', 'F'))
            ->count() : 0;

        // Statistiques globales
        $classes = $anneeId
            ? Classe::where('annee_id', $anneeId)-> count() : 0;

        $professeurs = Enseignant::count();

        $nouveaux    = $anneeId ? Inscription::where('annee_id', $anneeId)
            ->whereHas('eleve', fn($q) =>
            $q->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
            )
            ->count() : 0;
        $totalPrevu = $previsionScolarite + $previsionInscription;
        $totalPaye = $totalPayeScolarite + $totalPayeInscription;
        $tauxPaiement = $totalPrevu > 0 ? round(($totalPaye / $totalPrevu) * 100, 2) : 0;

        $classeTop = $statistiquesClasses->sortByDesc('total')->first();

        function countParNiveau($anneeId, array $niveaux)
        {
            if (!$anneeId) return 0;

            // Nettoyage PHP des valeurs recherchées
            $niveauxNormalises = array_map(function ($niveau) {
                return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $niveau));
            }, $niveaux);

            return Inscription::where('annee_id', $anneeId)
                ->whereHas('classe.niveau', function ($q) use ($niveauxNormalises) {
                    $q->where(function ($sub) use ($niveauxNormalises) {
                        foreach ($niveauxNormalises as $niveau) {
                            // SQL : LOWER + suppression d'accents via collation unicode
                            $sub->orWhereRaw("
                        LOWER(
                            CONVERT(nom USING utf8mb4)
                            COLLATE utf8mb4_general_ci
                        ) LIKE ?
                    ", ['%' . $niveau . '%']);
                        }
                    });
                })
                ->count();
        }


        $inscritsPrimaire = countParNiveau($anneeId, ['Primaire']);
        $inscritsCollege  = countParNiveau($anneeId, ['Collège', 'College']);
        $inscritsLycee  = countParNiveau($anneeId, ['Lycee', 'Lycee']);


// -------------------------------
// 🔔 Vérification des montants manquants
// -------------------------------
        $totalclassesyear = null;
        if ($anneeId) {

            $classes = Classe::where('annee_id', $anneeId)->get();

            foreach ($classes as $classe) {

                // Vérifie la présence des deux types de frais avec la bonne casse
                $fraisScolarite = MontantFrais::where('classe_id', $classe->id)
                    ->where('annee_id', $anneeId)
                    ->whereHas('frais', fn($q) => $q->where('description', 'Scolarité'))
                    ->exists();

                $fraisInscription = MontantFrais::where('classe_id', $classe->id)
                    ->where('annee_id', $anneeId)
                    ->whereHas('frais', fn($q) => $q->where('description', 'Frais d\'Inscription'))
                    ->exists();

                $titre = "Frais non définis pour {$classe->nom}";
                $message = "Veuillez définir les montants de scolarité et d'inscription pour la classe {$classe->nom}.";
                $url = route('montants_frais.index');

                // Si l’un des frais est manquant
                if (!$fraisScolarite || !$fraisInscription) {

                    // Vérifie si la notification existe déjà
                    $notificationsExistantes = \App\Models\Notification::where('titre', $titre)
                        ->where('type', 'Alerte frais')
                        ->get();

                    if ($notificationsExistantes->count() > 0) {
                        // Réactive les notifications déjà lues
                        foreach ($notificationsExistantes as $notif) {
                            if ($notif->is_read == 1) {
                                $notif->update(['is_read' => 0]);
                            }
                        }
                    } else {
                        // Crée une nouvelle notification pour les destinataires
                        $destinataires = User::whereIn('role', ['directeur', 'comptable', 'admin'])->get();

                        foreach ($destinataires as $user) {
                            envoyerNotification($titre, $message, 'Alerte frais', $user->id, $url);
                        }
                    }

                } else {
                    // ✅ Tous les frais sont définis → supprimer les anciennes notifications
                    \App\Models\Notification::where('titre', $titre)
                        ->where('type', 'Alerte frais')
                        ->delete();
                }
            }
        }


        $matricule = auth()->user()->matricule;

// Vérifier si l'utilisateur est un enseignant
        $enseignant = Enseignant::where('matricule', $matricule)->first();

        if ($enseignant) {
            $affectations = Affectation::with(['classe', 'matiere'])
                ->withCount('emploisDuTemps')
                ->where('enseignant_id', $enseignant->id)
                ->get();

            // ✅ Compter le nombre de matières et classes distinctes
            $nombreMatieres = $affectations->pluck('matiere.id')->unique()->count();
            $nombreClasses = $affectations->pluck('classe.id')->unique()->count();
        } else {
            // Si ce n’est pas un enseignant (ex : admin, comptable, etc.)
            $affectations = collect(); // liste vide
            $nombreMatieres = 0;
            $nombreClasses = 0;
        }


        return view('dashboard', compact(
            'totalEleves','garcons','filles','classes','professeurs','nouveaux','anneeActive','inscrits','nonInscrits','totalPayeScolarite',
            'totalPayeInscription',
            'previsionScolarite',
            'previsionInscription',
            'statistiquesClasses',
            'totalSouscriptions',
            'souscriptionsParService',
            'tauxPaiement',
            'classeTop',
            'inscritsCollege',
            'inscritsPrimaire',
            'nombreMatieres',
            'nombreClasses',
            'totalclassesyear',
            'inscritsLycee',
        ));
    }
}
