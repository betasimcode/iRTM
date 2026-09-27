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

            $table->string(
                'setup_file_path'
            )->nullable();
        
            $table->string(
                'setup_file_hash'
            )->nullable();
        
            $table->unsignedBigInteger(
                'setup_file_size'
            )->nullable();
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
