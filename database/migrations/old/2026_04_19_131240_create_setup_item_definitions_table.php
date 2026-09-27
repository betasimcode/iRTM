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
        Schema::create('setup_item_definitions', function (Blueprint $table) {
            $table->id();
        
            $table->string('raw_key')->unique();   // clave cruda completa
            $table->string('label')->nullable();   // nombre bonito
        
            $table->string('zone');                // fl_tyre, fl_susp, etc
        
            $table->string('group')->nullable();   // SUSP, TYRE, CHASSIS...
            $table->string('position')->nullable();// FL, FR, RL, RR
            $table->string('metric')->nullable();  // SPRING, CAMBER...
        
            $table->string('status')->default('pending'); // pending | mapped
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setup_item_definitions');
    }
};
