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
        Schema::create('classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 50);
            $table->enum('niveau', ['primaire', 'college']);
            $table->unsignedBigInteger('enseignant_id')->nullable()->index('fk_classes_enseignant');
            $table->unsignedBigInteger('annee_id')->index('fk_classes_annee');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->enum('type_decoupage', ['trimestre', 'semestre'])->nullable()->after('niveau_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
