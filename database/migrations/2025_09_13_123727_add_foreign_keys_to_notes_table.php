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
        Schema::table('notes', function (Blueprint $table) {
            $table->foreign(['inscription_id'], 'fk_notes_inscription')->references(['id'])->on('inscriptions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['matiere_id'], 'fk_notes_matiere')->references(['id'])->on('matieres')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign('fk_notes_inscription');
            $table->dropForeign('fk_notes_matiere');
        });
    }
};
