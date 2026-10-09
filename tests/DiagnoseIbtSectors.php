
<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stintId = (int) ($argv[1] ?? 1046);

$ibtPath = storage_path(
    "app/private/telemetry/2026/10/user_1/stint_{$stintId}/stint_{$stintId}.ibt"
);

if (!is_file($ibtPath)) {
    fwrite(STDERR, "IBT no encontrado: {$ibtPath}\n");
    exit(1);
}

$processor = app(IbtProcessor::class);

$laps = $processor->extractLaps($ibtPath);
$sectors = $processor->extractLapSectors($ibtPath);

echo "========================================\n";
echo " DIAGNÓSTICO DE SECTORES IBT\n";
echo "========================================\n\n";

foreach ($laps as $lap) {
    $lapNumber = (int) $lap['lap'];
    $lapSectors = $sectors[$lapNumber] ?? [];

    $values = [];

    for ($i = 1; $i <= 5; $i++) {
        $value = $lapSectors["S{$i}"] ?? null;
        $values[] = $value === null
            ? 'NULL'
            : number_format((float) $value, 6, '.', '');
    }

    $valid = true;
    $sum = 0.0;

    foreach ($values as $value) {
        if ($value === 'NULL') {
            $valid = false;
            break;
        }

        $sum += (float) $value;
    }

    echo "VUELTA NATIVA: {$lapNumber}\n";
    echo "TIEMPO OFICIAL: "
        . number_format((float) $lap['lap_time'], 6, '.', '')
        . "\n";

    echo "SECTORES: " . implode(' | ', $values) . "\n";

    echo "SUMA SECTORES: "
        . ($valid
            ? number_format($sum, 6, '.', '')
            : 'NO CALCULABLE')
        . "\n\n";
}
