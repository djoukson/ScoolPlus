<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribution_bourses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bourse_id')->constrained('bourses')->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribution_bourses');
    }
};


