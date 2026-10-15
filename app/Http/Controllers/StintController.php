<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Stint;
use App\Models\Series;
use App\Models\irLap;
use App\Models\IrSession;
use App\Models\Team;
use App\Models\User;
use App\Services\Stints\StintJsonMetricsService;
use App\Services\Setup\SetupRepairService;
use App\Services\Stints\StintMetricsService;
use App\Services\Stints\StintQueryService;

class StintController extends Controller
{
/// ****************************************************************
/// ******************** FUNCION INDEX *****************************
/// ****************************************************************

    public function index()
    {
        try {

            $stints = StintQueryService::base()

                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // $stints = \App\Models\Stint::query()
            //     ->with([
            //         'user.team', // esto sí tiene sentido
            //     ])
            //     ->withCount('laps')
            //     ->withAvg('laps', 'lap_time')
            //     ->orderBy('created_at', 'desc')
            //     ->paginate(15);


        $metricsService = app(StintJsonMetricsService::class);

        $stints->getCollection()->transform(function ($stint) use ($metricsService) {
            $lapsJsonPath = storage_path(
                'app/private/telemetry/' .
                $stint->created_at->format('Y/m') .
                '/user_' . $stint->user_id .
                '/stint_' . $stint->id .
                '/stint_' . $stint->id . '_laps.json'
            );

            $lapsData = [];

            if (is_file($lapsJsonPath)) {
                $decoded = json_decode(file_get_contents($lapsJsonPath), true);

                if (is_array($decoded) && is_array($decoded['laps'] ?? null)) {
                    $lapsData = $decoded['laps'];
                }
            }

            $metrics = $metricsService->calculate($lapsData);

            $validFuel = collect($lapsData)
                ->filter(function ($lap) {
                    return is_numeric($lap['lap_time'] ?? null)
                        && (float) $lap['lap_time'] > 5
                        && is_numeric($lap['fuel_used'] ?? null);
                });

            $stint->json_lap_count = $metrics['valid_lap_count'];
            $stint->json_avg_lap = $metrics['avg_lap_107'];
            $stint->json_best_lap = $metrics['best_lap'];
            $stint->json_duration = $metrics['valid_duration'];
            $stint->json_avg_fuel = $validFuel->isNotEmpty()
                ? $validFuel->avg('fuel_used')
                : null;

            return $stint;
        });


            return view('stints.index', compact('stints'));

        } catch (\Exception $e) {

            \Log::error("Error en Index Stints: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error cargando stints',
                'error_debug' => $e->getMessage()
            ], 500);
        }
    }
/// ****************************************************************
/// ******************** FUNCION SHOW ******************************
/// ****************************************************************


    public function show(Stint $stint)
    {
        // StintController@show

        $fromSeries = request('from_series');

        $series = null;

        if ($fromSeries) {

            $series = Series::find($fromSeries);

        }

        $stint->load([
            'user.team',
            'session',       // ✔ ahora funciona
            'track',
            'laps.sectors'
        ]);

        // 🔥 cálculos
        $laps = $stint->laps;


        // Vueltas registradas en el JSON procesado del IBT.
        $stintLapsCount = null;
        $lapsJson = null;
        $stintDate = $stint->created_at;

        if ($stintDate) {
            $stintDirectory = storage_path(
                'app/private/telemetry/' .
                $stintDate->format('Y/m') .
                '/user_' . $stint->user_id .
                '/stint_' . $stint->id
            );

            $lapsJsonPath = $stintDirectory .
                '/stint_' . $stint->id . '_laps.json';

            $sessionJsonPath = $stintDirectory . '/session.json';
            $sessionJson = null;

            if (is_file($sessionJsonPath)) {
                $decodedSession = json_decode(
                    file_get_contents($sessionJsonPath),
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    && is_array($decodedSession)
                ) {
                    $sessionJson = $decodedSession;
                }
            }

            // =========================================================
            // CONDICIONES METEOROLÓGICAS DE LA SESIÓN
            // =========================================================

            $weather = $sessionJson['conditions'] ?? [];



            $lapsJson = null;
            $stintLapsCount = null;
            $lapsJson = null;
            $jsonLapsByLap = [];

            if (is_file($lapsJsonPath)) {
                $decodedLaps = json_decode(
                    file_get_contents($lapsJsonPath),
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    && isset($decodedLaps['laps'])
                    && is_array($decodedLaps['laps'])
                ) {
                    $lapsJson = $decodedLaps;
                    $stintLapsCount = count($lapsJson['laps']);

                    foreach ($lapsJson['laps'] as $jsonLap) {
                        if (
                            !is_array($jsonLap)
                            || !isset($jsonLap['lap'])
                        ) {
                            continue;
                        }

                        $jsonLapsByLap[(string) $jsonLap['lap']] = $jsonLap;
                    }
                }
            }



            if (is_file($lapsJsonPath)) {
                $lapsJson = json_decode(
                    file_get_contents($lapsJsonPath),
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE &&
                    isset($lapsJson['laps']) &&
                    is_array($lapsJson['laps'])
                ) {
                    $stintLapsCount = count($lapsJson['laps']);
                }
            }
        }



        // =========================================================
        // MÉTRICAS DE RITMO DESDE EL JSON DEL IBT
        // =========================================================

        $lapMetrics = app(StintJsonMetricsService::class)->calculate(
            $lapsJson['laps'] ?? []
        );

        $jsonAvgLap107 = $lapMetrics['avg_lap_107'];
        $jsonBestLap = $lapMetrics['best_lap'];
        $jsonDuration = $lapMetrics['valid_duration'];
        // --- Cálculos de Rendimiento ---
        $metrics = StintMetricsService::build($stint);

        $wear = $metrics['wear'];
        $temps = $metrics['temps'];
        // $stint->avg_lap = $laps->avg('lap_time');
        // $stint->bestlap = $laps->min('lap_time');
        // // Si en la tabla ir_laps el campo es 'fuel_consumed'
        // $stint->avg_fuel = $laps->avg('fuel_consumed');
        // // --- Cálculos de Clima (Promedios del Stint) ---
        // // Calculamos el promedio de lo que realmente ocurrió en las vueltas
        // $stint->avg_track_temp = $laps->avg('track_temp');
        // $stint->avg_air_temp = $laps->avg('air_temp');
        // $stint->avg_wind_speed = $laps->avg('wind_speed');

        // =========================
        // 🟥 INCIDENTES STINT
        // =========================

        $stint->total_incidents = $laps->sum('incident_count');
        $stint->total_offtracks = $laps->sum('offtrack_count');

        $stint->valid_laps = $laps->where('is_valid_lap', true)->count();
        $stint->invalid_laps = $laps->where('is_valid_lap', false)->count();

        // Para el cielo y estado de pista en el resumen, solemos usar el último registrado o el más frecuente
        $stint->current_sky = $laps
            ->pluck('sky')
            ->filter(fn($v) => $v !== null)
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        $stint->current_track_state = $laps
            ->pluck('track_state')
            ->filter(fn($v) => $v !== null)
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();
        // (Tus variables de neumáticos se mantienen igual por ahora)
        $setsUsed = 0;
        $degradationBySet = [];
        $totalWear = null;
        $worstTyre = null;

        $telemetry = $stint->tyres;



        $start = $telemetry->firstWhere('snapshot_type', 'start');
        $end   = $telemetry->firstWhere('snapshot_type', 'end');

        // =========================
        // 🔴 WEAR (DELTA REAL)
        // =========================
        $wear = null;

        if ($start && $end) {
            $wear = [
                'fl' => round(($start->wear_fl - $end->wear_fl) * 100, 1),
                'fr' => round(($start->wear_fr - $end->wear_fr) * 100, 1),
                'rl' => round(($start->wear_rl - $end->wear_rl) * 100, 1),
                'rr' => round(($start->wear_rr - $end->wear_rr) * 100, 1),
            ];
        }

        // =========================
        // 🔵 TEMPS (usar END o MID)
        // =========================
        $temps = null;

        if ($end) {
            $temps = [
                'fl' => $end->temp_fl,
                'fr' => $end->temp_fr,
                'rl' => $end->temp_rl,
                'rr' => $end->temp_rr,
            ];
        }

        return view('stints.show', compact(
        'stint',
        'sessionJson',
        'weather',
        'laps',
        'stintLapsCount',
        'lapsJson',
        'jsonLapsByLap',
        'jsonAvgLap107',
        'jsonBestLap',
        'jsonDuration',
        'lapMetrics',
        'setsUsed',
        'degradationBySet',
        'totalWear',
        'metrics',
        'series',
        'wear',
        'temps',
        'worstTyre'
    ));
    }


    public function ShowAnalysis(Stint $stint)
    {
        $stint->load('tyres');

        // =========================================================
        // VUELTAS DESDE EL JSON
        // =========================================================

        $laps = collect();

        $stintDate = $stint->created_at;

        if ($stintDate && $stint->user_id && $stint->id) {
            $lapsJsonPath = storage_path(
                'app/private/telemetry/' .
                $stintDate->format('Y/m') .
                '/user_' . $stint->user_id .
                '/stint_' . $stint->id .
                '/stint_' . $stint->id . '_laps.json'
            );

            if (is_file($lapsJsonPath)) {
                $decoded = json_decode(
                    file_get_contents($lapsJsonPath),
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    && isset($decoded['laps'])
                    && is_array($decoded['laps'])
                ) {
                    $laps = collect($decoded['laps'])
                        ->filter(fn ($lap) =>
                            is_array($lap)
                            && is_numeric($lap['lap_time'] ?? null)
                            && (float) $lap['lap_time'] > 5
                            && !($lap['is_pit_lap'] ?? false)
                        )
                        ->map(fn ($lap) => (object) $lap)
                        ->values();
                }
            }
        }

        // =========================================================
        // MÉTRICAS DE RITMO
        // =========================================================

        $bestLap = $laps->min('lap_time');

        $pace = null;

        if ($laps->count() >= 3) {
            $sorted = $laps->sortBy('lap_time')->values();

            $topCount = max(
                3,
                (int) ($laps->count() * 0.3)
            );

            $pace = $sorted->take($topCount)->avg('lap_time');
        }

        // =========================================================
        // CONSISTENCIA
        // =========================================================

        $consistency = null;

        if ($pace !== null && $laps->count() >= 5) {
            $variance = $laps->avg(
                fn ($lap) => pow($lap->lap_time - $pace, 2)
            );

            $consistency = sqrt($variance);
        }

        // =========================================================
        // VENTANA COMPETITIVA: 103 % DEL RITMO OBJETIVO
        // =========================================================

        $competitiveEndLap = null;

        if ($pace !== null) {
            $threshold = $pace * 1.03;

            foreach ($laps as $lap) {
                if ($lap->lap_time > $threshold) {
                    $competitiveEndLap = ($lap->lap ?? 0) - 1;
                    break;
                }
            }

            if ($competitiveEndLap === null && $laps->isNotEmpty()) {
                $competitiveEndLap = $laps->last()->lap ?? null;
            }
        }

        // =========================================================
        // NEUMÁTICOS: SE MANTIENE LA FUENTE ACTUAL (DB)
        // =========================================================

        $telemetry = $stint->tyres;

        $start = $telemetry->firstWhere('snapshot_type', 'start');
        $end = $telemetry->firstWhere('snapshot_type', 'end');

        $wear = null;
        $temps = null;
        $tempDelta = null;

        if ($start && $end) {
            $wear = [
                'fl' => round(($start->wear_fl - $end->wear_fl) * 100, 1),
                'fr' => round(($start->wear_fr - $end->wear_fr) * 100, 1),
                'rl' => round(($start->wear_rl - $end->wear_rl) * 100, 1),
                'rr' => round(($start->wear_rr - $end->wear_rr) * 100, 1),
            ];

            $temps = [
                'fl' => round($end->temp_fl, 1),
                'fr' => round($end->temp_fr, 1),
                'rl' => round($end->temp_rl, 1),
                'rr' => round($end->temp_rr, 1),
            ];

            $tempDelta = [
                'fl' => round($end->temp_fl - $start->temp_fl, 1),
                'fr' => round($end->temp_fr - $start->temp_fr, 1),
                'rl' => round($end->temp_rl - $start->temp_rl, 1),
                'rr' => round($end->temp_rr - $start->temp_rr, 1),
            ];
        }

        return view('stints.partials.analysis', [
            'bestlap' => $bestLap,
            'stint' => $stint,
            'laps' => $laps,
            'pace' => $pace,
            'consistency' => $consistency,
            'competitiveEndLap' => $competitiveEndLap,
            'wear' => $wear,
            'temps' => $temps,
            'tempDelta' => $tempDelta,
        ]);
    }



    public function repairSetup(Stint $stint)
        {
            $result = SetupRepairService::repair($stint);

            dd($result);
        }

    public function repairIbt(Stint $stint)
    {
        try {
            $result = app(
                \App\Services\Ibt\IbtRepairService::class
            )->repair($stint);

            \Log::info('IBT REPAIR REQUESTED', [
                'stint_id' => $stint->id,
                'result' => $result,
            ]);

            return redirect()
                ->route('stints.show', $stint)
                ->with(
                    'success',
                    'Solicitud de reprocesamiento IBT enviada correctamente.'
                );

        } catch (\Throwable $e) {
            \Log::error('IBT REPAIR FAILED', [
                'stint_id' => $stint->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('stints.show', $stint)
                ->with(
                    'error',
                    'No se pudo reprocesar el IBT: '
                    . $e->getMessage()
                );
        }
    }


    public function repairIbtRaw(Stint $stint)
    {
        try {
            $result = app(
                \App\Services\Ibt\IbtRawRepairService::class
            )->repair($stint);

            \Log::info('IBT RAW REPAIR REQUESTED', [
                'stint_id' => $stint->id,
                'result' => $result,
            ]);

            return redirect()
                ->route('stints.show', $stint)
                ->with(
                    'success',
                    'Exportación RAW completada. Registros: '
                    . ($result['records'] ?? 0)
                );

        } catch (\Throwable $e) {
            \Log::error('IBT RAW REPAIR FAILED', [
                'stint_id' => $stint->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('stints.show', $stint)
                ->with(
                    'error',
                    'No se pudo exportar el IBT en RAW: '
                    . $e->getMessage()
                );
        }
    }




}
