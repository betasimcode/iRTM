<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelemetriesTablev2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telemetries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con el usuario
            $table->integer('lap'); // Número de la vuelta
            $table->decimal('lap_time', 8, 3); // Tiempo de vuelta
            $table->decimal('fuel', 8, 2); // Combustible restante
            $table->decimal('length_km', 8, 2); // Longitud del circuito
            $table->decimal('track_temp', 5, 2); // Temperatura de la pista
            $table->decimal('air_temp', 5, 2); // Temperatura del aire
            $table->timestamp('timestamp'); // Timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telemetries');
    }
}
