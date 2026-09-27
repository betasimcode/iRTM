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
        Schema::create('lap_sectors', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('telemetry_id');

            $table->integer('sector_number');
            $table->decimal('sector_time',8,3);

            $table->timestamps();

            $table->foreign('telemetry_id')
                ->references('id')
                ->on('telemetries')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lap_sectors');
    }
};
