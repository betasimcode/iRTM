<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::table('stints', function (Blueprint $table) {
$table->float('best_lap')->nullable()->after('laps');
});
}


public function down(): void
{
    Schema::table('stints', function (Blueprint $table) {
        $table->dropColumn('best_lap');
    });
}


};
