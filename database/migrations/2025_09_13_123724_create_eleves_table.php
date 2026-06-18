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
        Schema::create('eleves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_eleves_user');
            $table->string('matricule', 50)->unique('matricule');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->date('date_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->text('adresse')->nullable();
            $table->text('nationalite')->nullable();
            $table->text('observation')->nullable();
            $table->text('status_eleve')->nullable();
            $table->string('tuteur_nom', 100)->nullable();
            $table->string('tuteur_tel', 30)->nullable();
            $table->boolean('statut')->default(1); // 1 = actif, 0 = inactif
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
