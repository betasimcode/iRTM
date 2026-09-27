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
            $table->string('ir_class')->nullable(); // Ej: "C", "B", "A"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iracing_series', function (Blueprint $table) {
            //
        });
    }
};
