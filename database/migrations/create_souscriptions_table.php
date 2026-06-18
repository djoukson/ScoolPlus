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
        Schema::create('souscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eleve_id'); // élève qui souscrit
            $table->unsignedBigInteger('service_id'); // service souscrit
            $table->unsignedBigInteger('annee_id'); // année scolaire à laquelle la souscription appartient
            $table->timestamps();

            // Clés étrangères
            $table->foreign('eleve_id')->references('id')->on('eleves')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('annee_id')->references('id')->on('annees_scolaires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('souscriptions');
    }
};
