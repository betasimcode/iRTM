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
        Schema::table('race_sessions', function (Blueprint $table) {
            $table->string('tyre_choice')->nullable();
            $table->string('session_type')->nullable();
            $table->integer('laps_done')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_sessions', function (Blueprint $table) {
            //
        });
    }
};
