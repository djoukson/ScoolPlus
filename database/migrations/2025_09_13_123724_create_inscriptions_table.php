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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eleve_id')->index('fk_inscription_eleve');
            $table->unsignedBigInteger('classe_id')->index('fk_inscription_classe');
            $table->unsignedBigInteger('annee_id')->index('fk_inscription_annee');
            $table->date('date_inscription');
            $table->enum('statut', ['actif', 'transfere', 'abandon', 'termine'])->nullable()->default('actif');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
