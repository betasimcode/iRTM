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
        Schema::table('ir_laps', function (Blueprint $table) {
            $table->float('track_temp')->nullable();
            $table->float('air_temp')->nullable();
            $table->float('wind_speed')->nullable();
            $table->integer('wind_dir')->nullable();
            $table->float('humidity')->nullable();
            $table->integer('sky')->nullable(); // 0: Clear, 1: Partly Cloudy, etc.
            $table->integer('track_state')->nullable(); // Para el estado dinámico
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ir_laps', function (Blueprint $table) {
            //
        });
    }
};
