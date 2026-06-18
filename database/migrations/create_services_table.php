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
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->decimal('montant', 10, 2); // montant du service
            $table->unsignedBigInteger('annee_id'); // ⚠️ correspond à bigIncrements
            $table->timestamps();

            $table->foreign('annee_id')
                ->references('id')
                ->on('annees_scolaires')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
