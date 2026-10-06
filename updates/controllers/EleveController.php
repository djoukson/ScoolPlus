<?php

namespace App\Http\Controllers;

use App\Models\AnneesScolaire;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Inscription;
use Illuminate\Http\Request;

class EleveController extends Controller
{
    public function index(Request $request)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }

        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $anneeId = $anneeActive ? $anneeActive->id : null;

        $eleves = Eleve::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                        ->orWhere('matricule', 'like', "%{$search}%");
                });
            })
            ->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))
            ->paginate(100)
            ->appends($request->only(['search','sort','direction']));

        $classes = $anneeId
            ? Classe::where('annee_id', $anneeId)->get()
            : collect();

        logAction('Consultation', "Liste des élèves affichée (année scolaire : {$anneeActive?->nom}, recherche : {$request->search}).");

        return view('eleves.index', compact('eleves', 'classes', 'anneeActive'));
    }

    public function elevesparannee(Request $request)
    {
        // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
        // 2️⃣ Vérifie si l'utilisateur connecté est admin ou directeur
        if (!in_array(auth()->user()->role, ['admin', 'directeur','secretaire'])) {
            return redirect()->back()->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
        }
        // 📌 Année scolaire active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $anneeId = $anneeActive ? $anneeActive->id : null;

        $search = $request->input('search');

        // ✅ Requête principale
        $eleves = Eleve::select('eleves.*', 'classes.nom as classe_nom','inscriptions.status_eleve as status_eleve')
            ->join('inscriptions', 'inscriptions.eleve_id', '=', 'eleves.id')
            ->join('classes', 'inscriptions.classe_id', '=', 'classes.id')
            ->with(['attributions.bourse'])
            ->where('inscriptions.annee_id', $anneeId)

            // ✅ FILTRE DE RECHERCHE
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('eleves.nom', 'LIKE', "%{$search}%")
                        ->orWhere('eleves.prenom', 'LIKE', "%{$search}%")
                        ->orWhere('eleves.matricule', 'LIKE', "%{$search}%");
                });
            });

        // ✅ Pagination
        $eleves = $eleves->paginate(100)->withQueryString();

        // ✅ Classes de l'année
        $classes = $anneeId
            ? Classe::where('annee_id', $anneeId)->get()
            : collect();

        logAction(
            'Consultation',
            "Affichage des élèves inscrits pour l’année : {$anneeActive?->nom}."
        );

        return view(
            'eleves.ajouteleveparannee',
            compact('eleves', 'classes', 'anneeActive')
        );
    }



    public function store(Request $request)
    {
        $lastId = Eleve::max('id') + 1;
        $matricule = 'ELV' . date('Y') . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        $anneeId = session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id');

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance'=> 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'adresse' => 'nullable|string',
            'tuteur_nom' => 'nullable|string|max:100',
            'nationalite' => 'nullable|string|max:100',
            'observation' => 'nullable|string|max:255',
            'tuteur_tel' => 'nullable|string|max:30',
            'classe_id' => 'nullable|exists:classes,id',
            'lieudenaissance' => 'nullable|max:100',
            'taille_eleve' => 'nullable|string|max:10',
            'pointure_chaussure' => 'nullable|string|max:5',
            'taille_habit' => 'nullable|string|max:10',
        ]);

        $eleve = Eleve::create(array_merge($validated, [
            'matricule' => $matricule,
        ]));

        logAction('Ajout', "Nouvel élève ajouté : {$eleve->nom} {$eleve->prenom} (matricule : {$eleve->matricule}).");

        if (!empty($validated['classe_id'])) {
            $classe = Classe::find($validated['classe_id']);
            Inscription::create([
                'eleve_id' => $eleve->id,
                'classe_id' => $classe->id,
                'annee_id' => $anneeId,
            ]);
            logAction('Inscription', "Élève {$eleve->nom} {$eleve->prenom} inscrit dans la classe {$classe->nom} pour l’année {$anneeId}.");
        }

        return redirect()->route('eleves.index')->with('success', 'Élève ajouté avec succès.');
    }

    public function storeeleveinscription(Request $request)
    {
        $lastId = Eleve::max('id') + 1;
        $matricule = 'ELV' . date('Y') . str_pad($lastId, 2, '0', STR_PAD_LEFT);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'adresse' => 'nullable|string',
            'tuteur_nom' => 'nullable|string|max:100',
            'tuteur_tel' => 'nullable|string|max:30',
            'nationalite' => 'nullable|string|max:100',
            'observation' => 'nullable|string|max:255',
            'classe_id' => 'required|exists:classes,id',
            'annee_id' => 'required|exists:annees_scolaires,id',
            'lieudenaissance' => 'nullable|max:100',
            'taille_eleve' => 'nullable|string|max:10',
            'pointure_chaussure' => 'nullable|string|max:5',
            'taille_habit' => 'nullable|string|max:10',
        ]);

        $eleve = Eleve::create([
            'matricule' => $matricule,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'date_naissance' => $validated['date_naissance'] ?? null,
            'sexe' => $validated['sexe'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'tuteur_nom' => $validated['tuteur_nom'] ?? null,
            'tuteur_tel' => $validated['tuteur_tel'] ?? null,
            'lieudenaissance' => $validated['lieudenaissance'] ?? null,
            'nationalite' => $validated['nationalite'] ?? null,
            'observation' => $validated['observation'] ?? null,
            // ✅ Nouveaux champs mensurations
            'taille_eleve' => $validated['taille_eleve'] ?? null,
            'pointure_chaussure' => $validated['pointure_chaussure'] ?? null,
            'taille_habit' => $validated['taille_habit'] ?? null,
        ]);

        Inscription::create([
            'eleve_id' => $eleve->id,
            'classe_id' => $validated['classe_id'],
            'annee_id' => $validated['annee_id'],
            'date_inscription' => now(),
        ]);

        logAction('Ajout + Inscription', "Nouvel élève {$eleve->nom} {$eleve->prenom} ajouté et inscrit dans la classe ID {$validated['classe_id']} pour l’année ID {$validated['annee_id']}.");

        return back()->with('success', 'Élève inscrit avec succès.');
    }

    public function update(Request $request, $id)
    {
        $eleve = Eleve::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'adresse' => 'nullable|string|max:255',
            'tuteur_nom' => 'nullable|string|max:255',
            'tuteur_tel' => 'nullable|string|max:20',
            'classe_id' => 'nullable|exists:classes,id',
            'observation' => 'nullable|string|max:255',
            'taille_eleve' => 'nullable|string|max:10',
            'pointure_chaussure' => 'nullable|string|max:5',
            'taille_habit' => 'nullable|string|max:10',
            'lieudenaissance' => 'nullable|max:100',
            'nationalite' => 'nullable|string|max:100',
        ]);
//dd($validatedData);
        $eleve->update($validatedData);

        logAction('Modification', "Informations de l’élève mises à jour : {$eleve->nom} {$eleve->prenom} (ID : {$eleve->id}).");

        if (!empty($validatedData['classe_id'])) {
            $classeActuelleId = $eleve->classeActuelle?->classe_id;
            if ($classeActuelleId != $validatedData['classe_id']) {
                $ancienneClasse = \App\Models\Classe::find($classeActuelleId)?->nom;
                $nouvelleClasse = \App\Models\Classe::find($validatedData['classe_id'])?->nom;
                $inscription = $eleve->classeActuelle;
                if ($inscription) {
                    $inscription->update(['classe_id' => $validatedData['classe_id']]);
                } else {
                    $eleve->inscriptions()->create([
                        'eleve_id' => $id,
                        'classe_id' => $validatedData['classe_id'],
                        'annee_id' => session('annee_id') ?? AnneesScolaire::where('active', 1)->value('id'),
                    ]);
                }
                logAction('Transfert', "Élève {$eleve->nom} {$eleve->prenom} transféré de {$ancienneClasse} à {$nouvelleClasse}.");
            }
        }

        return redirect()->back()->with('success', 'Élève mis à jour avec succès.');
    }

    public function destroy(Eleve $eleve)
    {
        $nomComplet = "{$eleve->nom} {$eleve->prenom}";
        $eleve->delete();

        logAction('Suppression', "Élève supprimé du système : {$nomComplet} (ID : {$eleve->id}).");

        return redirect()->route('eleves.index')->with('success', 'Élève supprimé avec succès.');
    }

    public function toggleStatus($id)
    {
        $eleve = Eleve::findOrFail($id);
        $ancienStatut = $eleve->statut ? 'actif' : 'inactif';
        $eleve->statut = !$eleve->statut;
        $eleve->save();
        $nouveauStatut = $eleve->statut ? 'actif' : 'inactif';

        logAction('Changement de statut', "Statut de l’élève {$eleve->nom} {$eleve->prenom} changé de {$ancienStatut} à {$nouveauStatut}.");

        return redirect()->route('elevesparannee.index')->with('success', 'Statut modifié avec succès.');
    }

    public function elevesshow(Request $request, $id)
{
    // 1️⃣ Vérifie d'abord si l'utilisateur est connecté
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    // 2️⃣ Vérifie les autorisations
    if (!in_array(auth()->user()->role, ['admin', 'directeur', 'secretaire'])) {
        return redirect()->back()
            ->with('error', 'Accès refusé. Vous n\'avez pas les autorisations nécessaires.');
    }

    // 3️⃣ Récupère la classe actuelle
    $classe = Classe::findOrFail($id);

    // 4️⃣ Récupère l'année scolaire active
    $anneeActive = \App\Models\AnneesScolaire::where('active', 1)->first();

    // 5️⃣ Recherche l'année scolaire précédente
    $anneePrecedente = null;

    if ($anneeActive) {
        $anneePrecedente = \App\Models\AnneesScolaire::where(
            'id',
            '<',
            $anneeActive->id
        )
        ->orderByDesc('id')
        ->first();
    }

    // 6️⃣ Récupère uniquement les classes de l'année précédente
    //    avec leur effectif
    $classess = collect();

    if ($anneePrecedente) {
        $classess = Classe::where('annee_id', $anneePrecedente->id)
            ->withCount([
                'inscriptions as effectif' => function ($query) use ($anneePrecedente) {
                    $query->where('annee_id', $anneePrecedente->id);
                }
            ])
            ->orderBy('nom')
            ->get();
    }

    // 7️⃣ Récupère les élèves de la classe actuelle
    $query = Inscription::with('eleve')
        ->where('classe_id', $id);

    // 8️⃣ Recherche d'un élève
    if ($request->filled('search')) {

        $search = $request->search;

        $query->whereHas('eleve', function ($q) use ($search) {

            $q->where('nom', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%");
        });
    }

    // 9️⃣ Récupération des inscriptions
    $inscriptions = $query->get();

    // 🔟 Récupération des élèves
    $eleves = $inscriptions->pluck('eleve');

    // 1️⃣1️⃣ Journalisation
    logAction(
        'Consultation',
        "Affichage des élèves inscrits dans la classe {$classe->nom} (recherche : {$request->search})."
    );

    // 1️⃣2️⃣ Retour vers la vue
    return view(
        'eleves.eleveshow',
        compact(
            'classe',
            'eleves',
            'classess',
            'anneeActive',
            'anneePrecedente'
        )
    );
}
    public function profil($id)
    {
        $eleve = Eleve::with(['classeActuelle.classe', 'parent'])->findOrFail($id);

        // 📌 Récupérer l'année active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        // ⚡ Récupérer l’inscription de l’élève pour l’année active
        $inscription = $eleve->inscriptions()
            ->where('annee_id', $anneeActive->id)
            ->first();

        // 🎓 Récupérer la bourse attribuée à l’élève (si elle existe)
        $attribution = null;
        if ($inscription) {
            $attribution = \App\Models\AttributionBourse::with(['bourse.frais'])
                ->where('inscription_id', $inscription->id)
                ->where('etat', 'active')
                ->first();
        }

        logAction('Consultation', "Profil de l’élève consulté : {$eleve->nom} {$eleve->prenom} (ID : {$eleve->id}).");

        $parent = $eleve->parent;

        return view('eleves.profil', compact('eleve', 'parent', 'attribution', 'anneeActive'));
    }



    public function carte($id)
    {
        $eleve = Eleve::with('classeActuelle.classe')->findOrFail($id);
        logAction('Consultation', "Carte scolaire consultée pour l’élève {$eleve->nom} {$eleve->prenom} (matricule : {$eleve->matricule}).");

        return view('eleves.carte', compact('eleve'));
    }

    public function uploadImage(Request $request, $id)
    {
        try {

            /* ================= 0. DEPASSEMENT LIMITE PHP ================= */
            if (
                $request->isMethod('post') &&
                empty($_FILES) &&
                empty($_POST) &&
                (int) $_SERVER['CONTENT_LENGTH'] > 0
            ) {
                return back()->with('error',
                    "L’image dépasse la limite autorisée par le serveur (" .
                    ini_get('upload_max_filesize') . ")."
                );
            }

            /* ================= 1. ELEVE ================= */
            $eleve = Eleve::find($id);
            if (!$eleve) {
                return back()->with('error', "Élève introuvable.");
            }

            /* ================= 2. FICHIER ENVOYE ================= */
            if (!isset($_FILES['photo'])) {
                return back()->with('error', "Aucun fichier reçu.");
            }

            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $phpFileErrors = [
                    UPLOAD_ERR_INI_SIZE   => "Fichier trop grand (limite serveur).",
                    UPLOAD_ERR_FORM_SIZE  => "Fichier trop grand (formulaire).",
                    UPLOAD_ERR_PARTIAL    => "Upload incomplet.",
                    UPLOAD_ERR_NO_FILE    => "Aucun fichier envoyé.",
                    UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
                    UPLOAD_ERR_CANT_WRITE => "Impossible d’écrire sur le disque.",
                    UPLOAD_ERR_EXTENSION  => "Upload bloqué par une extension PHP."
                ];
                return back()->with('error', $phpFileErrors[$_FILES['photo']['error']] ?? "Erreur inconnue upload.");
            }

            if (!$request->hasFile('photo')) {
                return back()->with('error', "Aucune image détectée.");
            }

            $file = $request->file('photo');

            /* ================= 3. VALIDITE IMAGE ================= */
            if (!$file->isValid()) {
                return back()->with('error', "Fichier invalide.");
            }

            /* MIME réel */
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedMime)) {
                return back()->with('error', "Format non autorisé (JPG, PNG, GIF).");
            }

            /* Extension */
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($file->getClientOriginalExtension()), $allowedExt)) {
                return back()->with('error', "Extension de fichier invalide.");
            }

            /* Taille Laravel (2MB) */
            if ($file->getSize() > 2 * 1024 * 1024) {
                return back()->with('error', "Image trop grande (2MB maximum).");
            }

            /* ================= 4. DOSSIER ================= */
            $destinationPath = public_path('uploads/eleves');

            if (!file_exists($destinationPath)) {
                if (!mkdir($destinationPath, 0755, true)) {
                    return back()->with('error', "Impossible de créer le dossier d’upload.");
                }
            }

            if (!is_writable($destinationPath)) {
                return back()->with('error', "Dossier uploads/eleves non accessible en écriture.");
            }

            /* ================= 5. SUPPRIMER ANCIENNE IMAGE ================= */
            if ($eleve->imglink && file_exists(public_path($eleve->imglink))) {
                if (!unlink(public_path($eleve->imglink))) {
                    return back()->with('error', "Impossible de supprimer l’ancienne image.");
                }
            }

            /* ================= 6. ENREGISTRER IMAGE ================= */
            $imageName = uniqid('eleve_', true) . '.' . $file->getClientOriginalExtension();

            if (!$file->move($destinationPath, $imageName)) {
                return back()->with('error', "Erreur lors de l’enregistrement de l’image.");
            }

            /* ================= 7. SAUVEGARDE DB ================= */
            $eleve->imglink = 'uploads/eleves/' . $imageName;

            if (!$eleve->save()) {
                return back()->with('error', "Erreur lors de la sauvegarde en base de données.");
            }

            return back()->with('success', "Photo mise à jour avec succès.");

        } catch (\Throwable $e) {
            return back()->with('error', "Erreur système : " . $e->getMessage());
        }
    }



    public function print($id)
    {
        // Récupération de l'élève avec toutes les relations nécessaires
        $eleve = Eleve::with([
            'classeActuelle.classe',               // Classe actuelle
            'inscriptions.classe',                 // Toutes les inscriptions
            'inscriptions.attributions.bourse',    // Bourses par inscription
            'inscriptions.absences',               // Absences et retards
            'parent',                              // Informations parents
            'notes' => function ($query) {
                $query->with('matiere', 'decoupage') // Moyennes par matière avec trimestre
                ->orderBy('decoupage_id');    // Trier par découpage (trimestre)
            }
        ])->findOrFail($id);

        // Paiements pour la classe et année actuelle
        $paiementsActuels = $eleve->paiementsClasseActuelle()
            ->with('frais', 'annee', 'classe')
            ->get();

        // Regroupement des moyennes par année scolaire et par trimestre
        $moyennesParAnnee = $eleve->notes->groupBy(function($note) {
            return $note->decoupage->annee_id;
        })->map(function($notesAnnee) {
            return $notesAnnee->groupBy(fn($note) => $note->decoupage->trimestre);
        });

        // Regroupement des absences par année et trimestre si disponibles
        $absencesParAnnee = $eleve->inscriptions->mapWithKeys(function($inscription) {
            return [$inscription->id => $inscription->absences->groupBy('trimestre')];
        });

        // Retour vers la vue print dédiée
        return view('eleves.print', compact(
            'eleve',
            'paiementsActuels',
            'moyennesParAnnee',
            'absencesParAnnee'
        ));
    }



}
