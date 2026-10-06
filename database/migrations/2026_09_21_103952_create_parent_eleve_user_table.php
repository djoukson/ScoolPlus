<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_eleve_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('eleve_id')
                ->constrained('eleves')
                ->cascadeOnDelete();

            $table->string('relation')->nullable();
            // Exemple : pere, mere, tuteur, parents

            $table->timestamps();

            $table->unique(['user_id', 'eleve_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_eleve_user');
    }
};