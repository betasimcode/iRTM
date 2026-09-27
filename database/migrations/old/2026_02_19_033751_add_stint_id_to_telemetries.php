<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telemetries', function (Blueprint $table) {
            $table->foreignId('stint_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('telemetries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stint_id');
        });
    }
};
