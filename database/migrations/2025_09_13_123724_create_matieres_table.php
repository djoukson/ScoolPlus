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
        Schema::create('matieres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 100);
            $table->enum('niveau', ['primaire', 'college']);
            $table->integer('coefficient')->nullable()->default(1);
            $table->string('sigle')->nullable()->after('nom'); // nullable pour éviter les erreurs
            $table->decimal('cout_par_heure', 10, 2)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};
