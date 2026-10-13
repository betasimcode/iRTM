<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ir_stints', function (Blueprint $table) {
            $table->string('status', 20)
                ->default('pending')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('ir_stints', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
