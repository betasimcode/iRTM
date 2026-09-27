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
        Schema::update('iracing_series', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('iracing_series_id')->unique(); // 228
            $table->string('license')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iracing_series');
    }
};
