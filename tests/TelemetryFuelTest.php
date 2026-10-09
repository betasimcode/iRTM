<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$processor = app(\App\Services\Ibt\IbtProcessor::class);

$ibt = 'B:\laragon\www\iracing-manager-mrt\storage\app\private\telemetry\2026\10\user_1\stint_1041.ibt';

$ranges = [
    1 => [10179, 15772],
    2 => [15773, 21306],
    3 => [21307, 26812],
    4 => [26813, 32352],
    5 => [32353, 37889],
    6 => [37890, 43432],
    7 => [43433, 48941],
    8 => [48942, 54392],
];

$laps = $processor->extractLaps($ibt);

$lapTimes = [];

foreach ($laps as $lap) {
    $lapNumber = (int) $lap['lap'];
    $lapTimes[$lapNumber] = (float) $lap['lap_time'];
}

echo PHP_EOL;
echo "======================================================================" . PHP_EOL;
echo " FUEL POR VUELTA" . PHP_EOL;
echo "======================================================================" . PHP_EOL;

foreach ($ranges as $lapNumber => $range) {

    $startRecord = $range[0];
    $endRecord = $range[1];

    $fuelStart = null;
    $fuelEnd = null;
    $sampleCount = 0;

    foreach (
        $processor->streamRecordsRange(
            $ibt,
            $startRecord,
            $endRecord,
            ['FuelLevel']
        ) as $record
    ) {

        $fuel = $record['FuelLevel'] ?? null;

        if ($fuel === null || !is_numeric($fuel)) {
            continue;
        }

        $fuel = (float) $fuel;

        if ($fuelStart === null) {
            $fuelStart = $fuel;
        }

        $fuelEnd = $fuel;
        $sampleCount++;
    }

    $fuelUsed = null;

    if (
        $fuelStart !== null &&
        $fuelEnd !== null
    ) {
        $fuelUsed = $fuelStart - $fuelEnd;
    }

    echo PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;
    echo "LAP {$lapNumber}" . PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;

    echo "Records             : " . $sampleCount . PHP_EOL;

    if (isset($lapTimes[$lapNumber])) {
        echo "Lap time            : " . $lapTimes[$lapNumber] . " s" . PHP_EOL;
    }

    echo "Fuel start          : ";
    var_export($fuelStart);
    echo PHP_EOL;

    echo "Fuel end            : ";
    var_export($fuelEnd);
    echo PHP_EOL;

    echo "Fuel used           : ";
    var_export($fuelUsed);
    echo PHP_EOL;
}

echo PHP_EOL;
echo "======================================================================" . PHP_EOL;
echo " FIN" . PHP_EOL;
echo "======================================================================" . PHP_EOL;
