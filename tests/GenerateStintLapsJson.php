<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)
    ->bootstrap();

$processor = app(IbtProcessor::class);

$ibt = 'B:\laragon\www\iracing-manager-mrt\storage\app\private\telemetry\2026\10\user_1\stint_1036.ibt';

$output = 'B:\laragon\www\iracing-manager-mrt\storage\app\private\telemetry\2026\10\user_1\stint_1036_laps.json';

echo "========================================\n";
echo " iRTeam Manager - IBT JSON Generator\n";
echo "========================================\n\n";

echo "IBT:\n";
echo $ibt . "\n\n";

echo "Procesando...\n";

$payload = $processor->writeLapsJson(
    $ibt,
    $output
);

echo "\nJSON generado correctamente.\n\n";

echo "Archivo:\n";
echo $output . "\n\n";

echo "Vueltas procesadas: ";
echo $payload['summary']['timed_laps'] ?? 0;
echo "\n";

echo "Fuel por vuelta: ";
echo $payload['summary']['fuel_per_lap'] ?? 'N/A';
echo "\n";

echo "Incidentes: ";
echo $payload['summary']['incidents'] ?? 0;
echo "\n\n";

foreach ($payload['laps'] as $lap) {

    echo sprintf(
        "Lap %d | Time %.6f | Fuel start %.6f | Fuel end %.6f | Fuel used %.6f\n",
        $lap['lap'],
        $lap['lap_time'],
        $lap['fuel_start'] ?? 0,
        $lap['fuel_end'] ?? 0,
        $lap['fuel_used'] ?? 0
    );

    if (!empty($lap['incidents'])) {

        foreach ($lap['incidents'] as $incident) {

            echo sprintf(
                "    INCIDENT | Count %d | Total %d | Time %.3f | Dist %.6f\n",
                $incident['count'],
                $incident['total'],
                $incident['session_time'],
                $incident['lap_dist_pct']
            );
        }
    }
}

echo "\n========================================\n";
echo " FIN\n";
echo "========================================\n";
