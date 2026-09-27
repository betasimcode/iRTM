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
        Schema::table('stints', function (Blueprint $table) {

            $table->float('avg_track_temp')->nullable();
            $table->float('avg_air_temp')->nullable();
            $table->float('avg_humidity')->nullable();

            $table->float('avg_wind_speed')->nullable();
            $table->float('avg_wind_dir')->nullable();

            $table->integer('sky_mode')->nullable();
            $table->integer('track_state_mode')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stints', function (Blueprint $table) {
            //
        });
    }
};
