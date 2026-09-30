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
        Schema::create('series_standing_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')
                ->constrained('series')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('car_class_id');

            $table->string('car_class_name')->nullable();

            $table->boolean('enabled')->default(true);

            $table->timestamps();

            $table->unique(
                ['series_id', 'car_class_id'],
                'standing_config_series_class_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_standing_configs');
    }
};
