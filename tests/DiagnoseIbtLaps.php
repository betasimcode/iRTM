
<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stintId = (int) ($argv[1] ?? 1046);

$basePath = storage_path(
    'app/private/telemetry/2026/10/user_1'
);

$stintFolder = "{$basePath}/stint_{$stintId}";
$ibtPath = "{$stintFolder}/stint_{$stintId}.ibt";

if (!is_file($ibtPath)) {
    fwrite(STDERR, "IBT no encontrado: {$ibtPath}\n");
    exit(1);
}

$processor = app(IbtProcessor::class);
$laps = $processor->extractLaps($ibtPath);

echo "========================================\n";
echo " DIAGNÓSTICO DE VUELTAS IBT\n";
echo "========================================\n";
echo "Stint: {$stintId}\n";
echo "IBT: {$ibtPath}\n\n";

printf(
    "%-8s %-14s %-8s %-8s %-14s %-14s\n",
    'VUELTA',
    'TIEMPO',
    'SOURCE',
    'COMPLETED',
    'TIME_INDEX',
    'SESSION_TIME'
);

foreach ($laps as $lap) {
    printf(
        "%-8s %-14s %-8s %-8s %-14s %-14s\n",
        $lap['lap'] ?? '-',
        number_format((float) ($lap['lap_time'] ?? 0), 6, '.', ''),
        $lap['source_lap'] ?? '-',
        $lap['source_completed'] ?? '-',
        $lap['time_index'] ?? '-',
        number_format((float) ($lap['session_time'] ?? 0), 6, '.', '')
    );
}

echo "\nTotal de vueltas extraídas: " . count($laps) . "\n";
