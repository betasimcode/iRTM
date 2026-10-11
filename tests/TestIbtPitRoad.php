<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$processor = app(IbtProcessor::class);

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
|
| Uso:
| php Test/TestIbtPitRoad.php 1050
|
| El ID se utiliza para localizar el IBT. Ajustar usuario/año/mes si procede.
| Este script solo lee el IBT; no modifica archivos ni datos de la aplicación.
|
*/

$stintId = isset($argv[1]) ? (int) $argv[1] : 1050;
$userId = 1;
$year = '2026';
$month = '10';

$ibtPath = storage_path(
    "app/private/telemetry/{$year}/{$month}/user_{$userId}/stint_{$stintId}/stint_{$stintId}.ibt"
);

if (!is_file($ibtPath)) {
    fwrite(STDERR, "ERROR: IBT no encontrado:\n{$ibtPath}\n");
    exit(1);
}

try {
    echo "========================================\n";
    echo " TEST IBT - ON PIT ROAD\n";
    echo "========================================\n\n";
    echo "Archivo: {$ibtPath}\n\n";

    /* 1. Verificar variables disponibles. */
    $header = $processor->readHeader($ibtPath);
    $variables = $processor->readVariables($ibtPath);
    $available = array_column($variables, 'name');

    $required = [
        'SessionTime',
        'Lap',
        'LapCompleted',
        'LapDistPct',
        'OnPitRoad',
        'PlayerCarInPitStall',
    ];

    echo "Registros IBT: {$header['session_record_count']}\n\n";

    foreach ($required as $name) {
        printf(
            "%-28s %s\n",
            $name,
            in_array($name, $available, true) ? 'OK' : 'NO DISPONIBLE'
        );
    }

    foreach ($variables as $variable) {
        if (in_array($variable['name'], ['OnPitRoad', 'PlayerCarInPitStall'], true)) {
            printf(
                "Variable BOOL: %s | type=%s | offset=%d | count=%d\n",
                $variable['name'],
                (string) ($variable['type_name'] ?? $variable['type'] ?? '?'),
                (int) $variable['offset'],
                (int) ($variable['count'] ?? 1)
            );
        }
    }

    foreach (['OnPitRoad', 'Lap', 'LapDistPct', 'SessionTime'] as $name) {
        if (!in_array($name, $available, true)) {
            throw new RuntimeException("Falta la variable necesaria: {$name}");
        }
    }

    $boolVariables = [];
    foreach ($variables as $variable) {
        if (in_array($variable['name'], ['OnPitRoad', 'PlayerCarInPitStall'], true)) {
            $boolVariables[$variable['name']] = $variable;
        }
    }

    /* 2. Inspección de los bytes originales de las variables BOOL. */
    $recordStart = $header['session_info_offset'] + $header['session_info_length'];
    $bufferLength = (int) $header['buffer_length'];
    $sampleIndexes = [1, 6090, 63519, 64319, 64320];
    $handle = fopen($ibtPath, 'rb');

    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir el IBT para inspeccionar sus bytes.');
    }

    try {
        foreach ($sampleIndexes as $index) {
            if ($index < 0 || $index >= (int) $header['session_record_count']) {
                continue;
            }

            if (fseek($handle, $recordStart + ($index * $bufferLength)) !== 0) {
                throw new RuntimeException("No se pudo posicionar en el registro {$index}.");
            }

            $buffer = fread($handle, $bufferLength);
            if ($buffer === false || strlen($buffer) !== $bufferLength) {
                throw new RuntimeException("No se pudo leer el registro {$index} completo.");
            }

            echo "\nRegistro {$index} - bytes originales\n";
            foreach ($boolVariables as $name => $variable) {
                $offset = (int) $variable['offset'];
                printf(
                    "%s | offset=%d | byte=0x%02X | decimal=%d | cuatro_bytes=%s\n",
                    $name,
                    $offset,
                    ord($buffer[$offset]),
                    ord($buffer[$offset]),
                    bin2hex(substr($buffer, $offset, 4))
                );
            }
        }
    } finally {
        fclose($handle);
    }

    /* 3. Recorrer las muestras y construir transiciones, segmentos y episodios. */
    $previous = null;
    $transitions = [];
    $pitLapSamples = [];
    $pitValues = [];
    $stallValues = [];
    $recordCount = 0;
    $lapSegments = [];
    $activeSegment = null;
    $pitEpisodes = [];
    $activePitEpisode = null;

    foreach ($processor->streamRecords($ibtPath, $required) as $index => $record) {
        $recordCount++;

        $pitValue = $record['OnPitRoad'] ?? false;
        $stallValue = $record['PlayerCarInPitStall'] ?? false;
        $pitKey = var_export($pitValue, true);
        $stallKey = var_export($stallValue, true);
        $pitValues[$pitKey] = ($pitValues[$pitKey] ?? 0) + 1;
        $stallValues[$stallKey] = ($stallValues[$stallKey] ?? 0) + 1;

        $onPitRoad = (bool) $pitValue;
        $pitStall = (bool) $stallValue;
        $lap = (int) ($record['Lap'] ?? 0);
        $completed = (int) ($record['LapCompleted'] ?? 0);

        // Algunos lectores pueden exponer el sentinel unsigned de -1.
        if ($completed === 4294967295) {
            $completed = -1;
        }

        $sample = [
            'record' => (int) $index,
            'session_time' => (float) ($record['SessionTime'] ?? 0),
            'lap' => $lap,
            'lap_completed' => $completed,
            'lap_dist_pct' => (float) ($record['LapDistPct'] ?? 0),
            'on_pit_road' => $onPitRoad,
            'pit_stall' => $pitStall,
        ];

        /* Segmentos CONTIGUOS: un cambio de Lap o LapCompleted cierra el segmento. */
        $sameSegment = $activeSegment !== null
            && $activeSegment['lap'] === $lap
            && $activeSegment['completed'] === $completed;

        if (!$sameSegment) {
            if ($activeSegment !== null) {
                $activeSegment['duration'] = $activeSegment['last_time'] - $activeSegment['first_time'];
                $lapSegments[] = $activeSegment;
            }

            $activeSegment = [
                'lap' => $lap,
                'completed' => $completed,
                'first_record' => (int) $index,
                'last_record' => (int) $index,
                'first_time' => $sample['session_time'],
                'last_time' => $sample['session_time'],
                'samples' => 0,
                'pit_road_samples' => 0,
                'pit_stall_samples' => 0,
                'first_pit_record' => null,
                'last_pit_record' => null,
                'first_pit_time' => null,
                'last_pit_time' => null,
                'min_dist' => $sample['lap_dist_pct'],
                'max_dist' => $sample['lap_dist_pct'],
            ];
        }

        $activeSegment['last_record'] = (int) $index;
        $activeSegment['last_time'] = $sample['session_time'];
        $activeSegment['samples']++;
        $activeSegment['min_dist'] = min($activeSegment['min_dist'], $sample['lap_dist_pct']);
        $activeSegment['max_dist'] = max($activeSegment['max_dist'], $sample['lap_dist_pct']);

        if ($onPitRoad) {
            $activeSegment['pit_road_samples']++;
            $activeSegment['first_pit_record'] ??= (int) $index;
            $activeSegment['first_pit_time'] ??= $sample['session_time'];
            $activeSegment['last_pit_record'] = (int) $index;
            $activeSegment['last_pit_time'] = $sample['session_time'];
            $pitLapSamples[$lap] = ($pitLapSamples[$lap] ?? 0) + 1;
        }

        if ($pitStall) {
            $activeSegment['pit_stall_samples']++;
        }

        /* Episodios de pit road, preservando el episodio hasta su salida. */
        if ($onPitRoad && $activePitEpisode === null) {
            $activePitEpisode = [
                'entry_record' => (int) $index,
                'entry_time' => $sample['session_time'],
                'entry_lap' => $lap,
                'entry_completed' => $completed,
                'entry_dist' => $sample['lap_dist_pct'],
                'stall_samples' => 0,
            ];
        }

        if ($onPitRoad && $activePitEpisode !== null && $pitStall) {
            $activePitEpisode['stall_samples']++;
        }

        if (!$onPitRoad && $activePitEpisode !== null) {
            $activePitEpisode['exit_record'] = (int) $index;
            $activePitEpisode['exit_time'] = $sample['session_time'];
            $activePitEpisode['exit_lap'] = $lap;
            $activePitEpisode['exit_completed'] = $completed;
            $activePitEpisode['exit_dist'] = $sample['lap_dist_pct'];
            $activePitEpisode['duration'] = $sample['session_time'] - $activePitEpisode['entry_time'];
            $activePitEpisode['closed'] = true;
            $pitEpisodes[] = $activePitEpisode;
            $activePitEpisode = null;
        }

        if ($previous !== null && $onPitRoad !== $previous['on_pit_road']) {
            $transition = $sample;
            $transition['event'] = $onPitRoad ? 'ENTRADA' : 'SALIDA';
            $transitions[] = $transition;
        }

        $previous = $sample;
    }

    /* Cerrar los segmentos/episodios que siguen abiertos al final del IBT. */
    if ($activeSegment !== null) {
        $activeSegment['duration'] = $activeSegment['last_time'] - $activeSegment['first_time'];
        $lapSegments[] = $activeSegment;
    }

    if ($activePitEpisode !== null) {
        $activePitEpisode['exit_record'] = null;
        $activePitEpisode['exit_time'] = null;
        $activePitEpisode['exit_lap'] = null;
        $activePitEpisode['exit_completed'] = null;
        $activePitEpisode['exit_dist'] = null;
        $activePitEpisode['duration'] = null;
        $activePitEpisode['closed'] = false;
        $pitEpisodes[] = $activePitEpisode;
    }

    /* 4. Mostrar transiciones. */
    echo "\n========================================\n";
    echo " TRANSICIONES ONPITROAD\n";
    echo "========================================\n";

    if ($transitions === []) {
        echo "No se detectaron transiciones.\n";
    } else {
        foreach ($transitions as $transition) {
            printf(
                "%-8s | Record %d | Time %.3f | Lap %d | Completed %d | Dist %.4f | Stall %s\n",
                $transition['event'],
                $transition['record'],
                $transition['session_time'],
                $transition['lap'],
                $transition['lap_completed'],
                $transition['lap_dist_pct'],
                $transition['pit_stall'] ? 'YES' : 'NO'
            );
        }
    }

    /* 5. Distribución de muestras con OnPitRoad activo. */
    echo "\n========================================\n";
    echo " MUESTRAS CON ONPITROAD ACTIVO POR LAP\n";
    echo "========================================\n";

    if ($pitLapSamples === []) {
        echo "No hay muestras con OnPitRoad activo.\n";
    } else {
        ksort($pitLapSamples, SORT_NUMERIC);
        foreach ($pitLapSamples as $lapNumber => $count) {
            printf("Lap %d: %d muestras\n", $lapNumber, $count);
        }
    }

    /* 6. Resumen por segmentos contiguos de Lap/LapCompleted. */
    echo "\n========================================\n";
    echo " SEGMENTOS CONTINUOS DE VUELTA\n";
    echo "========================================\n";

    foreach ($lapSegments as $segment) {
        printf(
            "Lap %d | Completed %d | Records %d-%d | Time %.3f-%.3f | Duration %.3fs | Samples %d | PitRoad %d | Stall %d | PitRecords %s-%s | PitTime %s-%s | Dist %.4f-%.4f\n",
            $segment['lap'],
            $segment['completed'],
            $segment['first_record'],
            $segment['last_record'],
            $segment['first_time'],
            $segment['last_time'],
            $segment['duration'],
            $segment['samples'],
            $segment['pit_road_samples'],
            $segment['pit_stall_samples'],
            $segment['first_pit_record'] === null ? '-' : (string) $segment['first_pit_record'],
            $segment['last_pit_record'] === null ? '-' : (string) $segment['last_pit_record'],
            $segment['first_pit_time'] === null ? '-' : number_format($segment['first_pit_time'], 3),
            $segment['last_pit_time'] === null ? '-' : number_format($segment['last_pit_time'], 3),
            $segment['min_dist'],
            $segment['max_dist']
        );
    }

    /* 7. Resumen de episodios completos o abiertos de pit road. */
    echo "\n========================================\n";
    echo " EPISODIOS DE PIT ROAD\n";
    echo "========================================\n";

    if ($pitEpisodes === []) {
        echo "No se detectaron episodios de pit road.\n";
    } else {
        foreach ($pitEpisodes as $episodeNumber => $episode) {
            printf(
                "Episodio %d | Entrada record %d time %.3f lap %d completed %d dist %.4f | Salida %s | Duración %s | Muestras en stall %d | Estado %s\n",
                $episodeNumber + 1,
                $episode['entry_record'],
                $episode['entry_time'],
                $episode['entry_lap'],
                $episode['entry_completed'],
                $episode['entry_dist'],
                $episode['exit_record'] === null ? '-' : sprintf(
                    'record %d time %.3f lap %d completed %d dist %.4f',
                    $episode['exit_record'],
                    $episode['exit_time'],
                    $episode['exit_lap'],
                    $episode['exit_completed'],
                    $episode['exit_dist']
                ),
                $episode['duration'] === null ? '-' : number_format($episode['duration'], 3) . 's',
                $episode['stall_samples'],
                $episode['closed'] ? 'CERRADO' : 'ABIERTO AL FINAL DEL IBT'
            );
        }
    }

    /* 8. Distribución de valores decodificados por el IbtProcessor. */
    echo "\n========================================\n";
    echo " VALORES REALES DECODIFICADOS\n";
    echo "========================================\n";
    echo "OnPitRoad:\n";
    print_r($pitValues);
    echo "PlayerCarInPitStall:\n";
    print_r($stallValues);

    echo "\nMuestras procesadas: {$recordCount}\n";
    echo "Diagnóstico terminado. No se han modificado archivos ni datos.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "\nERROR: {$e->getMessage()}\n");
    exit(1);
}
