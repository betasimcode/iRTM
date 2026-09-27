<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // contexto
            $table->string('car')->nullable();
            $table->string('track')->nullable();
            $table->string('session_type')->nullable();

            // resumen
            $table->integer('laps');
            $table->float('avg_lap')->nullable();
            $table->float('avg_fuel')->nullable();

            $table->timestamp('started_at');
            $table->timestamp('ended_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stints');
    }
};
