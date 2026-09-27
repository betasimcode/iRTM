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
        Schema::create('ir_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('subsession_id')->unique();
            $table->unsignedBigInteger('session_id')->nullable();

            $table->string('track')->nullable();
            $table->unsignedBigInteger('track_id')->nullable();
            $table->string('session_type')->nullable();

            $table->integer('sof')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
            }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ir_sessions');
    }
};
