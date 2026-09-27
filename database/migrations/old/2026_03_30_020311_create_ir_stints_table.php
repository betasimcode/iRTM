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
        Schema::create('ir_stints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('tracks');
            $table->integer('iracing_subsession_id')->index();

            // Identificación del Mustang
            $table->string('car_name');
            $table->integer('car_id');

            // --- LA CLAVE PARA ENDURANCE ---
            $table->integer('sim_time_day')->comment('Segundos desde medianoche en el sim');
            // Opcional: Podemos guardar el offset de la sesión (ej: 14:30)

            // Estado inicial
            $table->float('fuel_setup');
            $table->float('track_temp');
            $table->float('air_temp');
            $table->integer('track_usage_pct')->nullable();

            // Resultados
            $table->integer('laps_completed')->default(0);
            $table->float('fuel_consumed')->default(0);
            $table->float('fuel_per_lap')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ir_stints');
    }
};
