<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stint;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IrStintController extends Controller
{
    public function store(Request $request)
    {
        Log::info('ENTRANDO A STORE STINT');
        // 1. VALIDACIÓN DE SEGURIDAD (App Token)
        $apiToken = $request->header('X-API-TOKEN');
        $appUser = User::where('api_token', $apiToken)->first();

        if (!$appUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. VALIDACIÓN DEL PILOTO (Driver de iRacing)
        $driverData = $request->input('driver');
        if (!$driverData || empty($driverData['iracing_user_id'])) {
            return response()->json(['error' => 'Driver data missing'], 400);
        }

        $driver = User::where('iracing_user_id', $driverData['iracing_user_id'])->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not registered'], 403);
        }

        // 3. ACTUALIZAR STATUS (Logger Ping) - Una sola vez
        $driver->update([
            'logger_version' => $request->app_version ?? $driver->logger_version,
            'last_logger_ping' => now(),
        ]);

        // 4. VALIDAR VUELTAS (Mínimo 2 para ser un Stint)
        $lapsData = $request->input('laps', []);

        $validLaps = collect($lapsData)->filter(function($lap) {
            return isset($lap['lap_time']) && $lap['lap_time'] > 5;
        });

        if (empty($lapsData)) {
            $validLaps = collect(); // permitir vacío
        } else {
            $validLaps = collect($lapsData)->filter(function($lap) {
                return isset($lap['lap_time']) && $lap['lap_time'] > 5;
            });
        }
        $request->validate([
            'session_time_start' => 'nullable|numeric',
        ]);

        // Filtramos vueltas inválidas (menos de 5 segundos)
        $validLaps = collect($lapsData)->filter(function($lap) {
            return isset($lap['lap_time']) && $lap['lap_time'] > 5;
        });

        // 5. PROCESADO DE DATOS Y GUARDADO
        try {
            return DB::transaction(function () use ($request, $driver, $validLaps) {

                // Creamos el Stint usando el ID interno del driver
                $stint = Stint::create([
                    'user_id'               => $driver->id, // 👈 SOLUCIONADO: Aquí asignamos el ID de la tabla users
                    'iracing_subsession_id' => $request->iracing_subsession_id,
                    'session_phase' => $request->session_phase,
                    'track_id'              => $request->track_id,
                    'car_name'              => $request->car_name,
                    'car_id'                => $request->car_id, //
                    'sim_time_day'          => $request->sim_time_day,
                    'humidity' => $request->humidity,
                    'pressure' => $request->pressure,
                    'air_density' => $request->air_density,
                    'fuel_setup'            => $request->fuel_setup,
                    'track_temp'            => $request->track_temp,
                    'air_temp'              => $request->air_temp,
                    'fuel_per_lap'          => $request->fuel_per_lap,
                    'fuel_consumed'         => $request->fuel_consumed,
                    'laps_completed'        => $validLaps->count(), // 👈 SOLUCIONADO: Conteo real
                ]);

                // Guardamos las vueltas vinculadas
                foreach ($validLaps as $lap) {
                    $stint->laps()->create([
                        'lap_number' => $lap['lap_number'],
                        'lap_time'   => $lap['lap_time'],
                        'fuel_level' => $lap['fuel'] ?? null,
                        'sectors'    => isset($lap['sectors']) ? json_encode($lap['sectors']) : null,
                        // El created_at de la primera vuelta servirá de "started_at"
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'stint_id' => $stint->id,
                    'message' => 'Stint and ' . $validLaps->count() . ' laps registered.'
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error("Error saving Stint: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function end(Request $request)
    {
        Log::info('🏁 STINT END', $request->all());

        $stintId = $request->route('id');

        $stint = Stint::find($stintId);

        if (!$stint) {
            return response()->json([
                'success' => false,
                'error' => 'Stint not found'
            ], 404);
        }

        $validLapCount = $stint->laps()
            ->where('lap_number', '!=', 0)
            ->where('lap_time', '>', 5)
            ->count();

        Log::info('🔎 STINT VALIDATION', [
            'stint_id' => $stint->id,
            'valid_laps' => $validLapCount,
        ]);

        if ($validLapCount === 0) {
            Log::info('🗑️ DISCARDING INVALID STINT', [
                'stint_id' => $stint->id,
            ]);

            $stint->delete();

            return response()->json([
                'success' => true,
                'stint_id' => $stint->id,
                'valid_laps' => 0,
                'valid' => false,
                'discarded' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'stint_id' => $stint->id,
            'valid_laps' => $validLapCount,
            'valid' => true,
            'discarded' => false,
        ]);
            }

}
