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
        Schema::create('series_standing_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_standing_sync_id')
                ->constrained('series_standing_syncs')
                ->cascadeOnDelete();

            $table->foreignId('series_standing_id')
                ->constrained('series_standings')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('cust_id');

            $table->unsignedInteger('rank');

            $table->decimal('points', 10, 3)->default(0);

            $table->decimal('raw_points', 10, 3)->nullable();

            $table->unsignedInteger('irating')->nullable();

            $table->unsignedSmallInteger('weeks_counted')->default(0);

            $table->unsignedInteger('starts')->default(0);
            $table->unsignedInteger('wins')->default(0);

            $table->timestamp('captured_at');

            $table->timestamps();

            $table->unique(
                ['series_standing_sync_id', 'series_standing_id', 'cust_id'],
                'standing_snapshot_driver_unique'
            );

            $table->index(
                ['series_standing_id', 'cust_id', 'captured_at'],
                'standing_snapshot_history_index'
            );

            $table->index(
                ['series_standing_id', 'captured_at', 'rank'],
                'standing_snapshot_rank_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_standing_snapshots');
    }
};
