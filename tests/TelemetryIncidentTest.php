<?php

use App\Services\Ibt\IbtProcessor;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)
    ->bootstrap();

$processor = app(IbtProcessor::class);

$ibt = 'B:\laragon\www\iracing-manager-mrt\storage\app\private\telemetry\2026\10\user_1\stint_1036.ibt';

$variables = [
    'SessionTime',
    'Lap',
    'LapDistPct',
    'PlayerIncidents',
    'PlayerCarDriverIncidentCount',
    'PlayerCarMyIncidentCount',
    'PlayerCarTeamIncidentCount',
];

echo "========================================\n";
echo " iRTeam Manager - Incident Test\n";
echo "========================================\n\n";

echo "IBT:\n";
echo $ibt . "\n\n";

echo "Buscando eventos de incidentes...\n";
echo "----------------------------------------\n\n";

$previous = [];

$events = [];

foreach (
    $processor->streamRecords(
        $ibt,
        $variables
    ) as $index => $record
) {
    foreach ([
        'PlayerIncidents',
        'PlayerCarDriverIncidentCount',
        'PlayerCarMyIncidentCount',
        'PlayerCarTeamIncidentCount',
    ] as $variable) {

        if (!array_key_exists($variable, $record)) {
            continue;
        }

        $current = $record[$variable];

        if (!array_key_exists($variable, $previous)) {
            $previous[$variable] = $current;
            continue;
        }

        if ($current == $previous[$variable]) {
            continue;
        }

        $event = [
            'record' => $index,
            'session_time' => (float) ($record['SessionTime'] ?? 0),
            'lap' => (int) ($record['Lap'] ?? 0),
            'lap_dist_pct' => (float) ($record['LapDistPct'] ?? 0),
            'variable' => $variable,
            'previous' => $previous[$variable],
            'current' => $current,
            'delta' => $current - $previous[$variable],
        ];

        $events[] = $event;

        echo sprintf(
            "Record %6d | Time %10.3f | Lap %3d | Dist %8.6f | %-36s | %s -> %s | Delta %+d\n",
            $index,
            $event['session_time'],
            $event['lap'],
            $event['lap_dist_pct'],
            $variable,
            formatValue($event['previous']),
            formatValue($event['current']),
            $event['delta']
        );

        $previous[$variable] = $current;
    }
}

echo "\n";
echo "========================================\n";
echo " RESUMEN\n";
echo "========================================\n\n";

$driverEvents = array_values(
    array_filter(
        $events,
        fn (array $event) =>
            $event['variable'] ===
            'PlayerCarDriverIncidentCount'
    )
);

echo "Eventos DriverIncidentCount: ";
echo count($driverEvents);
echo "\n\n";

foreach ($driverEvents as $number => $event) {

    echo sprintf(
        "#%d | Record %d | Time %.3f | Lap %d | Dist %.6f | %d -> %d | Delta %+d\n",
        $number + 1,
        $event['record'],
        $event['session_time'],
        $event['lap'],
        $event['lap_dist_pct'],
        $event['previous'],
        $event['current'],
        $event['delta']
    );
}

echo "\n========================================\n";
echo " COMPARACIÓN DE CONTADORES\n";
echo "========================================\n\n";

$driver = 0;
$my = 0;
$team = 0;

foreach ($events as $event) {

    switch ($event['variable']) {

        case 'PlayerCarDriverIncidentCount':
            $driver = $event['current'];
            break;

        case 'PlayerCarMyIncidentCount':
            $my = $event['current'];
            break;

        case 'PlayerCarTeamIncidentCount':
            $team = $event['current'];
            break;
    }

    if (
        $event['variable'] ===
        'PlayerCarDriverIncidentCount'
    ) {
        echo sprintf(
            "Time %.3f | Lap %d | Driver=%d | My=%d | Team=%d\n",
            $event['session_time'],
            $event['lap'],
            $driver,
            $my,
            $team
        );
    }
}

echo "\n========================================\n";
echo " FIN\n";
echo "========================================\n";


function formatValue(mixed $value): string
{
    if (is_array($value)) {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }

    if (is_float($value)) {
        return sprintf('%.6f', $value);
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    return (string) $value;
}
