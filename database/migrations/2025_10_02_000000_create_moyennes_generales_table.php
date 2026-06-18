<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moyennes_generales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('annee_id')->constrained('annees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('decoupage_id')->constrained('decoupages')->onDelete('cascade')->onUpdate('cascade');

            $table->decimal('moyenne_classe', 5, 2)->default(0.00);
            $table->decimal('moyenne_forte', 5, 2)->default(0.00);
            $table->decimal('moyenne_faible', 5, 2)->default(0.00);
            $table->string('decision_jury', 100)->nullable();

            $table->timestamps();

            $table->unique(['classe_id', 'annee_id', 'decoupage_id'], 'unique_classe_annee_decoupage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moyennes_generales');
    }
};
