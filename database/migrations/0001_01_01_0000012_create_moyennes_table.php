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
        Schema::create('moyennes', function (Blueprint $table) {
            $table->id();

            // 🔗 Inscription (l'élève inscrit à une classe/année)
            $table->unsignedBigInteger('inscription_id');

            // 🔗 Découpage (trimestre / semestre)
            $table->unsignedBigInteger('decoupage_id');

            // ✅ Moyenne de l’élève
            $table->decimal('moyenne', 5, 2);

            // ✅ Rang dans la classe
            $table->integer('rang')->nullable();

            // ✅ Appréciation (mention, remarque du prof, etc.)
            $table->string('appreciation', 50)->nullable();

            // ✅ Timestamps
            $table->timestamps();

            // ✅ Contrainte d’unicité (un élève ne peut avoir qu’une moyenne par découpage)
            $table->unique(['inscription_id', 'decoupage_id'], 'unique_moyenne');

            // ✅ Index sur decoupage_id pour les recherches rapides
            $table->index('decoupage_id', 'fk_moyennes_decoupage');

            $table->decimal('moyenne_annuelle', 5, 2)->default(0.00);
            $table->integer('rang_annuel')->nullable();
            $table->string('appreciation_annuelle')->nullable();

             $table->foreign('inscription_id')->references('id')->on('inscriptions')->onDelete('cascade');
             $table->foreign('decoupage_id')->references('id')->on('decoupages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moyennes');
    }
};
