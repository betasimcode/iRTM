<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->enum('role', ['admin','user'])
                  ->default('user')
                  ->after('password');

            $table->foreignId('team_id')
                  ->nullable()
                  ->after('role')
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['team_id']);
            $table->dropColumn(['team_id','role']);
        });
    }
};
