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
        Schema::create('decoupages', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->enum('type', ['trimestre', 'semestre']);
            $table->foreignId('annee_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->timestamps();
            $table->foreignId('decoupage_id')
                ->nullable()
                ->after('annee_id')
                ->constrained('decoupages')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decoupages');
    }
};
