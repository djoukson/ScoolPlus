<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Paiement;
use App\Models\PaiementSouscription;
use App\Models\Service;
use App\Models\Souscription;
use App\Models\Eleve;
use App\Models\AnneesScolaire;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Affiche la liste des services
     */
    public function index()
    {
        // 📌 Récupérer l'année active (depuis la session ou par défaut en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        $services = Service::with('annee')->where('annee_id',$anneeActive->id)->orderBy('created_at', 'desc')->get();
        $annees = AnneesScolaire::orderBy('nom', 'desc')->get();

        return view('services.service', compact('services', 'annees'));
    }

    /**
     * Stocke un nouveau service
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'annee_id' => 'required|exists:annees_scolaires,id',
        ]);

        Service::updateOrCreate(
            ['id' => $request->id],
            $request->only(['libelle', 'description', 'montant', 'annee_id'])
        );

        return redirect()->route('services.index')->with('success', 'Service enregistré avec succès.');
    }

    /**
     * Récupère un service pour édition
     */
    public function edit(Service $service)
    {
        return response()->json($service);
    }

    /**
     * Supprime un service
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service supprimé avec succès.');
    }

    /**
     * Affiche les souscriptions
     */
    public function souscriptions()
    {
        // 🔹 Récupérer l'année active depuis la session ou, à défaut, celle marquée active en BDD
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // 🔹 Récupérer les classes ayant des élèves inscrits pour cette année
        $classes = Classe::whereHas('inscriptions', function($q) use ($anneeActive) {
            $q->where('annee_id', $anneeActive->id);
        })
            ->with(['inscriptions' => function($q) use ($anneeActive) {
                $q->where('annee_id', $anneeActive->id)->with('eleve');
            }])
            ->get();

        // 🔹 Récupérer les services liés à l'année active
        $services = Service::with('annee')
            ->where('annee_id', $anneeActive->id)
            ->get();

        // 🔹 Récupérer les souscriptions de l'année active
        $souscriptions = Souscription::with([
            'service.annee',
            'eleve' => function($q) use ($anneeActive) {
                $q->with(['inscriptions' => function($q2) use ($anneeActive) {
                    $q2->where('annee_id', $anneeActive->id)->with('classe');
                }]);
            }
        ])
            ->whereHas('service', function($q) use ($anneeActive) {
                $q->where('annee_id', $anneeActive->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('services.souscription', compact('classes', 'services', 'souscriptions', 'anneeActive'));
    }


// Route AJAX pour récupérer les élèves d'une classe
    public function getElevesByClasse($classeId)
    {
        // 🔹 Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return response()->json([], 404);
        }

        // 🔹 Récupérer les élèves inscrits dans cette classe pour cette année
        $eleves = Eleve::whereHas('inscriptions', function($q) use ($classeId, $anneeActive) {
            $q->where('classe_id', $classeId)
                ->where('annee_id', $anneeActive->id);
        })
            ->select('id', 'nom', 'prenom')
            ->orderBy('nom')
            ->get();

        return response()->json($eleves);
    }





    /**
     * Stocke une nouvelle souscription
     */
    public function storeSouscription(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $currentYear = AnneesScolaire::where('active', 1)->first();
//dd($currentYear->id);
        $service = Service::findOrFail($request->service_id);

        Souscription::create([
            'eleve_id' => $request->eleve_id,
            'service_id' => $request->service_id,
            'annee_id' => $currentYear->id,
            'montant' => $service->montant, // montant pris depuis le service
        ]);

        return redirect()->route('services.souscriptions')->with('success', 'Souscription enregistrée.');
    }

    /**
     * Supprime une souscription
     */
    public function destroySouscription(Souscription $souscription)
    {
        $souscription->delete();
        return redirect()->route('services.souscriptions')->with('success', 'Souscription supprimée.');
    }


    public function paiementsSouscriptions()
    {
        // 🔹 Récupérer l'année active (session ou active en BDD)
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // 🔹 Récupérer uniquement les services liés à l'année active
        $services = Service::with([
            'annee',
            'souscriptions.eleve' => function ($q) use ($anneeActive) {
                // Charger uniquement les souscriptions dont l'élève est inscrit dans l'année active
                $q->with(['inscriptions' => function ($q2) use ($anneeActive) {
                    $q2->where('annee_id', $anneeActive->id);
                }]);
            }
        ])
            ->where('annee_id', $anneeActive->id)
            ->get();

        // 🔹 Préparer le tableau pour affichage
        $paiementsGrouped = [];

        foreach ($services as $service) {
            foreach ($service->souscriptions as $sub) {
                $paiements = PaiementSouscription::where('souscription_id', $sub->id)->get();
                $montantPaye = $paiements->sum('montant');

                $paiementsGrouped[] = [
                    'eleve' => $sub->eleve,
                    'service' => $service,
                    'montant_total' => $service->montant,
                    'montant_paye' => $montantPaye,
                    'reste' => $service->montant - $montantPaye,
                    'souscription' => $sub,
                ];
            }
        }

        return view('services.paiement', compact('services', 'paiementsGrouped', 'anneeActive'));
    }

    public function getSouscriptionsForService(Service $service)
    {
        $souscriptions = $service->souscriptions()->with('eleve')->get();
        return response()->json($souscriptions);
    }



// Récupérer les souscriptions pour un service via AJAX
    public function getSouscriptions(Service $service)
    {
        $souscriptions = Souscription::with('eleve')
            ->where('service_id', $service->id)
            ->get();

        return response()->json($souscriptions);
    }

// Stocker le paiement
    public function storePaiement(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'service_id' => 'required|exists:services,id',
            'montant' => 'required|numeric|min:1',
        ]);

        // 🔹 Vérifier si une souscription existe pour cet élève et ce service
        $souscription = Souscription::where('eleve_id', $request->eleve_id)
            ->where('service_id', $request->service_id)
            ->first();

        // 🔹 Si aucune souscription, en créer une automatiquement
        if (!$souscription) {
            $souscription = Souscription::create([
                'eleve_id' => $request->eleve_id,
                'service_id' => $request->service_id,
                'date_souscription' => now(),
            ]);
        }

        // 🔹 Enregistrer le paiement
        PaiementSouscription::create([
            'souscription_id' => $souscription->id,
            'eleve_id' => $request->eleve_id,
            'service_id' => $request->service_id,
            'montant' => $request->montant,
            'date_paiement' => now(),
        ]);

        return redirect()
            ->route('services.historique', [$request->eleve_id, $request->service_id])
            ->with('success', 'Paiement enregistré avec succès.');
    }

    // Mettre à jour un paiement
    public function updatePaiement(Request $request, $id)
    {
        $paiement = PaiementSouscription::findOrFail($id);

        $request->validate([
            'montant' => 'required|numeric|min:1',
        ]);

        $paiement->update([
            'montant' => $request->montant,
        ]);

        return redirect()->back()->with('success', 'Paiement mis à jour avec succès.');
    }


    // Supprimer un paiement
    public function deletePaiement($id)
    {
        $paiement = PaiementSouscription::findOrFail($id);
        $paiement->delete();

        return redirect()->back()->with('success', 'Paiement supprimé avec succès.');
    }
    public function historique($eleveId, $serviceId)
    {
        // 🔹 Charger l'élève
        $eleve = Eleve::findOrFail($eleveId);

        // 🔹 Charger le service sélectionné
        $service = Service::findOrFail($serviceId);

        // 🔹 Récupérer uniquement les paiements liés à cet élève et ce service
        $paiements = PaiementSouscription::with('service')
            ->where('eleve_id', $eleveId)
            ->where('service_id', $serviceId)
            ->orderBy('created_at', 'desc')
            ->get();

        // 🔹 Calculs
        $montantService = $service->montant ?? 0;
        $totalPaye = $paiements->sum('montant');
        $totalRestant = max($montantService - $totalPaye, 0);

        // 🔹 Vérifier si le formulaire d’ajout doit être affiché
        $showAddForm = request()->has('add');

        // 🔹 Retourner la vue
        return view('services.historique', compact(
            'eleve',
            'paiements',
            'service',
            'showAddForm',
            'montantService',
            'totalPaye',
            'totalRestant'
        ));
    }




}
