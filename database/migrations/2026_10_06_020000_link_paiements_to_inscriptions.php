<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('inscription_id')->nullable()->after('eleve_id');
            $table->index('inscription_id', 'paiements_inscription_id_index');
        });

        DB::statement(
            'UPDATE paiements AS paiement
             INNER JOIN inscriptions AS inscription
                ON inscription.eleve_id = paiement.eleve_id
               AND inscription.classe_id = paiement.classe_id
               AND inscription.annee_id = paiement.annee_id
             SET paiement.inscription_id = inscription.id'
        );

        Schema::table('paiements', function (Blueprint $table) {
            $table->foreign('inscription_id', 'fk_paiements_inscription_scope')
                ->references('id')->on('inscriptions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign('fk_paiements_inscription_scope');
            $table->dropIndex('paiements_inscription_id_index');
            $table->dropColumn('inscription_id');
        });
    }
};
