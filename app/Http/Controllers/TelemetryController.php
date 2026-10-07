<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TyreSnapshot;
use App\Models\Team;
use App\Models\IrSession;
use App\Models\Car;
use App\Models\Circuit;
use App\Models\Telemetry;
use App\Models\Stint;
use Carbon\Carbon;
use App\Events\LapCompleted;
use App\Models\TrackSector;
use App\Models\IrSessionPhase;
use App\Models\IracingSerie;
use App\Models\StintFile;

class TelemetryController extends Controller
{

    /* ======================================================
        GUARDAR TELEMETRIA INDIVIDUAL (no crítico aún)
    ====================================================== */


public function store(Request $request)
{
    // Verificar que los datos llegaron
    Log::info('Datos recibidos:', $request->all());

    $user = User::where('api_token', $request->header('X-API-TOKEN'))->first();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    Telemetry::create([
        'user_id' => $user->id,
        'car_id'   => $request->car_id ?? null,
        'track_id' => $request->track_id ?? null,
        'lap' => $request->lap,
        'lap_time' => $request->lap_time,
        'fuel' => $request->fuel,
        'length_km' => $request->length_km,  // Verificar si llega el valor correctamente
        'track_temp' => $request->track_temp,
        'air_temp' => $request->air_temp,
        'timestamp' => Carbon::parse($request->timestamp),
    ]);

    return response()->json(['ok' => true]);
}



    /* ======================================================
        GUARDAR STINT COMPLETO (el importante)
    ====================================================== */
    public function storeStint(Request $request)
    {
        $user = User::where('api_token', $request->header('X-API-TOKEN'))->first();

        // =========================
        // VALIDAR DRIVER REGISTRADO
        // =========================

        $driverData = $request->input('driver');

        if (!$driverData || empty($driverData['iracing_user_id'])) {
            return response()->json(['error' => 'Driver data missing'], 400);
        }

        $driver = User::where(
            'iracing_user_id',
            $driverData['iracing_user_id']
        )->first();

        if ($driver) {
            $driver->logger_version = $request->app_version ?? null;
            $driver->last_logger_ping = now();
            $driver->save();
        }

        if (!$driver) {
            return response()->json([
                'error' => 'Driver not registered'
            ], 403);
        }


        if(!$user)
            return response()->json(['error'=>'Unauthorized'],401);

        $data = $request->all();

        if(!isset($data['laps']) || count($data['laps']) < 2)
            return response()->json(['error'=>'Not enough laps'],422);

        $laps = collect($data['laps'])
            ->filter(function($lap){
            return isset($lap['lap_time']) && $lap['lap_time'] > 5;
            })
            ->values();
            if($laps->count() < 2)
            return response()->json(['error'=>'Not enough valid laps'],422);



    // =========================
    // CALCULOS DEL STINT
    // =========================

    // Asegurar colección
    $laps = collect($laps);

    $session = IrSession::firstOrCreate(
        [
            'subsession_id' => $request->session['subsession_id'] ?? 0
        ],
        [
            'session_id' => $request->session['session_id'] ?? null,
            'track' => $request->session['track'] ?? null,
            'track_id' => $request->session['track_id'] ?? null,
            'session_type' => $request->session['session_type'] ?? null,
            'sof' => $request->session['sof'] ?? null,
            'started_at' => now()
        ]
    );
    $validLaps = $laps->where('lap_time', '>', 0);

    if ($validLaps->isNotEmpty()) {

        $incomingFastest = $request->stint_meta['session_fastest_lap'] ?? null;

        if ($incomingFastest) {

            if (!$session->fastest_lap || $incomingFastest < $session->fastest_lap) {

                $session->fastest_lap = $incomingFastest;
                $session->fastest_driver = $request->stint_meta['session_fastest_driver'] ?? null;

                $session->save();
            }
        }

    // 1️⃣ Excluir vueltas de pit
    $cleanLaps = $laps->filter(function ($lap) {
        return empty($lap['is_pit_lap']) || (int)$lap['is_pit_lap'] === 0;
    });

    // 2️⃣ Mejor vuelta sin pits
    $bestLap = $cleanLaps->min('lap_time');

    // 3️⃣ Límite 107%
    $limit107 = $bestLap * 1.07;

    // 4️⃣ Filtrar vueltas válidas
    $validLaps = $cleanLaps->filter(function ($lap) use ($limit107) {
        return $lap['lap_time'] <= $limit107;
    });

    // 5️⃣ Calcular medias reales
    $avgLap  = $validLaps->avg('lap_time');
    $avgFuel = $validLaps->avg('fuel_used');

    // 6️⃣ Duración real rodando
    $duration = (int) round($validLaps->sum('lap_time'));

    // 7️⃣ Timestamps originales
    $started = Carbon::parse($laps->first()['timestamp']);
    $ended   = Carbon::parse($laps->last()['timestamp']);

    // ===============================
    // MÉTRICAS AVANZADAS
    // ===============================

    // Consistency (desviación estándar)
    $lapTimes = $validLaps->pluck('lap_time');

    $variance = $lapTimes->map(function ($lap) use ($avgLap) {
        return pow($lap - $avgLap, 2);
    })->avg();

    $consistency = $variance ? sqrt($variance) : 0;


    // Degradación (últimas 3 vs primeras 3)
    $firstLaps = $validLaps->take(3);
    $lastLaps  = $validLaps->slice(-3);

    $degradation = null;

    if ($firstLaps->count() >= 2 && $lastLaps->count() >= 2) {
        $degradation = $lastLaps->avg('lap_time') - $firstLaps->avg('lap_time');
    }


    // Variabilidad consumo
    $fuelValues = $validLaps->pluck('fuel_used')->filter();

    $fuelVariance = $fuelValues->map(function ($fuel) use ($avgFuel) {
        return pow($fuel - $avgFuel, 2);
    })->avg();

        // =====================================================
        // AUTO REGISTRO COCHE (SEGURO)
        // =====================================================

        $carData = $data['car'] ?? null;

        $carName = is_array($carData)
            ? ($carData['car_name'] ?? 'Unknown')
            : $carData;

        $tankCapacity = $data['tank_capacity'] ?? null;

        $car = Car::firstOrCreate(
            ['name' => $carName],
            ['tank_capacity' => $tankCapacity]
        );

        // Actualizar tanque si viene valor nuevo
        if ($tankCapacity !== null && $car->tank_capacity != $tankCapacity) {
            $car->update([
                'tank_capacity' => $tankCapacity
            ]);
        }

        // =====================================================
        // AUTO REGISTRO CIRCUITO CON DISTANCIA
        // =====================================================

        $trackName = is_string($data['track'] ?? null)
            ? $data['track']
            : 'Unknown';
            $trackId = $data['track_id'] ?? null;
            $trackCity = $data['track_city'] ?? null;
            $trackCountry = $data['track_country'] ?? null;
        $lengthKm = null;

        if(isset($data['track_length']))
        {
            // si llega float desde Python
            if(is_numeric($data['track_length']))
            {
                $lengthKm = floatval($data['track_length']);
            }
            // si llega string tipo "1.245 km" (compatibilidad antigua)
            elseif(is_string($data['track_length']))
            {
                if(str_contains($data['track_length'],'km'))
                    $lengthKm = floatval($data['track_length']);

                if(str_contains($data['track_length'],'mi'))
                    $lengthKm = floatval($data['track_length']) * 1.60934;
            }
        }

        $circuit = Circuit::firstOrCreate(
            ['iracing_track_id' => $data['track_id'] ?? null],
            [
                'name' => $trackName,
                'city' => $data['track_city'] ?? null,
                'country' => $data['track_country'] ?? null,
                'length_km' => $lengthKm
            ]
        );

        // si ya existía pero no tenía longitud → actualizar
        if($lengthKm !== null && $circuit->length_km === null)
        {
            $circuit->update(['length_km'=>$lengthKm]);
        }


        // =========================
        // CREAR STINT
        // =========================
        $carId = null;

        if ($request->has('car.car_name')) {
            $carName = $request->input('car.car_name');
            $carId = \App\Models\Car::where('name', $carName)->value('id');
        }

        $trackId = $request->track_id ?? null;

        $stint = Stint::create([
            'user_id'        => $user->id,              // quien envía
            'driver_id'      => $user->iracing_user_id,            // piloto real
            'team_id'        => $driver->team_id,       // equipo del piloto
            'car_id'     => $carId,
            'circuit_id' => $trackId,

            // legacy (déjalos por ahora)
            'car' => is_array($request->car)
                ? $request->car['car_name']
                : $request->car,
            'track' => $data['track'] ?? 'Unknown',

            'session_type'   => $data['session_type'] ?? 'Testing',
            'laps'           => $laps->count(),
            'best_lap'       => $bestLap,
            'avg_lap'        => $avgLap,
            'avg_fuel'       => $avgFuel,
            'consistency'    => $consistency,
            'degradation'    => $degradation,
            'fuel_variance'  => $fuelVariance,
            'duration_seconds' => $duration,
            'type'           => $this->classifyStint($laps->count()),
            'started_at'     => $started,
            'ended_at'       => $ended,
            'ir_session_id' => $session->id,
            'session_fastest_lap' => $request->stint_meta['session_fastest_lap'] ?? null,
            'session_fastest_driver' => $request->stint_meta['session_fastest_driver'] ?? null,
        ]);

        // =========================
        // GUARDAR VUELTAS
        // =========================
        $carName = $request->input('car.car_name');

        $car = Car::where('name', $carName)->first();

        $carId = $car?->id;
        $trackId = $request->track_id ?? null;
        foreach($laps as $lap)
        {
            $telemetry = Telemetry::create([
                'user_id' => $user->id,
                'car_id' => $carId,
                'stint_id' => $stint->id,
                'track_id' => $trackId,
                'length_km' => $request->track_length,
                'lap' => $lap['lap'],
                'is_pit_lap' => isset($lap['is_pit_lap']) ? (int)$lap['is_pit_lap'] : 0,
                'lap_time' => $lap['lap_time'],
                'fuel' => $lap['fuel'],
                'fuel_used' => $lap['fuel_used'] ?? null,
                'timestamp' => Carbon::parse($lap['timestamp']),
            ]);
           // =========================
            // GUARDAR SECTORES
            // =========================

            if (!empty($lap['sectors']) && is_array($lap['sectors']))
            {
                foreach ($lap['sectors'] as $i => $sectorTime)
                {
                    if ($sectorTime !== null && $sectorTime > 0)
                    {
                        \App\Models\LapSector::create([
                            'telemetry_id' => $telemetry->id,
                            'sector_number' => $i + 1,
                            'sector_time' => $sectorTime
                        ]);
                    }
                }
            }

            $gap = null;

            if ($bestLap && $telemetry->lap_time) {
                $gap = round($telemetry->lap_time - $bestLap, 3);
            }

            event(new LapCompleted($telemetry, $gap, $bestLap));
        }

        // 🔥 SNAPSHOTS
            $tyreSetNumber = 1;
            $previousSnapshot = null;

            if (!empty($request->tyre_snapshots)) {

                foreach ($request->tyre_snapshots as $snapshotData) {

                    $currentWear = $snapshotData['wear_fl'] ?? null;

                    // Detectar cambio de juego (salto grande hacia arriba)
                    if ($previousSnapshot &&
                        $previousSnapshot->wear_fl !== null &&
                        $currentWear !== null &&
                        ($currentWear - $previousSnapshot->wear_fl) > 5
                    ) {
                        $tyreSetNumber++;
                    }
                    $snapshot = $stint->tyreSnapshots()->create([
                        'lap_number' => $snapshotData['lap_number'] ?? null,
                        'tyre_compound' => $snapshotData['tyre_compound'] ?? null,
                        'wear_fl' => $snapshotData['wear_fl'] ?? null,
                        'wear_fr' => $snapshotData['wear_fr'] ?? null,
                        'wear_rl' => $snapshotData['wear_rl'] ?? null,
                        'wear_rr' => $snapshotData['wear_rr'] ?? null,
                        'tyre_set_number' => $tyreSetNumber,
                    ]);

                    $degradation = null;

                    // Primer snapshot → calcular desde 100
                    if (!$previousSnapshot &&
                        $snapshot->lap_number > 0 &&
                        $snapshot->wear_fl !== null
                    ) {
                        $degradation =
                            (100 - $snapshot->wear_fl) / $snapshot->lap_number;
                    }

                    // Snapshot con anterior
                    elseif ($previousSnapshot &&
                        $previousSnapshot->lap_number !== null &&
                        $snapshot->lap_number !== null &&
                        $previousSnapshot->wear_fl !== null &&
                        $snapshot->wear_fl !== null
                    ) {
                        $lapDiff = $snapshot->lap_number - $previousSnapshot->lap_number;

                        if ($lapDiff > 0) {
                            $degradation =
                                ($previousSnapshot->wear_fl - $snapshot->wear_fl) / $lapDiff;
                        }
                    }

                    if ($degradation !== null) {
                        $snapshot->update([
                            'degradation_per_lap' => $degradation
                        ]);
                    }

                    $previousSnapshot = $snapshot;
                }
            }
        return response()->json(['status' => 'ok'], 200);
    }
}

    /* ======================================================
        CLASIFICACION DE STINT
    ====================================================== */
    private function classifyStint($laps)
    {
        if($laps <= 6) return 'short run';
        if($laps <= 15) return 'long run';
        return 'race sim';
    }

    // public function processIbt(Request $request)
    // {
    //     return app(TelemetryService::class)->processIbt($request->all());
    // }

    public function processIbt(Request $request)
    {

        $methodStart = microtime(true);

        try {

            // ==============================
            // 1. VALIDAR ARCHIVO
            // ==============================
            if (!$request->hasFile('ibt')) {
                return response()->json(['error' => 'No file'], 400);
            }

            $file = $request->file('ibt');
            $stintId = $request->input('stint_id');
            $ibtFilename = $file->getClientOriginalName();


            $stint = null;

            $userId = null;

            if ($stintId) {

                $stint = Stint::find($stintId);

                $userId = $stint?->user_id;
            }

            Log::info('IBT recibido', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'stint_id' => $stintId
            ]);


            // ==============================
            // 2. GUARDAR IBT
            // ==============================
            $path = $file->store('ibts');

            Log::info('IBT guardado en', ['path' => $path]);

            Log::info('IBT exists?', [
                'exists' => Storage::exists($path)
            ]);


            // ==============================
            // 3. LEER CONTENIDO
            // ==============================
            $loadStart = microtime(true);

            $content = Storage::get($path);

            Log::info('IBT LOAD', [
                'seconds' => round(
                    microtime(true) - $loadStart,
                    3
                ),
                'size_mb' => round(
                    strlen($content) / 1024 / 1024,
                    1
                )
            ]);

            // ==============================
            // 🔥 PARSEO WEEKEND INFO (CLAVE)
            // ==============================
            preg_match('/SeriesID:\s*(\d+)/', $content, $seriesMatch);
            preg_match('/Official:\s*(\d+)/', $content, $officialMatch);
            preg_match('/IsFixedSetup:\s*(\d+)/', $content, $fixedMatch);


            $seriesId = $seriesMatch[1] ?? null;
            $isOfficial = isset($officialMatch[1]) ? (int)$officialMatch[1] : null;
            $isFixedSetup = isset($fixedMatch[1]) ? (int)$fixedMatch[1] : null;

            if ($seriesId) {

                \App\Models\IracingSerie::firstOrCreate(
                    ['iracing_series_id' => $seriesId],
                    [
                        'name' => 'UNKNOWN_' . $seriesId
                    ]
                );
            }

            Log::info('IBT WEEKEND INFO', [
                'serie_id' => $seriesId,
                'official' => $isOfficial,
                'fixed_setup' => $isFixedSetup
            ]);


            // ==============================
            // 4. PARSEO BASE
            // ==============================
            preg_match('/SubSessionID:\s*(\d+)/', $content, $subMatch);
            preg_match('/TrackID:\s*(\d+)/', $content, $trackMatch);
            preg_match('/CarID:\s*(\d+)/', $content, $carMatch);

            $rawSubsessionId = $subMatch[1] ?? null;
            $trackId = $trackMatch[1] ?? null;
            $carId   = $carMatch[1] ?? null;

            // 🚨 VALIDACIÓN FUERTE
            if (!$trackId || !$carId) {
                Log::warning('IBT INVALIDO → SIN TRACK O CAR', [
                    'track_id' => $trackId,
                    'car_id' => $carId
                ]);

                return response()->json(['error' => 'Invalid IBT'], 400);
            }

            // ==============================
            // 5. NORMALIZAR SESSION ID
            // ==============================
            if ($rawSubsessionId == 0 || $rawSubsessionId === "0") {
                $subsessionId = now()->format('Ymd') . '_' . $trackId . '_' . $carId;
            } else {
                $subsessionId = $rawSubsessionId;
            }


            // ==============================
            // 🔥 6. PARSEO REAL DE FASES
            // ==============================

            // ==============================
            // 🔥 EXTRAER SOLO BLOQUE Sessions
            // ==============================
            preg_match('/Sessions:(.*?)(?:\n\S|\Z)/s', $content, $sessionsBlockMatch);

            $sessionsBlock = $sessionsBlockMatch[1] ?? '';

            // ==============================
            // 🔥 PARSEAR SOLO ESE BLOQUE
            // ==============================
            preg_match_all(
                '/SessionNum:\s*(\d+).*?SessionType:\s*([^\r\n]+)/s',
                $sessionsBlock,
                $matches
            );

            $sessions = [];

            if (!empty($matches[1])) {
                foreach ($matches[1] as $i => $num) {
                    $sessions[] = [
                        'num' => (int)$num,
                        'type' => trim($matches[2][$i])
                    ];
                }
            }

            // fallback
            if (empty($sessions)) {
                $sessions[] = [
                    'num' => 0,
                    'type' => 'Practice'
                ];
            }


            // ==============================
            // 🔥 7. FASE ACTUAL (CLAVE REAL)
            // ==============================
            preg_match('/CurrentSessionNum:\s*(\d+)/', $content, $currentMatch);
            $currentNum = isset($currentMatch[1]) ? (int)$currentMatch[1] : null;

            $sessionType = 'Unknown';

            if ($currentNum !== null) {
                foreach ($sessions as $s) {
                    if ($s['num'] === $currentNum) {
                        $sessionType = $s['type'];
                        break;
                    }
                }
            } else {
                // fallback (última)
                $lastSession = end($sessions);
                $sessionType = $lastSession['type'] ?? 'Unknown';
            }


            Log::info('IBT PARSED', [
                'subsession_id' => $subsessionId,
                'session_type' => $sessionType,
                'current_session_num' => $currentNum,
                'track_id' => $trackId,
                'car_id' => $carId,
                'sessions_detected' => $sessions
            ]);


            // ==============================
            // 8. CREAR / OBTENER SESIÓN
            // ==============================
            $series = \App\Models\IracingSerie::where('iracing_series_id', $seriesId)->first();

            $sessionName = 'Session ' . $subsessionId;

            if ($series) {
                $sessionName = ($series->short_name ?? $series->name) . ' #' . $subsessionId;
            }

            $session = \App\Models\IrSession::firstOrCreate(
                ['iracing_subsession_id' => $subsessionId],
                [
                    'name' => $sessionName,
                    'serie_id' => $series?->id
                ]
            );

            // 🔥 update si estaba vacía o TEMP
            if (str_starts_with($session->name, 'TEMP') || !$session->serie_id) {
                $session->update([
                    'name' => $sessionName,
                    'serie_id' => $series?->id
                ]);
            }

            $finalPath = null;

            $finalFilename = null;

            // ==============================
            // 8.5 FINAL STORAGE
            // ==============================

            try {

                if ($userId) {

                    $year = now()->format('Y');

                    $month = now()->format('m');

                    $destinationFolder =

                        "telemetry/{$year}/{$month}/user_{$userId}";

                    // ==================================
                    // CREATE DIRECTORY
                    // ==================================

                    Storage::makeDirectory(
                        $destinationFolder
                    );

                    // ==================================
                    // FINAL FILE
                    // ==================================

                    $finalFilename =
                        'stint_' . $stintId . '.ibt';

                    $finalPath =
                        $destinationFolder .
                        '/' .
                        $finalFilename;

                    // ==================================
                    // COPY
                    // ==================================

                    $copyStart = microtime(true);

                    Storage::copy(
                        $path,
                        $finalPath
                    );

                    Log::info('IBT COPY', [
                        'seconds' => round(
                            microtime(true) - $copyStart,
                            3
                        )
                    ]);

                    Log::info('IBT FINAL STORAGE', [

                        'from' => $path,

                        'to' => $finalPath
                    ]);
                }

            } catch (\Exception $e) {

                Log::error(

                    'IBT FINAL STORAGE ERROR',

                    [
                        'error' => $e->getMessage()
                    ]
                );
            }


            /// ========================================
            //  8.6 REGISTER STINT FILE
            // ========================================

            try {

                if (

                    $stintId

                    && $stint

                    && $finalPath

                    && Storage::exists($finalPath)

                ) {

                    $hashStart = microtime(true);

                    StintFile::updateOrCreate(

                        [

                            'stint_id' => $stint->id,

                            'user_id' => $userId,

                            'type' => 'ibt',

                        ],

                        [

                            'filename' => $finalFilename,

                            'filepath' => $finalPath,

                            'filesize' => Storage::size(
                                $finalPath
                            ),

                            'filehash' => hash_file(
                                'sha256',
                                Storage::path($finalPath)
                            ),
                        ]
                    );

                    Log::info('IBT HASH', [

                        'seconds' => round(
                            microtime(true) - $hashStart,
                            3
                        )

                    ]);

                    Log::info(
                        'STINT FILE REGISTERED',
                        [

                            'stint_id' => $stint->id,

                            'file' => $finalFilename

                        ]
                    );
                }

                Log::info('IBT TOTAL PROCESS', [

                    'seconds' => round(
                        microtime(true) - $methodStart,
                        3
                    )

                ]);

            } catch (\Exception $e) {

                Log::error(
                    'STINT FILE REGISTER ERROR',
                    [

                        'error' => $e->getMessage()

                    ]
                );
            }



            // ==============================
            // 9. CREAR FASES (SIN DUPLICAR)
            // ==============================
            foreach ($sessions as $s) {

                \App\Models\IrSessionPhase::firstOrCreate(
                    [
                        'session_id' => $session->id,
                        'session_type' => $s['type']
                    ],
                    [
                        'session_num' => $s['num']
                    ]
                );
            }


            // ==============================
            // 10. ACTUALIZAR STINT
            // ==============================
            if ($stintId && !is_null($subsessionId)) {

                \App\Models\Stint::where('id', $stintId)->update([
                    'iracing_subsession_id' => $subsessionId,
                    'session_phase' => $sessionType,
                    'ibt_filename' => $ibtFilename,
                ]);

                Log::info('STINT UPDATED FROM IBT', [
                    'stint_id' => $stintId,
                    'phase' => $sessionType
                ]);
            }


            // ==============================
            // 11. RESPUESTA
            // ==============================
            return response()->json([
                'status' => 'ok',
                'subsession_id' => $subsessionId,
                'session_type' => $sessionType
            ]);


        } catch (\Exception $e) {

            Log::error('IBT ERROR: ' . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function processSetup(Request $request)
    {
        try {

            // ==============================
            // VALIDAR
            // ==============================

            $request->validate([

                'setup' => 'required|file',

                'stint_id' => 'required|exists:ir_stints,id'
            ]);

            // ==============================
            // STINT
            // ==============================

            $stint = \App\Models\Stint::findOrFail(
                $request->stint_id
            );

            // ==============================
            // FILE
            // ==============================

            $file = $request->file('setup');

            $filename =

                'stint_' .

                $stint->id .

                '.sto';

            $path = $file->storeAs(
                'setups',
                $filename,
                'local'
            );
            $fileSize = $file->getSize();
            $fileHash = hash_file(
                'sha256',
                Storage::disk('local')->path($path)
            );

            $setup = \App\Models\Setup::where(

                'stint_id',

                $stint->id

            )->first();

            if ($setup) {

                $setup->update([

                    'setup_file_path' => $path,

                    'setup_file_size' => $fileSize,

                    'setup_file_hash' => $fileHash,

                ]);

            }


            // ==============================
            // LOG
            // ==============================

            \Log::info(

                'SETUP FILE STORED',

                [

                    'stint_id' => $stint->id,

                    'path' => $path,

                    'size' => $file->getSize()
                ]
            );

            // ==============================
            // RESPONSE
            // ==============================



            return response()->json([

                'status' => 'ok',

                'stint_id' => $stint->id,

                'path' => $path
            ]);

        } catch (\Exception $e) {

            \Log::error(

                'SETUP FILE ERROR',

                [

                    'error' => $e->getMessage()
                ]
            );

            return response()->json([

                'error' => $e->getMessage()
            ], 500);
        }
    }


public function storeLapLive(Request $request)
{
    $user = User::where('api_token',$request->header('X-API-TOKEN'))->first();

    if(!$user)
        return response()->json(['error'=>'Unauthorized'],401);

    $telemetry = Telemetry::create([
        'user_id'=>$user->id,
        'lap'=>$request->lap,
        'lap_time'=>$request->lap_time,
        'fuel'=>$request->fuel,
        'timestamp'=>now()
    ]);

    $gap = null;
    $bestLap = $request->best_lap ?? $request->lap_time;

    if($bestLap && $request->lap_time)
        $gap = round($request->lap_time - $bestLap,3);

    // event(new LapCompleted($telemetry,$gap,$bestLap));

    return response()->json(['ok'=>true]);
}

public function getTrackSectors($track_id)
{
    $sectors = DB::table('track_sectors')
        ->where('track_id', $track_id)
        ->orderBy('sector_number')
        ->pluck('start_pct')
        ->toArray();

    if (!$sectors) {
        return response()->json(['sector_splits' => []]);
    }

    return response()->json([
        'sector_splits' => $sectors
    ]);
}






}
