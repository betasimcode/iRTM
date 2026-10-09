<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$processor = app(IbtProcessor::class);
$stintId = isset($argv[1]) ? (int) $argv[1] : 1046;

if ($stintId <= 0) {
    fwrite(STDERR, "ERROR: indica un stint_id válido.\n");
    exit(1);
}

$directory = storage_path(
    "app/private/telemetry/2026/10/user_1/stint_{$stintId}"
);

$ibt = $directory . DIRECTORY_SEPARATOR . "stint_{$stintId}.ibt";
$output = $directory . DIRECTORY_SEPARATOR . "stint_{$stintId}_laps.json";


$sessionInfoDebug = $directory . DIRECTORY_SEPARATOR . 'session-info-debug.txt';


if (!is_file($ibt)) {
    fwrite(STDERR, "ERROR: IBT no encontrado: {$ibt}\n");
    exit(1);
}



echo "========================================\n";
echo " iRTeam Manager - IBT JSON Generator\n";
echo "========================================\n\n";
echo "IBT: {$ibt}\n";
echo "Extrayendo límites de sector desde SplitTimeInfo...\n";

try {
    $boundaries = $processor->extractSectorBoundaries($ibt);
    echo 'Límites: ' . implode(', ', array_map(
        static fn (float $value): string => number_format($value, 6, '.', ''),
        $boundaries
    )) . "\n";

    echo "Generando JSON...\n";
    $payload = $processor->writeLapsJson($ibt, $output);


    $processor->writeSessionInfoDebugJson(
        $ibt,
        $sessionInfoDebug
    );

    echo "SessionInfo exportado: {$sessionInfoDebug}\n";



    echo "\nJSON generado correctamente.\n";
    echo "Archivo: {$output}\n";
    echo 'Vueltas procesadas: ' . ($payload['summary']['timed_laps'] ?? 0) . "\n";
    echo 'Fuel por vuelta: ' . ($payload['summary']['fuel_per_lap'] ?? 'N/A') . "\n";
    echo 'Incidentes: ' . ($payload['summary']['incidents'] ?? 0) . "\n\n";

    foreach ($payload['laps'] as $lap) {
        echo sprintf(
            "Lap %d | Time %.6f | Fuel start %.6f | Fuel end %.6f | Fuel used %.6f\n",
            $lap['lap'],
            $lap['lap_time'],
            $lap['fuel_start'] ?? 0,
            $lap['fuel_end'] ?? 0,
            $lap['fuel_used'] ?? 0
        );

        foreach (($lap['sectors'] ?? []) as $sector => $time) {
            echo sprintf(
                "    %s: %s\n",
                $sector,
                $time === null ? 'SIN DATOS' : number_format((float) $time, 6, '.', '') . ' s'
            );
        }

        $sectorValues = array_values($lap['sectors'] ?? []);
        $complete = count($sectorValues) > 0 && !in_array(null, $sectorValues, true);
        if ($complete) {
            $sum = array_sum($sectorValues);
            echo sprintf("    SUMA SECTORES: %.6f | VUELTA: %.6f | DIF: %+.6f s\n", $sum, $lap['lap_time'], $sum - $lap['lap_time']);
        }

    echo sprintf(
        "Lap %d | Candidate %s | Time %.6f | Fuel start %.6f | Fuel end %.6f | Fuel used %.6f\n",
        $lap['lap'],
        $lap['candidate_lap'] ?? 'NO DISPONIBLE',
        $lap['lap_time'],
        $lap['fuel_start'] ?? 0,
        $lap['fuel_end'] ?? 0,
        $lap['fuel_used'] ?? 0
    );



    }
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: {$e->getMessage()}\n");
    exit(1);
}

