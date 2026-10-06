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


            if (auth()->user()->role === 'parent') {
    return $this->parentDashboard($anneeActive);
}

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

                    $nouveaux = Inscription::where('classe_id', $classe->id)
                        ->where('annee_id', $anneeId)
                        ->where('type_inscription', 'Nouveau')
                        ->count();

                    // Totaux pour la classe (nombre d’élèves * montant unitaire)
                    $previsionClasseScolarite = $classe->effectif * $montantScolarite;
                    $previsionClasseInscription = $nouveaux * $montantInscription;
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
       $totalClasses = $anneeId ? Classe::where('annee_id', $anneeId)->count() : 0;

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

           $classesPourFrais = Classe::where('annee_id', $anneeId)->get();

    foreach ($classesPourFrais as $classe) {

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
            'totalEleves','garcons','filles','totalClasses','professeurs','nouveaux','anneeActive','inscrits','nonInscrits','totalPayeScolarite',
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
   private function parentDashboard($anneeActive)
{
    $user    = auth()->user();
    $anneeId = $anneeActive?->id;

    // Enfants du parent + inscription de l'année active (avec classe et niveau)
    $enfants = $user->enfants()
        ->with(['inscriptions' => function ($q) use ($anneeId) {
            $q->where('annee_id', $anneeId)->with('classe.niveau');
        }])
        ->get();

    $enfantsData = $enfants->map(function ($eleve) use ($anneeId) {

        $inscription = $eleve->inscriptions->first();
        $classe      = $inscription?->classe;

        // Montant prévu = somme des frais définis pour la classe cette année
        $prevu = ($classe && $anneeId)
            ? (float) \App\Models\MontantFrais::with('frais')
                ->where('classe_id', $classe->id)
                ->where('annee_id', $anneeId)
                ->get()
                ->reject(fn($montant) => $inscription?->isReinscrit() && $montant->frais?->libelle === "Frais d'Inscription")
                ->sum('montant')
            : 0;

        // Montant payé pour cet élève cette année
        // ⚠️ suppose la colonne paiements.eleve_id — adapte si besoin
        $paye = $anneeId
            ? (float) \App\Models\Paiement::where('eleve_id', $eleve->id)
                ->where('annee_id', $anneeId)
                ->sum('montant_paye')
            : 0;

        return [
            'id'       => $eleve->id,
            'nom'      => $eleve->nom,
            'prenom'   => $eleve->prenom,
            'classe'   => $classe?->nom,
            'niveau'   => $classe?->niveau?->nom,
            'inscrit'  => (bool) $inscription,
            'prevu'    => $prevu,
            'paye'     => $paye,
            'reste'    => max(0, $prevu - $paye),
            // null = frais non définis (évite d'afficher 0 % trompeur)
            'taux'     => $prevu > 0 ? min(100, (int) round(($paye / $prevu) * 100)) : null,
            'absences' => $this->countAbsencesEleve($eleve->id),
        ];
    });

    $totalPrevu = $enfantsData->sum('prevu');
    $totalPaye  = $enfantsData->sum('paye');
    $totalReste = $enfantsData->sum('reste');
    $tauxGlobal = $totalPrevu > 0 ? min(100, (int) round(($totalPaye / $totalPrevu) * 100)) : null;

    // Notifications récentes non lues
    $notificationsRecentes = \App\Models\Notification::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
        ->where('is_read', 0)
        ->latest()
        ->take(4)
        ->get();

    return view('dashboard', [
        'anneeActive'           => $anneeActive,
        'enfantsData'           => $enfantsData,
        'totalPrevu'            => $totalPrevu,
        'totalPaye'             => $totalPaye,
        'totalReste'            => $totalReste,
        'tauxGlobal'            => $tauxGlobal,
        'messagesNonLus'        => $this->countMessagesNonLus($user->id),
        'notificationsRecentes' => $notificationsRecentes,
    ]);
}

/**
 * Nombre de conversations contenant au moins un message non lu.
 * ⚠️ Tables supposées : conversation_participants / messages (comme dans la navbar).
 */
private function countMessagesNonLus(int $userId): int
{
    try {
        return \Illuminate\Support\Facades\DB::table('conversation_participants as cp')
            ->where('cp.user_id', $userId)
            ->whereExists(function ($q) use ($userId) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('messages as m')
                  ->whereColumn('m.conversation_id', 'cp.conversation_id')
                  ->where('m.sender_id', '!=', $userId)
                  ->where(function ($w) {
                      $w->whereNull('cp.last_read_at')
                        ->orWhereColumn('m.created_at', '>', 'cp.last_read_at');
                  });
            })
            ->count();
    } catch (\Throwable $e) {
        return 0;
    }
}

/**
 * Nombre d'absences d'un élève.
 * ⚠️ Suppose un modèle App\Models\Absence avec la colonne eleve_id.
 *    Ajoute ici un filtre sur l'année scolaire / la période si ta table le permet.
 */
private function countAbsencesEleve(int $eleveId): int
{
    try {
        if (!class_exists(\App\Models\Absence::class)) {
            return 0;
        }

        return \App\Models\Absence::where('eleve_id', $eleveId)->count();
    } catch (\Throwable $e) {
        return 0;
    }
}
}
