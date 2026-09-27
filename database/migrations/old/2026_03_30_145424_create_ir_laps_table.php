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
        Schema::create('ir_laps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ir_stint_id')->constrained()->onDelete('cascade');
            $table->integer('lap_number');
            $table->float('lap_time');
            $table->float('fuel_lap_start');
            $table->float('fuel_consumed'); // Gasolina usada solo en esta vuelta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ir_laps');
    }
};
