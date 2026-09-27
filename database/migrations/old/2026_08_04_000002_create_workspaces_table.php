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
        Schema::create('workspaces', function (Blueprint $table) {

            $table->id();

            $table->enum('type', [
                'driver',
                'team'
            ]);

            $table->string('slug')->unique();

            /*
             * Propietario del Workspace.
             * En Driver coincide con el usuario.
             * En Team es el creador del equipo.
             */
            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Solo existe cuando type = team
             */
            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
