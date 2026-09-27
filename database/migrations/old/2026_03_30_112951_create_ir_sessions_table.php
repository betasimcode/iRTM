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
        Schema::create('ir_sessions', function (Blueprint $table) {
            $table->id();
            // Relación con la tabla tracks que ya tenemos
            $table->foreignId('track_id')->constrained('tracks')->onDelete('cascade');

            // El ID único que iRacing asigna a la subsesión (ej: 54689210)
            $table->integer('iracing_subsession_id')->unique();

            // Tipo: Practice, Qualifying, Race, Time Trial
            $table->string('session_type');

            // Strength of Field (Nivel medio del split)
            $table->integer('sof')->nullable();

            // El timestamp de iRacing (cuándo ocurrió la sesión)
            $table->timestamp('session_at');

            $table->timestamps(); // created_at y updated_at de Laravel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ir_sessions');
    }
};
