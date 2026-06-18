<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

        public function up()
    {
        Schema::create('heures_cours', function (Blueprint $table) {
            $table->id();
            $table->string('libelle'); // ex : "1ère heure"
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heure_cours');
    }
};
