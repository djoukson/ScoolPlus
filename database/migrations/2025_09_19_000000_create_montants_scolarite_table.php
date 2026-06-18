<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMontantsScolariteTable extends Migration
{
    public function up()
    {
        Schema::create('montants_scolarite', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant', 15, 2)->default(0); // montant en GHS ou autre
            $table->string('description')->nullable(); // facultatif : libellé/description
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('montants_scolarite');
    }
}
