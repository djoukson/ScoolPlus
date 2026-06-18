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
        Schema::create('paiements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inscription_id')->index('fk_paiements_inscription');
            $table->decimal('montant', 10);
            $table->enum('type', ['inscription', 'scolarite', 'autre']);
            $table->integer('tranche')->nullable();
            $table->enum('mode_paiement', ['cash', 'mobile_money', 'carte', 'cheque', 'virement'])->nullable();
            $table->date('date_paiement');
            $table->string('reference', 150)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
