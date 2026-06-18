<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Paiement;
use App\Models\PaiementSouscription;
use App\Models\Depense;
use App\Models\SalaireRemuneration;

class EtatRapportController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->filled(['date_start', 'date_end'])) {
            return view('etats.index');
        }

        $dateDebut = Carbon::parse($request->date_start)->startOfDay();
        $dateFin   = Carbon::parse($request->date_end)->endOfDay();

        // Entrées
//        $paiements = Paiement::whereBetween('created_at', [$dateDebut, $dateFin])->get();
//        $souscriptions = PaiementSouscription::whereBetween('created_at', [$dateDebut, $dateFin])->get();


        $paiements = Paiement::with(['eleve', 'frais'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->get();

        $souscriptions = PaiementSouscription::with(['eleve', 'service'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->get();



        // Sorties
        $depenses = Depense::whereBetween('created_at', [$dateDebut, $dateFin])->get();
        $salaires = SalaireRemuneration::whereBetween('date_debut', [$dateDebut, $dateFin])->get();

        return view('etats.index', compact(
            'dateDebut',
            'dateFin',
            'paiements',
            'souscriptions',
            'depenses',
            'salaires'
        ));
    }

    public function print(Request $request)
    {
        $dateDebut = Carbon::parse($request->date_start)->startOfDay();
        $dateFin   = Carbon::parse($request->date_end)->endOfDay();
        $type      = $request->type; // all, scolarite, souscription, depense, salaire
        $paiements = Paiement::with(['eleve', 'frais'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->get();

        $souscriptions = PaiementSouscription::with(['eleve', 'service'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->get();

        return view('etats.print_stats', [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'type' => $type,
            'paiements' =>$paiements,
            'souscriptions' => $souscriptions,
            'depenses' => Depense::whereBetween('created_at', [$dateDebut, $dateFin])->get(),
            'salaires' => SalaireRemuneration::whereBetween('date_debut', [$dateDebut, $dateFin])->get(),
        ]);
    }
}
