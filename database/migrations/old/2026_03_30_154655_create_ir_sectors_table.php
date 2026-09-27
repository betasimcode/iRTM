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
        Schema::create('ir_sectors', function (Blueprint $table) {
            $table->id();
            // Relación con la vuelta
            $table->foreignId('ir_lap_id')->constrained()->onDelete('cascade');

            $table->integer('sector_number'); // 1, 2, 3...
            $table->double('sector_time', 8, 4); // Tiempo exacto del sector

            // Opcional: Guardar el porcentaje de pista donde empieza (para debug)
            $table->double('start_pct', 5, 4)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ir_sectors');
    }
};
