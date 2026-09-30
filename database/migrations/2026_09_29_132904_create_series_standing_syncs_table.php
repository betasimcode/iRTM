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
        Schema::create('series_standing_syncs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')
                ->constrained('series')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('car_class_id');

            $table->string('sync_type', 20)->default('weekly');
            // weekly | final | manual

            $table->string('status', 20)->default('running');
            // running | success | partial | failed

            $table->unsignedSmallInteger('classifications_processed')->default(0);

            $table->unsignedInteger('drivers_imported')->default(0);

            $table->text('error_message')->nullable();

            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();

            $table->timestamp('source_last_updated')->nullable();

            $table->timestamps();

            $table->index(
                ['series_id', 'car_class_id', 'started_at'],
                'standing_sync_history_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_standing_syncs');
    }
};
