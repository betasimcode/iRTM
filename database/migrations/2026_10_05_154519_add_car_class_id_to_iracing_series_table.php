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
        Schema::table('iracing_series', function (Blueprint $table) {
            $table->unsignedInteger('car_class_id')
                ->nullable()
                ->after('iracing_series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iracing_series', function (Blueprint $table) {
            $table->dropColumn('car_class_id');
        });
    }
};
