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
        Schema::create('series_entry_members', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('series_entry_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Estado del miembro
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'active',

                'inactive',

                'withdrawn'

            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | Rol deportivo
            |--------------------------------------------------------------------------
            */

            $table->string('role')
                ->default('driver');

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            $table->timestamp('joined_at')
                ->nullable();

            $table->timestamp('left_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Restricciones
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'series_entry_id',
                'user_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_entry_members');
    }
};
