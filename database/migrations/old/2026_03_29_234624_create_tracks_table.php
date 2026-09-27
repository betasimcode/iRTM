<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->integer('iracing_track_id')->unique();

            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('variant')->nullable();

            // Campos de localización y estado
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('track_state')->nullable(); // Ej: "Heavy Rubber", "Greasy"

            $table->float('length_km', 8, 3)->default(0.000);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
