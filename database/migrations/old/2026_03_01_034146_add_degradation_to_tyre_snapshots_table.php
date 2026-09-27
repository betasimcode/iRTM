<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tyre_snapshots', function (Blueprint $table) {
            $table->float('degradation_per_lap')->nullable()->after('wear_rr');
        });
    }

    public function down()
    {
        Schema::table('tyre_snapshots', function (Blueprint $table) {
            $table->dropColumn('degradation_per_lap');
        });
    }
};
