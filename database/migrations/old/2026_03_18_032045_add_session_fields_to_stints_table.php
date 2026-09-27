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

            // relación
            $table->foreignId('ir_session_id')
                ->nullable()
                ->constrained('ir_sessions')
                ->nullOnDelete();

            // snapshot del stint
            $table->timestamp('server_time_in')->nullable();
            $table->timestamp('server_time_out')->nullable();

            $table->float('session_fastest_lap')->nullable();
            $table->string('session_fastest_driver')->nullable();
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
