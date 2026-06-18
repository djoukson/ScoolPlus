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
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inscription_id')->index('fk_notes_inscription');
            $table->unsignedBigInteger('matiere_id')->index('fk_notes_matiere');
            $table->enum('type_eval', ['interro', 'devoir', 'examen']);
            $table->decimal('note', 5);
            $table->date('date_eval');
            $table->enum('trimestre', ['1', '2', '3'])->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
