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
            Schema::table('race_sessions', function (Blueprint $table) {
                $table->decimal('fuel_start', 6,2)->nullable();
                $table->decimal('fuel_end', 6,2)->nullable();
                $table->decimal('track_temp', 5,2)->nullable();
                $table->decimal('air_temp', 5,2)->nullable();
                $table->string('weather')->nullable();
                $table->integer('incidents')->nullable();
                $table->text('notes')->nullable();
});
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
