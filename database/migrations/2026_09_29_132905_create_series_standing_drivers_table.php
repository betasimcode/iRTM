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
        Schema::create('series_standing_drivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_standing_id')
                ->constrained('series_standings')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('cust_id');

            $table->unsignedInteger('rank');

            $table->unsignedSmallInteger('division')->nullable();

            $table->string('display_name', 100);

            $table->string('country_code', 8)->nullable();

            $table->decimal('points', 10, 3)->default(0);

            $table->decimal('raw_points', 10, 3)->nullable();

            $table->smallInteger('week_dropped')->nullable();

            $table->unsignedSmallInteger('weeks_counted')->default(0);

            $table->unsignedInteger('starts')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('top5')->default(0);
            $table->unsignedInteger('top25_percent')->default(0);
            $table->unsignedInteger('poles')->default(0);

            $table->decimal('avg_start_position', 8, 3)->nullable();
            $table->decimal('avg_finish_position', 8, 3)->nullable();
            $table->decimal('avg_field_size', 8, 3)->nullable();

            $table->unsignedInteger('laps')->default(0);
            $table->unsignedInteger('laps_led')->default(0);
            $table->unsignedInteger('incidents')->default(0);

            $table->unsignedInteger('irating')->nullable();

            $table->unsignedSmallInteger('license_category_id')->nullable();
            $table->unsignedSmallInteger('license_level')->nullable();

            $table->decimal('safety_rating', 5, 2)->nullable();

            $table->string('license_color', 12)->nullable();

            $table->json('helmet')->nullable();

            $table->timestamps();

            $table->unique(
                ['series_standing_id', 'cust_id'],
                'standing_driver_unique'
            );

            $table->index(
                ['cust_id', 'series_standing_id'],
                'standing_driver_customer_index'
            );

            $table->index(
                ['series_standing_id', 'rank'],
                'standing_driver_rank_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_standing_drivers');
    }
};
