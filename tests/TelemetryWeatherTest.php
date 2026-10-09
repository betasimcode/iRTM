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

$variables = [
    'TrackWetness',
    'Skies',
    'WindVel',
    'WindDir',
    'RelativeHumidity',
    'Precipitation',
    'WeatherDeclaredWet',
];

$laps = $processor->extractLaps($ibt);

$lapTimes = [];

foreach ($laps as $lap) {
    $lapNumber = (int) $lap['lap'];
    $lapTimes[$lapNumber] = (float) $lap['lap_time'];
}

echo PHP_EOL;
echo "======================================================================" . PHP_EOL;
echo " WEATHER AVERAGE POR VUELTA" . PHP_EOL;
echo "======================================================================" . PHP_EOL;

foreach ($ranges as $lapNumber => $range) {

    $startRecord = $range[0];
    $endRecord = $range[1];

    $windVelSum = 0.0;
    $windVelCount = 0;

    $windDirSum = 0.0;
    $windDirCount = 0;

    $humiditySum = 0.0;
    $humidityCount = 0;

    $trackWetness = null;
    $skies = null;
    $precipitation = null;
    $weatherDeclaredWet = null;

    $sampleCount = 0;

    foreach (
        $processor->streamRecordsRange(
            $ibt,
            $startRecord,
            $endRecord,
            $variables
        ) as $record
    ) {

        $sampleCount++;

        if (
            isset($record['WindVel']) &&
            is_numeric($record['WindVel'])
        ) {
            $windVelSum += (float) $record['WindVel'];
            $windVelCount++;
        }

        if (
            isset($record['WindDir']) &&
            is_numeric($record['WindDir'])
        ) {
            $windDirSum += (float) $record['WindDir'];
            $windDirCount++;
        }

        if (
            isset($record['RelativeHumidity']) &&
            is_numeric($record['RelativeHumidity'])
        ) {
            $humiditySum += (float) $record['RelativeHumidity'];
            $humidityCount++;
        }

        if ($trackWetness === null) {
            $trackWetness = $record['TrackWetness'] ?? null;
        }

        if ($skies === null) {
            $skies = $record['Skies'] ?? null;
        }

        if ($precipitation === null) {
            $precipitation = $record['Precipitation'] ?? null;
        }

        if ($weatherDeclaredWet === null) {
            $weatherDeclaredWet = $record['WeatherDeclaredWet'] ?? null;
        }
    }

    $windVelAverage = null;

    if ($windVelCount > 0) {
        $windVelAverage = $windVelSum / $windVelCount;
    }

    $windDirAverage = null;

    if ($windDirCount > 0) {
        $windDirAverage = $windDirSum / $windDirCount;
    }

    $humidityAverage = null;

    if ($humidityCount > 0) {
        $humidityAverage = $humiditySum / $humidityCount;
    }

    echo PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;
    echo "LAP {$lapNumber}" . PHP_EOL;
    echo "---------------------------------------------------------------------" . PHP_EOL;

    echo "Records             : " . $sampleCount . PHP_EOL;

    if (isset($lapTimes[$lapNumber])) {
        echo "Lap time            : " . $lapTimes[$lapNumber] . " s" . PHP_EOL;
    } else {
        echo "Lap time            : NULL" . PHP_EOL;
    }

    echo "TrackWetness        : ";
    var_export($trackWetness);
    echo PHP_EOL;

    echo "Skies               : ";
    var_export($skies);
    echo PHP_EOL;

    echo "WindVel average     : ";
    var_export($windVelAverage);
    echo PHP_EOL;

    echo "WindDir average rad : ";
    var_export($windDirAverage);
    echo PHP_EOL;

    echo "Humidity average    : ";
    var_export($humidityAverage);
    echo PHP_EOL;

    echo "Precipitation       : ";
    var_export($precipitation);
    echo PHP_EOL;

    echo "WeatherDeclaredWet  : ";
    var_export($weatherDeclaredWet);
    echo PHP_EOL;
}

echo PHP_EOL;
echo "======================================================================" . PHP_EOL;
echo " FIN" . PHP_EOL;
echo "======================================================================" . PHP_EOL;
