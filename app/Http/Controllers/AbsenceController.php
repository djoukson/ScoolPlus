<?php
namespace App\Http\Controllers;
use App\Models\Absence;
use App\Models\AnneesScolaire;
use App\Models\Classe;
use App\Models\Decoupage;
use App\Models\Inscription;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenceController extends Controller{

    public function index(Request $request)
    {
        // 0️⃣ Année scolaire active
        $anneeActive = session('annee_id')
            ? AnneesScolaire::find(session('annee_id'))
            : AnneesScolaire::where('active', 1)->first();

        if (!$anneeActive) {
            return back()->with('error', 'Aucune année scolaire active.');
        }


        $classes = Classe::where('annee_id', $anneeActive->id)
            ->whereHas('niveau', fn($q) => $q->where('nom', '!=', 'Primaire'))
            ->orderBy('nom')
            ->get();

        // 1️⃣ Absences avec relations
        $absences = Absence::with([
            'inscription.eleve',
            'inscription.classe',
            'matiere',
            'decoupage'
        ])
            ->whereHas('inscription', fn($q) => $q->where('annee_id', $anneeActive->id))
            ->get()
            // Grouper par élève
            ->groupBy(fn($absence) => $absence->inscription->eleve->id)
            // Mapper pour calculer le total des heures
            ->map(function($group) {
                return [
                    'inscription' => $group->first()->inscription,
                    'total_heures' => $group->sum('heures'),
                    'absences' => $group, // pour le modal ou page détails
                ];
            });

        return view('absence.index', [
            'anneeActive' => $anneeActive,
            'absences' => $absences,
            'classes' => $classes,
            'decoupages' => Decoupage::all(),
            'matieres' => Matiere::orderBy('nom')->get(),
        ]);
    }





    public function store(Request $request)
    {
        $request->validate([
            'inscription_id' => 'required|exists:inscriptions,id',
            'decoupage_id'   => 'required|exists:decoupages,id',
            'absences'       => 'required|array',
            'absences.*.matiere_id' => 'required|exists:matieres,id',
            'absences.*.heures'     => 'required|numeric|min:0.5',
        ]);

        $data = [];

        foreach ($request->absences as $absence) {
            $data[] = [
                'inscription_id' => $request->inscription_id,
                'matiere_id'     => $absence['matiere_id'],
                'decoupage_id'   => $request->decoupage_id,
                'heures'         => $absence['heures'],
                'is_justified'   => $absence['is_justified'] ?? 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        DB::table('absences')->insert($data);

        return back()->with('success', 'Absences enregistrées avec succès.');
    }
    public function destroy(Absence $absence)
    {
        try {
            // Récupérer l'ID de l'inscription pour revenir sur la page show
            $inscriptionId = $absence->inscription_id;

            $absence->delete(); // supprime l'absence

            return redirect()->route('absences.show', $inscriptionId)
                ->with('success', 'Absence supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('absences.show', $inscriptionId ?? 0)
                ->with('error', 'Impossible de supprimer cette absence.');
        }
    }


    public function show(Inscription $inscription)
    {
        // On récupère toutes les absences de cet élève
        $absences = $inscription->absences()->with(['matiere', 'decoupage'])->get();

        // Calcul du total des heures
        $total_heures = $absences->sum(fn($a) => $a->heures ?? 0);

        return view('absence.show', [
            'inscription' => $inscription,
            'absences' => $absences,
            'total_heures' => $total_heures,
        ]);
    }
    public function getMatieres(Classe $classe)
    {
        // On récupère les matières affectées à cette classe via la table affectation
        $matieres = Matiere::whereIn('id', function($query) use ($classe) {
            $query->select('matiere_id')
                ->from('affectation')
                ->where('classe_id', $classe->id);
        })->orderBy('nom')->get();

        return response()->json($matieres);
    }
    public function toggleJustified(Absence $absence)
    {
        $absence->is_justified = !$absence->is_justified;
        $absence->save();

        return back()->with(
            'success',
            $absence->is_justified
                ? 'Absence marquée comme justifiée.'
                : 'Absence marquée comme non justifiée.'
        );
    }


}
