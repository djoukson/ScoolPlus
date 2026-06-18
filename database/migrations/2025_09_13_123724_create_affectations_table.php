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
        Schema::create('affectations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enseignant_id')->index('fk_affect_enseignant');
            $table->unsignedBigInteger('classe_id')->index('fk_affect_classe');
            $table->unsignedBigInteger('matiere_id')->index('fk_affect_matiere');
            $table->unsignedBigInteger('annee_id')->index('fk_affect_annee');
            $table->integer('heures_attribuees')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
