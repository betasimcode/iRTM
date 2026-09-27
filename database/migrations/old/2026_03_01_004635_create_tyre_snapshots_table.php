<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tyre_snapshots', function (Blueprint $table) {
        $table->id();

        $table->foreignId('stint_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->integer('lap_number')->nullable();

        $table->string('tyre_compound')->nullable();

        $table->float('wear_fl')->nullable();
        $table->float('wear_fr')->nullable();
        $table->float('wear_rl')->nullable();
        $table->float('wear_rr')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tyre_snapshots');
    }
};
