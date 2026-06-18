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
        Schema::create('ecole', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('directeur')->nullable();
            $table->string('annee_id ')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable(); // chemin du fichier logo
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
