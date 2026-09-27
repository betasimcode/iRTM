<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telemetries', function (Blueprint $table) {

            $table->float('humidity')->nullable();
            $table->float('wind_speed')->nullable();
            $table->float('wind_dir')->nullable();
            $table->string('sky')->nullable();
            $table->string('track_state')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('telemetries', function (Blueprint $table) {

            $table->dropColumn([
                'humidity',
                'wind_speed',
                'wind_dir',
                'sky',
                'track_state'
            ]);

        });
    }
};
