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
        Schema::create('epreuves', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('matiere_id');
            $table->unsignedBigInteger('type_evaluation_id');
            $table->unsignedBigInteger('annee_id');
            $table->unsignedBigInteger('decoupage_id');
            $table->string('etat')->default('attente'); // état par défaut

            $table->timestamps();

            // Foreign keys
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('matiere_id')->references('id')->on('matieres')->onDelete('cascade');
            $table->foreign('type_evaluation_id')->references('id')->on('type_evaluations')->onDelete('cascade');
            $table->foreign('annee_id')->references('id')->on('annees_scolaires')->onDelete('cascade');
            $table->foreign('decoupage_id')->references('id')->on('decoupages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epreuves');
    }
};
