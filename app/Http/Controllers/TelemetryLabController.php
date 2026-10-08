<?php

namespace App\Http\Controllers;

use App\Models\Stint;
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
