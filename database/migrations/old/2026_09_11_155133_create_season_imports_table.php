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
        Schema::create('season_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ir_serie_id')
                ->constrained('iracing_series')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('year');

            $table->unsignedTinyInteger('season');

            $table->string('title');

            $table->unsignedSmallInteger('page_start');

            $table->unsignedSmallInteger('page_end');

            $table->timestamps();

            $table->unique([
                'ir_serie_id',
                'year',
                'season',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_imports');
    }
};
