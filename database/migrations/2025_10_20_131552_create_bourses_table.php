<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bourses', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->unsignedDecimal('pourcentage', 5, 2)->default(0); // 0–100 %
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bourses');
    }
};
