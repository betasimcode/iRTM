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
        Schema::table('ir_stints', function (Blueprint $table) {
            $table->string('session_phase')->nullable()->after('session_id');
        });
    }

    public function down(): void
    {

}

