<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ir_stints', function (Blueprint $table) {

            $table->double('humidity')
                ->nullable()
                ->after('air_temp');

            $table->double('pressure')
                ->nullable()
                ->after('humidity');

            $table->double('air_density')
                ->nullable()
                ->after('pressure');

        });
    }

    public function down(): void
    {
        Schema::table('ir_stints', function (Blueprint $table) {

            $table->dropColumn([
                'humidity',
                'pressure',
                'air_density'
            ]);

        });
    }
};