<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->foreign(['annee_id'], 'fk_affect_annee')->references(['id'])->on('annees_scolaires')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['classe_id'], 'fk_affect_classe')->references(['id'])->on('classes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['enseignant_id'], 'fk_affect_enseignant')->references(['id'])->on('enseignants')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['matiere_id'], 'fk_affect_matiere')->references(['id'])->on('matieres')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropForeign('fk_affect_annee');
            $table->dropForeign('fk_affect_classe');
            $table->dropForeign('fk_affect_enseignant');
            $table->dropForeign('fk_affect_matiere');
        });
    }
};
