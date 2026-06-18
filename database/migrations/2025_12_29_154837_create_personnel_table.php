<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('poste')->nullable();
            $table->decimal('salaire', 10, 2)->default(0); // salaire en GHS ou monnaie locale
            $table->string('tel', 8)->nullable();
            $table->string('email')->nullable()->unique();
            $table->boolean('statut')->default(true); // actif/inactif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};
