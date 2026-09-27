<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_sectors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('circuit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('sector_number');

            $table->decimal('start_pct', 8,5);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_sectors');
    }
};
