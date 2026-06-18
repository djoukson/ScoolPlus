<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('matiere_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('decoupage_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('heures', 5, 2); // 0.5, 1, 2.5, etc.
            $table->boolean('is_justified')->default(false);

            $table->timestamps();

            // 🔒 éviter doublons exacts
            $table->index(['inscription_id', 'matiere_id', 'decoupage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
