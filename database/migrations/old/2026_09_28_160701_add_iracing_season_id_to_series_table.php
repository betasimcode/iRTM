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
        Schema::table('series', function (Blueprint $table) {
            $table->unsignedBigInteger('iracing_season_id')
                ->nullable()
                ->after('iracing_series_id')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropIndex(['iracing_season_id']);
            $table->dropColumn('iracing_season_id');
        });
    }
};
