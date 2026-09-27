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
       Schema::table('stints', function (Blueprint $table) {
            $table->unsignedBigInteger('car_id')->nullable()->after('team_id');
            $table->unsignedBigInteger('circuit_id')->nullable()->after('car_id');

            $table->index(['car_id', 'circuit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stints', function (Blueprint $table) {
            //
        });
    }
};
