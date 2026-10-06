<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recompute existing classifications from the immediately preceding school year.
        DB::table('inscriptions')->update(['type_inscription' => 'Nouveau']);

        $annees = DB::table('annees_scolaires')->orderBy('id')->get(['id']);
        $anneePrecedenteId = null;

        foreach ($annees as $annee) {
            if ($anneePrecedenteId !== null) {
                DB::statement(
                    'UPDATE inscriptions AS courante
                     INNER JOIN inscriptions AS precedente
                        ON precedente.eleve_id = courante.eleve_id
                     SET courante.type_inscription = ?
                     WHERE courante.annee_id = ? AND precedente.annee_id = ?',
                    ['Réinscrit', $annee->id, $anneePrecedenteId]
                );
            }

            $anneePrecedenteId = $annee->id;
        }
    }

    public function down(): void
    {
        // Classifications are derived from enrollment history and are not reversible.
    }
};
