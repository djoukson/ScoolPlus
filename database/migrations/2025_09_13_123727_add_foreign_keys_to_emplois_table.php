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
        Schema::table('emplois', function (Blueprint $table) {
            $table->foreign(['cours_id'], 'fk_emplois_cours')->references(['id'])->on('cours')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['enseignant_id'], 'fk_emplois_enseignant')->references(['id'])->on('enseignants')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emplois', function (Blueprint $table) {
            $table->dropForeign('fk_emplois_cours');
            $table->dropForeign('fk_emplois_enseignant');
        });
    }
};
