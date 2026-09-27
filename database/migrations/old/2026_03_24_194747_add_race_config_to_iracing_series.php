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
        Schema::table('iracing_series', function (Blueprint $table) {
            $table->enum('race_type', ['laps', 'time'])->default('laps');
            $table->enum('start_type', ['standing', 'rolling'])->default('standing');

            $table->integer('race_length')->nullable();
            // vueltas o minutos según race_type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iracing_series', function (Blueprint $table) {
            //
        });
    }
};
