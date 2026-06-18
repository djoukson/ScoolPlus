<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bourse_frais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bourse_id')->constrained('bourses')->onDelete('cascade');
            $table->foreignId('frais_id')->constrained('frais')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bourse_frais');
    }
};

