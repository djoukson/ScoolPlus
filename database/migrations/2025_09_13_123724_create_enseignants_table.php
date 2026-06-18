<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enseignants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_enseignants_user');
            $table->string('matricule', 50)->nullable()->unique('matricule');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('specialite', 100)->nullable();
            $table->string('tel', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('type', 150)->nullable();
            $table->boolean('statut')->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->unsignedBigInteger('niveau_id')->nullable()->after('email');

            $table->foreign('niveau_id')
                ->references('id')
                ->on('niveaux')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};
