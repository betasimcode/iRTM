<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('race_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('circuit_id')->constrained()->onDelete('cascade');
            $table->decimal('lap_time', 6, 3); // tiempo en segundos
            $table->decimal('fuel_used', 5, 2); // combustible en litros
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_sessions');
    }
};
