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
 Schema::create('laps', function (Blueprint $table) {

    $table->id();

    $table->foreignId('stint_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->integer('lap_number');

    $table->float('lap_time')->nullable();
    $table->float('fuel_used')->nullable();

    $table->boolean('is_pit_lap')->default(false);

    $table->timestamps();

    $table->unique(['stint_id','lap_number']);

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laps');
    }
};
