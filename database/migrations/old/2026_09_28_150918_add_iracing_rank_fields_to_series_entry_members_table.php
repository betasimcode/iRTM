<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series_entry_members', function (Blueprint $table) {

            $table->unsignedBigInteger('iracing_series_id')
                ->nullable()
                ->after('user_id');

            $table->unsignedBigInteger('iracing_season_id')
                ->nullable()
                ->after('iracing_series_id');

            $table->unsignedInteger('iracing_division_id')
                ->nullable()
                ->after('iracing_season_id');

            $table->string('iracing_division_name')
                ->nullable()
                ->after('iracing_division_id');

            $table->timestamp('rank_updated_at')
                ->nullable()
                ->after('iracing_division_name');

        });
    }

    public function down(): void
    {
        Schema::table('series_entry_members', function (Blueprint $table) {

            $table->dropColumn([
                'iracing_series_id',
                'iracing_season_id',
                'iracing_division_id',
                'iracing_division_name',
                'rank_updated_at',
            ]);

        });
    }
};
