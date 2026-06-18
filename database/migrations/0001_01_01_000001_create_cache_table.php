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
        Schema::create('cache', function (Blueprint $table) {
            // Clé primaire de longueur 191 pour éviter l'erreur sur utf8mb4
            $table->string('key', 191)->primary();
            $table->mediumText('value');
            $table->integer('expiration')->default(0);
            $table->timestamps(); // facultatif, utile pour suivi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
    }
};
