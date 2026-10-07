<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stint_files', function (Blueprint $table) {

            $table->id();

            $table->foreignId('stint_id')
                ->constrained('ir_stints')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('type', 50);

            $table->string('filename');

            $table->string('filepath');

            $table->unsignedBigInteger('filesize')->nullable();

            $table->string('filehash', 64)->nullable();

            $table->timestamps();

            $table->unique([
                'stint_id',
                'type'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stint_files');
    }
};
