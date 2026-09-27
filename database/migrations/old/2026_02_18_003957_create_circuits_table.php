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
      Schema::create('circuits', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('country')->nullable();
    $table->decimal('length_km', 5, 3)->nullable();
    $table->decimal('fuel_per_lap', 5, 2)->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circuits');
    }
};
