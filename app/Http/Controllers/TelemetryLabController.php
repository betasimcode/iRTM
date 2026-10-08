<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use App\Models\StintFile;
use App\Services\Ibt\IbtProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TelemetryLabController extends Controller
{
    public function index(
        Request $request,
        IbtProcessor $processor
    ) {
        $stints = Stint::query()
            ->where('user_id', auth()->id())
            ->whereHas('files', function ($query) {
                $query->where('type', 'ibt');
            })
            ->with([
                'track',
                'files' => function ($query) {
                    $query->where('type', 'ibt');
                },
            ])
            ->latest('id')
            ->get();

        $selectedStint = null;
        $ibtFile = null;
        $header = null;
        $variables = [];
        $records = [];
        $laps = [];
        $lapTimingDiagnostics = [];
        $error = null;
        $transitions = [];
        $records = [];



        $startRecord = max(
            0,
            $request->integer('from', 0)
        );

        $endRecord = max(
            $startRecord,
            $request->integer('to', 29)
        );

        $selectedVariables = $request->input(
            'variables',
            [
                'SessionTime',
                'Lap',
                'LapCompleted',
                'LapDistPct',
                'LapLastLapTime',
                'LapBestLapTime',
                'OnPitRoad',
                'PlayerCarInPitStall',
                'PlayerTrackSurface',
                'PlayerCarDriverIncidentCount',
            ]
        );

        if (!is_array($selectedVariables)) {
            $selectedVariables = [];
        }

        $stintId = $request->integer('stint');

        if ($stintId) {
            $selectedStint = $stints->firstWhere(
                'id',
                $stintId
            );

            if (!$selectedStint) {
                abort(404);
            }

            $ibtFile = $selectedStint->files
                ->firstWhere('type', 'ibt');

            if (!$ibtFile) {
                $error =
                    'El stint no tiene un archivo IBT asociado.';
            } else {
                $path = Storage::disk('local')->path(
                    $ibtFile->filepath
                );

                if (!is_file($path)) {
                    $error =
                        'El archivo IBT no existe físicamente en storage.';
                } else {
                    try {
                        $header =
                            $processor->readHeader($path);

                        $variables =
                            $processor->readVariables($path);

                        $transitions =
                            $this->buildLapTransitions(
                                $processor,
                                $path
                            );

                        $laps = $processor->extractLaps(
                                $path
                            );

                        $lapTimingDiagnostics =
                            $this->buildLapTimingDiagnostics(
                                $processor,
                                $path
                            );

                        foreach (
                            $processor->streamRecordsRange(
                                $path,
                                $startRecord,
                                $endRecord,
                                $selectedVariables
                            ) as $index => $record
                        ) {
                            $records[] = [
                                'index' => $index,
                                'values' => $record,
                            ];
                        }
                    } catch (\Throwable $e) {
                        $error =
                            $e->getMessage();
                    }
                }
            }
        }

        return view(
            'telemetry.lab',
            compact(
                'stints',
                'selectedStint',
                'ibtFile',
                'header',
                'variables',
                'records',
                'laps',
                'lapTimingDiagnostics',
                'startRecord',
                'endRecord',
                'selectedVariables',
                'transitions',
                'error'
            )
        );

    }


private function extractLaps(
    IbtProcessor $processor,
    string $path
): array {
    $laps = [];

    $variables = [
        'SessionTime',
        'Lap',
        'LapCompleted',
        'LapLastLapTime',
        'LapBestLapTime',
    ];

    $pendingLaps = [];

    $previousCompleted = null;

    foreach (
        $processor->streamRecords(
            $path,
            $variables
        ) as $index => $record
    ) {
        $completed = (int) $record['LapCompleted'];

        /*
         * Detectamos exclusivamente incrementos de LapCompleted.
         *
         * Esto representa una vuelta consumida por iRacing.
         *
         * No utilizamos Lap como disparador porque puede presentar
         * transiciones transitorias, especialmente al entrar en boxes
         * o abandonar con ESC.
         */
        if (
            $previousCompleted !== null &&
            $completed > $previousCompleted
        ) {
            for (
                $lapNumber = $previousCompleted + 1;
                $lapNumber <= $completed;
                $lapNumber++
            ) {
                $pendingLaps[$lapNumber] = [
                    'lap' => $lapNumber,
                    'completed' => $completed,
                    'completion_index' => $index,
                    'lap_time' => null,
                    'record_index' => null,
                    'best_lap' => null,
                    'best_lap_time' => null,
                ];
            }
        }

        /*
         * El tiempo de la vuelta puede llegar varios ticks después
         * del incremento de LapCompleted.
         *
         * Asociamos el primer LapLastLapTime válido pendiente.
         */
        $lastLapTime = $record['LapLastLapTime'];

        if (
            $lastLapTime !== null &&
            is_numeric($lastLapTime) &&
            (float) $lastLapTime > 0
        ) {
            foreach (
                $pendingLaps as $lapNumber => &$pendingLap
            ) {
                if ($pendingLap['lap_time'] !== null) {
                    continue;
                }

                $pendingLap['lap_time'] =
                    (float) $lastLapTime;

                $pendingLap['record_index'] =
                    $index;

                $bestLapTime = $record['LapBestLapTime'];

                if (
                    $bestLapTime !== null &&
                    is_numeric($bestLapTime) &&
                    (float) $bestLapTime > 0
                ) {
                    $pendingLap['best_lap_time'] =
                        (float) $bestLapTime;
                }

                $pendingLap['best_lap'] =
                    $pendingLap['best_lap_time'] !== null
                        ? $lapNumber
                        : null;

                $laps[] = $pendingLap;

                unset($pendingLaps[$lapNumber]);

                break;
            }

            unset($pendingLap);
        }

        /*
         * El valor anterior se actualiza siempre, incluso cuando
         * iRacing hace una transición temporal hacia 0.
         *
         * Por tanto:
         *
         *   3 → 0
         *
         * no genera vuelta.
         *
         * Y posteriormente:
         *
         *   0 → 4
         *
         * tampoco genera una vuelta adicional si no existe
         * un incremento real de vuelta consumida.
         */
        $previousCompleted = $completed;
    }

    /*
     * Recalcular el mejor registro entre las vueltas realmente
     * cronometradas que hemos podido reconstruir.
     *
     * Esto evita depender exclusivamente del momento exacto en
     * que LapBestLapTime se actualizó dentro del IBT.
     */
    $bestLap = null;
    $bestLapTime = null;

    foreach ($laps as &$lap) {
        if (
            $lap['lap_time'] !== null &&
            (
                $bestLapTime === null ||
                $lap['lap_time'] < $bestLapTime
            )
        ) {
            $bestLapTime = $lap['lap_time'];
            $bestLap = $lap['lap'];
        }

        $lap['best_lap'] = $bestLap;
        $lap['best_lap_time'] = $bestLapTime;
    }

    unset($lap);

    return $laps;
}


    private function buildLapTransitions(
        IbtProcessor $processor,
        string $path
    ): array {
        $transitions = [];

        $previous = null;

        $variables = [
            'SessionTime',
            'Lap',
            'LapCompleted',
            'LapDistPct',
            'OnPitRoad',
            'PlayerCarInPitStall',
            'PlayerCarDriverIncidentCount',
            'PlayerTrackSurface',
        ];

        foreach (
            $processor->streamRecords(
                $path,
                $variables
            ) as $index => $record
        ) {

            if ($previous === null) {
                $previous = $record;
                continue;
            }

            $events = [];

            /*
            * LAP
            */
            if (
                $record['Lap'] !==
                $previous['Lap']
            ) {
                $events[] = sprintf(
                    'Lap %s → %s',
                    $previous['Lap'],
                    $record['Lap']
                );
            }

            /*
            * LAP COMPLETED
            */
            if (
                $record['LapCompleted'] !==
                $previous['LapCompleted']
            ) {
                $events[] = sprintf(
                    'LapCompleted %s → %s',
                    $previous['LapCompleted'],
                    $record['LapCompleted']
                );
            }

            /*
            * LAP DISTANCE RESET
            *
            * No tratamos cada cambio de LapDistPct
            * como transición.
            *
            * Solo nos interesa un retroceso
            * significativo, típico de un reset
            * de vuelta.
            */
            if (
                $record['LapDistPct'] <
                $previous['LapDistPct'] - 0.50
            ) {
                $events[] = sprintf(
                    'LapDistPct reset %.6f → %.6f',
                    $previous['LapDistPct'],
                    $record['LapDistPct']
                );
            }

            /*
            * PIT ROAD
            */
            if (
                $record['OnPitRoad'] !==
                $previous['OnPitRoad']
            ) {
                $events[] = sprintf(
                    'OnPitRoad %s → %s',
                    $previous['OnPitRoad'],
                    $record['OnPitRoad']
                );
            }

            /*
            * PIT STALL
            */
            if (
                $record['PlayerCarInPitStall'] !==
                $previous['PlayerCarInPitStall']
            ) {
                $events[] = sprintf(
                    'PitStall %s → %s',
                    $previous['PlayerCarInPitStall'],
                    $record['PlayerCarInPitStall']
                );
            }

            /*
            * INCIDENT COUNT
            */
            if (
                $record['PlayerCarDriverIncidentCount'] !==
                $previous['PlayerCarDriverIncidentCount']
            ) {
                $events[] = sprintf(
                    'IncidentCount %s → %s',
                    $previous['PlayerCarDriverIncidentCount'],
                    $record['PlayerCarDriverIncidentCount']
                );
            }

            /*
            * TRACK SURFACE
            *
            * Lo mostramos porque el caso 27494
            * demuestra que puede cambiar durante
            * una transición de estado.
            */
            if (
                $record['PlayerTrackSurface'] !==
                $previous['PlayerTrackSurface']
            ) {
                $events[] = sprintf(
                    'TrackSurface %s → %s',
                    $previous['PlayerTrackSurface'],
                    $record['PlayerTrackSurface']
                );
            }

            /*
            * Solo almacenamos registros que
            * realmente contienen una transición.
            */
            if (!empty($events)) {

                $transitions[] = [
                    'index' => $index,
                    'session_time' =>
                        $record['SessionTime'],
                    'lap' =>
                        $record['Lap'],
                    'lap_completed' =>
                        $record['LapCompleted'],
                    'lap_dist_pct' =>
                        $record['LapDistPct'],
                    'on_pit_road' =>
                        $record['OnPitRoad'],
                    'pit_stall' =>
                        $record['PlayerCarInPitStall'],
                    'incident_count' =>
                        $record['PlayerCarDriverIncidentCount'],
                    'track_surface' =>
                        $record['PlayerTrackSurface'],
                    'events' =>
                        $events,
                ];
            }

            $previous = $record;
        }

        return $transitions;
    }



    private function buildTimedLaps(
        IbtProcessor $processor,
        string $path
    ): array {
        $laps = [];

        $variables = [
            'SessionTime',
            'Lap',
            'LapCompleted',
            'LapLastLapTime',
            'LapBestLapTime',
            'LapBestLap',
        ];

        $previousCompleted = null;

        /*
        * Último tiempo que iRacing tenía publicado
        * en LapLastLapTime.
        */
        $knownLastLapTime = null;

        /*
        * Vuelta consumida que todavía estamos
        * esperando poder asociar a un tiempo.
        */
        $pendingLap = null;
        $pendingCompletionIndex = null;
        $pendingCompletionTime = null;

        foreach (
            $processor->streamRecords(
                $path,
                $variables
            ) as $index => $record
        ) {
            $sessionTime =
                (float) ($record['SessionTime'] ?? 0);

            $lap =
                (int) ($record['Lap'] ?? 0);

            $completed =
                (int) ($record['LapCompleted'] ?? 0);

            /*
            * iRacing puede representar -1 como
            * uint32 4294967295.
            */
            if ($completed === 4294967295) {
                $completed = -1;
            }

            $lastLapTime =
                (float) ($record['LapLastLapTime'] ?? 0);

            /*
            * Inicialización.
            */
            if ($previousCompleted === null) {
                $previousCompleted = $completed;

                $knownLastLapTime =
                    $lastLapTime > 0
                        ? $lastLapTime
                        : null;

                continue;
            }

            /*
            * ==================================================
            * 1. iRacing ha consumido una nueva vuelta
            * ==================================================
            */
            if ($completed > $previousCompleted) {

                $pendingLap = $completed;

                $pendingCompletionIndex = $index;

                $pendingCompletionTime =
                    $sessionTime;
            }

            /*
            * ==================================================
            * 2. LapLastLapTime se ha actualizado
            * ==================================================
            */
            if (
                $pendingLap !== null &&
                $lastLapTime > 0 &&
                (
                    $knownLastLapTime === null ||
                    abs(
                        $lastLapTime -
                        $knownLastLapTime
                    ) > 0.000001
                )
            ) {

                $laps[] = [
                    'lap' =>
                        $pendingLap,

                    'lap_time' =>
                        $lastLapTime,

                    'completed' =>
                        $completed,

                    'completion_index' =>
                        $pendingCompletionIndex,

                    'completion_time' =>
                        $pendingCompletionTime,

                    'record_index' =>
                        $index,

                    'session_time' =>
                        $sessionTime,

                    'best_lap' =>
                        (int) (
                            $record['LapBestLap'] ?? 0
                        ),

                    'best_lap_time' =>
                        (float) (
                            $record['LapBestLapTime'] ?? 0
                        ),
                ];

                /*
                * Ya hemos asociado el tiempo
                * con la vuelta consumida.
                */
                $pendingLap = null;

                $pendingCompletionIndex = null;

                $pendingCompletionTime = null;
            }

            /*
            * Guardamos el último valor conocido.
            */
            if ($lastLapTime > 0) {
                $knownLastLapTime =
                    $lastLapTime;
            }

            $previousCompleted =
                $completed;
        }

        return $laps;
    }


    private function buildLapTimingDiagnostics(
        IbtProcessor $processor,
        string $path
    ): array {
        $diagnostics = [];

        $variables = [
            'SessionTime',
            'Lap',
            'LapCompleted',
            'LapLastLapTime',
            'LapBestLapTime',
        ];

        $previousCompleted = null;

        $captureUntil = null;
        $captureLap = null;

        foreach (
            $processor->streamRecords(
                $path,
                $variables
            ) as $index => $record
        ) {
            $completed = (int) (
                $record['LapCompleted'] ?? 0
            );

            /*
            * iRacing puede representar -1 como
            * uint32 4294967295.
            */
            if ($completed === 4294967295) {
                $completed = -1;
            }

            /*
            * Detectamos el cambio de LapCompleted.
            */
            if (
                $previousCompleted !== null &&
                $completed !== $previousCompleted
            ) {
                /*
                * Capturamos este tick y los
                * siguientes 15 registros.
                */
                $captureUntil = $index + 15;

                $captureLap = $completed;
            }

            /*
            * Mientras estamos dentro de una ventana
            * de diagnóstico, guardamos cada registro.
            */
            if (
                $captureUntil !== null &&
                $index <= $captureUntil
            ) {
                $diagnostics[] = [
                    'index' =>
                        $index,

                    'session_time' =>
                        (float) (
                            $record['SessionTime'] ?? 0
                        ),

                    'lap' =>
                        (int) (
                            $record['Lap'] ?? 0
                        ),

                    'lap_completed' =>
                        $completed,

                    'lap_last_lap_time' =>
                        (float) (
                            $record['LapLastLapTime'] ?? 0
                        ),

                    'lap_best_lap_time' =>
                        (float) (
                            $record['LapBestLapTime'] ?? 0
                        ),

                    'trigger_lap' =>
                        $captureLap,
                ];
            }

            /*
            * Terminamos la ventana después
            * de 15 registros.
            */
            if (
                $captureUntil !== null &&
                $index >= $captureUntil
            ) {
                $captureUntil = null;
                $captureLap = null;
            }

            $previousCompleted =
                $completed;
        }

        return $diagnostics;
    }




}
