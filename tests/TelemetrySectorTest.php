
<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$processor = app(IbtProcessor::class);

$ibt = 'B:\laragon\www\iracing-manager-mrt\storage\app\private\telemetry\2026\10\user_1\stint_1046\stint_1046.ibt';

if (!is_file($ibt)) {
    echo "ERROR: IBT no encontrado: {$ibt}\n";
    exit(1);
}

echo "========================================\n";
echo " iRTeam Manager - Sector Validation Test\n";
echo "========================================\n\n";

echo "IBT: {$ibt}\n";
echo "Tamaño: " . filesize($ibt) . " bytes\n\n";

$variables = [
    'SessionTime',
    'Lap',
    'LapDistPct',
    'LapLastLapTime',
];

$sectorBoundaries = [
    0.000000,
    0.238799,
    0.417892,
    0.644500,
    0.778207,
    1.000000,
];

$firstSectorBoundary = $sectorBoundaries[1];
$lastSectorBoundary = $sectorBoundaries[count($sectorBoundaries) - 2];

$previous = null;
$crossings = [];
$finishTimes = [];
$officialLapTimes = [];

foreach ($processor->streamRecords($ibt, $variables) as $index => $record) {
    $lap = (int) ($record['Lap'] ?? 0);
    $dist = (float) ($record['LapDistPct'] ?? 0);
    $time = (float) ($record['SessionTime'] ?? 0);
    $lastLapTime = (float) ($record['LapLastLapTime'] ?? 0);

    if ($previous === null) {
        $previous = [
            'lap' => $lap,
            'dist' => $dist,
            'time' => $time,
            'index' => $index,
        ];
        continue;
    }

    $prevLap = $previous['lap'];
    $prevDist = $previous['dist'];
    $prevTime = $previous['time'];
    $prevIndex = $previous['index'];

    $timeDelta = $time - $prevTime;

// Evitar muestras inválidas.
// LapDistPct puede superar ligeramente 1.0 en el cruce de meta.
$isFinishResetCandidate =
    $prevDist >= $lastSectorBoundary &&
    $dist <= $firstSectorBoundary &&
    $dist < $prevDist;

if (
    $timeDelta <= 0 ||
    (
        !$isFinishResetCandidate &&
        (
            $dist < 0 ||
            $dist > 1 ||
            $prevDist < 0 ||
            $prevDist > 1
        )
    )
) {
    $previous = [
        'lap' => $lap,
        'dist' => $dist,
        'time' => $time,
        'index' => $index,
    ];
    continue;
}

    /*
     * Cruces de sectores interiores:
     * únicamente cuando el avance pertenece a la misma vuelta.
     */
    if ($lap > 0 && $lap === $prevLap && $dist >= $prevDist) {
        for ($sector = 1; $sector < count($sectorBoundaries) - 1; $sector++) {
            $boundary = $sectorBoundaries[$sector];

            if (
                $prevDist < $boundary &&
                $dist >= $boundary &&
                !isset($crossings[$lap][$sector])
            ) {
                $distanceDelta = $dist - $prevDist;

                if ($distanceDelta <= 0) {
                    continue;
                }

                $ratio = ($boundary - $prevDist) / $distanceDelta;
                $crossingTime = $prevTime + ($timeDelta * $ratio);

                $crossings[$lap][$sector] = $crossingTime;

                echo sprintf(
                    "Vuelta %d | S%d | %.6f s absolutos | registros %d -> %d\n",
                    $lap,
                    $sector,
                    $crossingTime,
                    $prevIndex,
                    $index
                );
            }
        }
    }

    /*
     * Cruce de meta:
     * LapDistPct vuelve a un valor pequeño.
     * La meta se atribuye a la vuelta anterior.
     */
   $lapChanged = $lap !== $prevLap;

$distanceReset =
    $prevDist >= $lastSectorBoundary &&
    $dist <= $firstSectorBoundary &&
    $dist < $prevDist;

$isFinishCrossing = $prevLap > 0 && $distanceReset;

if ($lapChanged || $distanceReset) {
    echo sprintf(
    "DIAG OFICIAL | Lap anterior=%d | Lap actual=%d"
    . " | LapLastLapTime=%.6f"
    . " | SessionTime=%.6f\n",
    $prevLap,
    $lap,
    $lastLapTime,
    $time
);
}


    if ($isFinishCrossing && !isset($finishTimes[$prevLap])) {
        $distanceDelta = (1.0 - $prevDist) + $dist;

        if ($distanceDelta > 0) {
            $ratio = (1.0 - $prevDist) / $distanceDelta;
            $crossingTime = $prevTime + ($timeDelta * $ratio);

            $finishTimes[$prevLap] = $crossingTime;

            // LapLastLapTime es una referencia del IBT,
            // no un tiempo reconstruido por este test.
            if ($lastLapTime > 0 && $lap >= 3) {
                $officialLapTimes[$lap - 2] = $lastLapTime;
            }

            $crossings[$prevLap][5] = $crossingTime;

            echo sprintf(
                "Vuelta %d | META | %.6f s absolutos | registros %d -> %d\n",
                $prevLap,
                $crossingTime,
                $prevIndex,
                $index
            );
        }
    }

    $lapChanged = $lap > $prevLap;



    $previous = [
        'lap' => $lap,
        'dist' => $dist,
        'time' => $time,
        'index' => $index,
    ];
}



/*
 * Informe de duraciones.
 */
echo "\n========================================\n";
echo " VALIDACION DE VUELTAS Y SECTORES\n";
echo "========================================\n\n";

ksort($crossings);
ksort($finishTimes);

$lapNumbers = array_keys($crossings);

foreach ($lapNumbers as $lap) {
    echo "----------------------------------------\n";
    echo "VUELTA {$lap}\n";
    echo "----------------------------------------\n";

    $sectorTimes = [];
    $complete = true;

    /*
     * S1 necesita el final de la vuelta anterior como inicio.
     */
    if (isset($finishTimes[$lap - 1], $crossings[$lap][1])) {
        $sectorTimes[1] =
            $crossings[$lap][1] - $finishTimes[$lap - 1];
    } else {
        $complete = false;
    }

    // S2, S3 y S4: diferencia entre cruces consecutivos.
    for ($sector = 2; $sector <= 4; $sector++) {
        if (
            isset(
                $crossings[$lap][$sector - 1],
                $crossings[$lap][$sector]
            )
        ) {
            $sectorTimes[$sector] =
                $crossings[$lap][$sector] -
                $crossings[$lap][$sector - 1];
        } else {
            $complete = false;
        }
    }

    // S5: desde S4 hasta meta.
    if (isset($crossings[$lap][4], $finishTimes[$lap])) {
        $sectorTimes[5] =
            $finishTimes[$lap] -
            $crossings[$lap][4];
    } else {
        $complete = false;
    }

    for ($sector = 1; $sector <= 5; $sector++) {
        if (isset($sectorTimes[$sector])) {
            echo sprintf(
                "S%d: %9.6f s\n",
                $sector,
                $sectorTimes[$sector]
            );
        } else {
            echo "S{$sector}: SIN DATOS SUFICIENTES\n";
        }
    }

    if (!$complete) {
        echo "TOTAL: no calculable; falta algún cruce.\n";
        echo "\n";
        continue;
    }

    $sectorSum = array_sum($sectorTimes);

    echo sprintf(
        "SUMA SECTORES: %9.6f s\n",
        $sectorSum
    );

    /*
     * Duración reconstruida entre metas consecutivas.
     */
    if (isset($finishTimes[$lap - 1], $finishTimes[$lap])) {
        $reconstructedLap =
            $finishTimes[$lap] - $finishTimes[$lap - 1];

        echo sprintf(
            "DURACION ENTRE METAS: %9.6f s\n",
            $reconstructedLap
        );

        echo sprintf(
            "DIFERENCIA SUMA / METAS: %+.6f s\n",
            $sectorSum - $reconstructedLap
        );
    } else {
        $reconstructedLap = null;
        echo "DURACION ENTRE METAS: no disponible\n";
    }

    if (isset($officialLapTimes[$lap])) {
        $reference = $officialLapTimes[$lap];

        echo sprintf(
            "LapLastLapTime IBT: %9.6f s\n",
            $reference
        );

        if ($reconstructedLap !== null) {
            echo sprintf(
                "DIFERENCIA RECONSTRUIDA / IBT: %+.6f s\n",
                $reconstructedLap - $reference
            );
        }
    } else {
        echo "LapLastLapTime IBT: no disponible en la muestra de meta\n";
    }

    echo "\n";
}

echo "========================================\n";
echo " FIN\n";
echo "========================================\n";
