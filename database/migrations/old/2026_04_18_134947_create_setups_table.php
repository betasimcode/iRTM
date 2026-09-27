<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setups', function (Blueprint $table) {
            $table->id();

            // 🔗 RELACIONES
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();

            // 📝 INFO
            $table->string('name');
            $table->text('description')->nullable();

            // 🔐 VISIBILIDAD
            $table->enum('visibility', ['private', 'team', 'public'])->default('private');

            // 🔧 DATA DEL SETUP
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setups');
    }
};
