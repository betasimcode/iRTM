<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\{Track, TrackSector, IrSession, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewTelemetryController extends Controller
{
  public function initSession(Request $request)
    {
        try {
            DB::beginTransaction();

            $trackData = $request->input('track');
            $sessionData = $request->input('session');
            $apiToken = $request->header('X-API-TOKEN');
            $user = \App\Models\User::where('api_token', $apiToken)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // 1. Buscamos el track por su ID de iRacing (ej: 15)
            $track = DB::table('tracks')
                ->where('iracing_track_id', $trackData['iracing_track_id'])
                ->first();

            if (!$track) {
                return response()->json([
                    'success' => false,
                    'error' => "El circuito {$trackData['iracing_track_id']} no está en la tabla tracks."
                ], 400);
            }

            // 2. Registrar la Sesión
            // CAMBIO AQUÍ: 'track_id' debe recibir el ID interno de la tabla (ej: 100)
            if (!isset($sessionData['session_type'])) {
                throw new \Exception("session_type es obligatorio desde el logger");
            }
            
            DB::table('ir_sessions')->updateOrInsert(
                ['iracing_subsession_id' => $sessionData['iracing_subsession_id']],
                [
                    'user_id'      => $user->id,
                    'track_id'     => $track->id, // <--- ANTES TENÍAS iracing_track_id (15). AHORA ID (100).
                    'session_type' => $sessionData['session_type'],
                    'sof'          => $sessionData['sof'] ?? 0,
                    'session_at'   => $sessionData['session_at'] ?? now(),
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ]
            );

            $dbSessionId = DB::table('ir_sessions')
                ->where('iracing_subsession_id', $sessionData['iracing_subsession_id'])
                ->value('id');

            // --- RECUPERAR SECTORES ---
            // Aquí depende de cómo esté tu tabla track_maps.
            // Si track_maps también usa el ID de iRacing (15), déjalo como está.
            // Si track_maps usa la relación interna (100), cámbialo a $track->id.
            $sectors = DB::table('track_maps')
                ->where('track_id', $track->id)
                ->orderBy('sector_number', 'asc')
                ->pluck('start_pct')
                ->toArray();

            $sectorsString = !empty($sectors) ? implode(',', $sectors) : "0.33,0.66,0.99";

            DB::commit();

            return response()->json([
                'success'       => true,
                'db_session_id' => $dbSessionId,
                'sectors'       => $sectorsString,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function startStint(Request $request)
    {
        try {
            $data = $request->all();

            // 1. BUSCAR EL ID INTERNO DEL TRACK
            // El Logger envía 'track_id' como el código de iRacing (ej: 288)
            $track = DB::table('tracks')
                ->where('iracing_track_id', $data['track_id'])
                ->first();

            if (!$track) {
                throw new \Exception("El track con iRacing ID {$data['track_id']} no existe en la DB.");
            }

            // 2. REEMPLAZAR EL ID PARA LA CLAVE FORÁNEA
            $data['track_id'] = $track->id;

            // 3. INSERTAR EL STINT
            $stintId = DB::table('ir_stints')->insertGetId([
                'iracing_subsession_id' => $data['iracing_subsession_id'],
                'track_id'              => $track->id, // ID interno (ej: 12)
                'car_name'              => $data['car_name'],
                'car_id'                => $data['car_id'],
                'sim_time_day'          => $data['sim_time_day'],
                'fuel_setup'            => $data['fuel_setup'],
                'track_temp'            => $data['track_temp'],
                'air_temp'              => $data['air_temp'],
                'track_usage_pct'       => $data['track_usage_pct'] ?? 0,
                'laps_completed'        => 0,
                'fuel_consumed'         => 0,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            return response()->json([
                'success'  => true,
                'stint_id' => $stintId,
                'message'  => "Stint iniciado correctamente."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // En NewTelemetryController.php o similar
    public function registerTrackSectors(Request $request)
    {
        $data = $request->all();
        $trackId = $data['track_id'];

        // 1. Aseguramos que el circuito existe (usando la info del IBT)
        DB::table('circuits')->updateOrInsert(
            ['iracing_track_id' => $trackId],
            [
                'name' => $data['name'],
                'variant' => $data['config'],
                'city' => $data['city'],
                'country' => $data['country'],
                'length_km' => $data['length'],
                'updated_at' => now()
            ]
        );

        // 2. Registramos los sectores
        foreach ($data['splits'] as $index => $pct) {
            DB::table('track_sectors')->updateOrInsert(
                ['track_id' => $trackId, 'sector_number' => $index + 1],
                ['start_pct' => $pct, 'updated_at' => now()]
            );
        }

        return response()->json(['success' => true, 'message' => 'Track sectors learned!']);
    }

    public function addLap(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            // 1. Insertar la vuelta (esto ya lo teníamos)
            $lapId = DB::table('ir_laps')->insertGetId([
                'ir_stint_id'     => $data['stint_id'],
                'lap_number'      => $data['lap_number'],
                'lap_time'        => $data['lap_time'],
                'fuel_lap_start'  => $data['fuel_lap_start'],
                'fuel_consumed'   => $data['fuel_consumed'],
                'incident_count'  => $data['incident_count'] ?? 0,
                'offtrack_count'  => $data['offtrack_count'] ?? 0,
                'is_valid_lap'    => $data['is_valid_lap'] ?? true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // 2. NUEVO: Insertar Sectores si vienen en el payload
            if (isset($data['sectors']) && is_array($data['sectors'])) {
                $sectorsToInsert = [];
                foreach ($data['sectors'] as $s) {
                    $sectorsToInsert[] = [
                        'ir_lap_id'     => $lapId,
                        'sector_number' => $s['sector_number'],
                        'sector_time'   => $s['sector_time'],
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
                DB::table('ir_sectors')->insert($sectorsToInsert);
            }

            // 3. Actualizar resumen del Stint
            // ... (el código de update que ya tenías para laps_completed y fuel) ...

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


}
