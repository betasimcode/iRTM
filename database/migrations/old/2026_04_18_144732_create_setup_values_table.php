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
        Schema::create('setup_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('setup_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('value');

            $table->timestamps();

            $table->index(['setup_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setup_values');
    }
};
