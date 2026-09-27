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
        Schema::create('series_entries', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('series_id')
                ->constrained('iracing_series')
                ->cascadeOnDelete();

            /*
             * Coche con el que el Workspace
             * competirá la temporada.
             */
            $table->foreignId('competition_car_id')
                ->constrained('cars')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'active',

                'withdrawn',

                'finished'

            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | Fechas deportivas
            |--------------------------------------------------------------------------
            */

            $table->timestamp('joined_at')
                ->nullable();

            $table->timestamp('left_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Restricciones
            |--------------------------------------------------------------------------
            */

            $table->unique([

                'workspace_id',

                'series_id'

            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_entries');
    }
};
