<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('eleve_id')
                ->nullable()
                ->constrained('eleves')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('type')->default('direct');

            $table->string('subject')->nullable();

            $table->timestamps();

            $table->index(['eleve_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};