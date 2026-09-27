<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {

            // Asegurar 1:1 real
            $table->foreignId('user_id')
                  ->unique()
                  ->change();

            // Rol dentro del equipo
            $table->enum('role', ['team_owner','driver'])
                  ->default('driver')
                  ->after('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {

            $table->dropColumn('role');
            $table->dropUnique(['user_id']);
        });
    }
};
