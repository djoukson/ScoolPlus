<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('montants_frais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annee_id')->constrained('annees_scolaires')->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('frais_id')->constrained('frais')->cascadeOnDelete();
            $table->decimal('montant', 12, 2); // ex: 150000.00
            $table->timestamps();

            $table->unique(['annee_id', 'classe_id', 'frais_id']); // éviter doublons
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('montants_frais');
    }
};
