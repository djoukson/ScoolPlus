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
        Schema::table('cours', function (Blueprint $table) {
            $table->foreign(['classe_id'], 'fk_cours_classe')->references(['id'])->on('classes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['matiere_id'], 'fk_cours_matiere')->references(['id'])->on('matieres')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->dropForeign('fk_cours_classe');
            $table->dropForeign('fk_cours_matiere');
        });
    }
};
