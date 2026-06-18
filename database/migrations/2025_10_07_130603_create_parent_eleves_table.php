<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table parent_eleves.
     */
    public function up(): void
    {
        Schema::create('parent_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')
                ->constrained('eleves')
                ->onDelete('cascade'); // Supprime aussi les parents si l'élève est supprimé

            // Informations du père
            $table->string('pere_nom')->nullable();
            $table->string('pere_tel')->nullable();
            $table->string('pere_profession')->nullable();
            $table->string('pere_email')->nullable();
            $table->string('pere_adresse')->nullable();

            // Informations de la mère
            $table->string('mere_nom')->nullable();
            $table->string('mere_tel')->nullable();
            $table->string('mere_profession')->nullable();
            $table->string('mere_email')->nullable();
            $table->string('mere_adresse')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Supprime la table si on rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_eleves');
    }
};
