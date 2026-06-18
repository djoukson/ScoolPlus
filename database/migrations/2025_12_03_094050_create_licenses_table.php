<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('license_key')->unique();
            $table->float('duration_years')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('machine_id')->nullable();
            $table->enum('status', ['active','expired','revoked'])->default('active');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
