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
        Schema::table('setups', function (Blueprint $table) {
    $table->unsignedBigInteger('stint_id')->nullable()->after('id');

    $table->foreign('stint_id')
        ->references('id')
        ->on('stints')
        ->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setups', function (Blueprint $table) {
            //
        });
    }
};
