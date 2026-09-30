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
        Schema::create('series_standings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')
                ->constrained('series')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('iracing_series_id');
            $table->unsignedBigInteger('iracing_season_id');
            $table->unsignedBigInteger('car_class_id');

            $table->string('scope', 16); // overall | division

            // -1 para Overall; 0, 1, 2... según el índice oficial
            $table->smallInteger('division_key')->default(-1);

            $table->smallInteger('division')->nullable();

            $table->smallInteger('race_week_num')->default(-1);

            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('drivers_count')->default(0);

            $table->timestamp('source_last_updated')->nullable();

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'series_id',
                    'car_class_id',
                    'scope',
                    'division_key',
                    'race_week_num',
                ],
                'standing_identity_unique'
            );

            $table->index([
                'iracing_season_id',
                'car_class_id',
                'scope',
            ], 'standing_official_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_standings');
    }
};
