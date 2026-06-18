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
        Schema::create('titulaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained();
            $table->foreignId('classe_id')->constrained();
            $table->foreignId('annee_id')->constrained('annees_scolaires');
            $table->timestamps();

            $table->unique(['classe_id', 'annee_id']); // Une classe n’a qu’un titulaire par année
            $table->unique(['enseignant_id', 'annee_id']); // Un enseignant ne peut pas être titulaire de plusieurs classes la même année
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titulaires');
    }
};
