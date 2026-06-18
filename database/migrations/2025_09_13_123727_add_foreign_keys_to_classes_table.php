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
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign(['annee_id'], 'fk_classes_annee')->references(['id'])->on('annees_scolaires')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['enseignant_id'], 'fk_classes_enseignant')->references(['id'])->on('enseignants')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign('fk_classes_annee');
            $table->dropForeign('fk_classes_enseignant');
        });
    }
};
