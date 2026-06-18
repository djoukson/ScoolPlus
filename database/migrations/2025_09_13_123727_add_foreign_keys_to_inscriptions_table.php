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
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->foreign(['annee_id'], 'fk_inscription_annee')->references(['id'])->on('annees_scolaires')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['classe_id'], 'fk_inscription_classe')->references(['id'])->on('classes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['eleve_id'], 'fk_inscription_eleve')->references(['id'])->on('eleves')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign('fk_inscription_annee');
            $table->dropForeign('fk_inscription_classe');
            $table->dropForeign('fk_inscription_eleve');
        });
    }
};
