<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IrLap;
use App\Models\Stint;
use App\Models\IrSector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LapController extends Controller
{
    public function addLap(Request $request)
    {
        Log::info("Datos recibidos de C#:", $request->all());

        // ✔ Validación completa
        $validated = $request->validate([
            'ir_stint_id'      => 'required|integer',
            'lap_number'       => 'required|integer',
            'lap_time'         => 'required|numeric',
            'fuel_lap_start'   => 'nullable|numeric',
            'fuel_consumed'    => 'nullable|numeric',
            'sectores_detalle' => 'required|array',
            'sectores_detalle.*.sector_number' => 'required|integer',
            'sectores_detalle.*.sector_time'   => 'required|numeric',
            'sectores_detalle.*.start_pct'     => 'required|numeric',
            'incident_count' => 'nullable|integer',
            'offtrack_count' => 'nullable|integer',
            'is_clean'   => 'nullable|boolean',
            'track_temp' => 'nullable|numeric',
            'air_temp' => 'nullable|numeric',

            'wind_speed' => 'nullable|numeric',
            'wind_dir' => 'nullable|numeric',

            'sky' => 'nullable|integer',

            'weather_type' => 'nullable|integer',
            'track_wetness' => 'nullable|integer',

            'weather_declared_wet' => 'nullable|boolean',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {

                $existingLap = IrLap::where('ir_stint_id', $validated['ir_stint_id'])
                    ->where('lap_number', $validated['lap_number'])
                    ->first();

                if ($existingLap) {
                    return response()->json([
                        'status' => 'duplicate',
                        'message' => 'Lap ya registrada'
                    ], 200);
                }

                $stintId = $validated['ir_stint_id'];
                $stint = Stint::find($stintId);

                $trackId = $stint->track_id;

                if (!$stint) {
                    return response()->json([
                        'error' => 'Stint not found'
                    ], 404);
                }


                // 🔥 SECTORES ESPERADOS DESDE TRACK_MAPS
                $mapSectors = \DB::table('track_maps')
                ->where('track_id', $trackId)
                ->count();

                // +1 porque los mapas son puntos, no tramos
                $expectedSectors = $mapSectors + 1;
                
                $receivedSectors = count($validated['sectores_detalle']);


                // ==============================
                // 🔥 VALIDACIÓN POR TRACK MAP
                // ==============================

                if ($receivedSectors < $expectedSectors) {

                    Log::warning("❌ Vuelta descartada (incompleta)", [
                        'lap_number' => $validated['lap_number'],
                        'received_sectors' => $receivedSectors,
                        'expected_sectors' => $expectedSectors
                    ]);
                
                    return response()->json([
                        'status' => 'ignored',
                        'reason' => 'incomplete lap'
                    ], 200);
                }


                $lap = IrLap::create([
                    'ir_stint_id'   => $stintId,
                    'lap_number'    => $validated['lap_number'],
                    'lap_time'      => $validated['lap_time'],
                    'fuel_lap_start'=> $validated['fuel_lap_start'] ?? 0,
                    'fuel_consumed' => $validated['fuel_consumed'] ?? 0,
                    
                    'pit_in' => $request->pit_in ?? false,
                    'pit_out' => $request->pit_out ?? false,
                    
                    'incident_count' => $request->incident_count ?? 0,
                    'offtrack_count' => $request->offtrack_count ?? 0,
                    // 🔥 REVIEW: fuente única de verdad desde logger
                    'is_clean' => $request->has('is_clean')
                    ? (bool)$request->is_clean
                    : true,
                    'track_temp' => $request->track_temp,
                    'air_temp' => $request->air_temp,

                    'wind_speed' => $request->wind_speed,
                    'wind_dir' => $request->wind_dir,

                    'sky' => $request->sky,

                    'track_state' => $request->track_wetness,

                    // nuevos campos
                    'weather_type' => $request->weather_type,
                    'weather_declared_wet' => $request->weather_declared_wet,
                ]);

                foreach ($validated['sectores_detalle'] as $sector) {
                    IrSector::create([
                        'ir_lap_id'     => $lap->id,
                        'sector_number' => $sector['sector_number'],
                        'sector_time'   => $sector['sector_time'],
                        'start_pct'     => $sector['start_pct'],
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'lap_id' => $lap->id
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error("Error crítico al guardar vuelta: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function enrichLap(Request $request)
    {
        try {

            $data = $request->validate([
                'ir_stint_id'     => 'required|integer',
                'lap_number'      => 'required|integer',
                'incident_count'  => 'nullable|integer',
                'offtrack_count'  => 'nullable|integer',
                'is_clean'    => 'nullable|boolean',
            ]);

            $lap = IrLap::where('ir_stint_id', $data['ir_stint_id'])
                ->where('lap_number', $data['lap_number'])
                ->first();

            if (!$lap) {
                return response()->json([
                    'status' => 'not_found'
                ], 404);
            }

            $lap->update([
                // 🔥 REVIEW: enriquecimiento puro (sin recalcular lógica)
                'incident_count' => $data['incident_count'] ?? $lap->incident_count,
                'offtrack_count' => $data['offtrack_count'] ?? $lap->offtrack_count,
                'is_clean' => $data['is_clean'] ?? $lap->is_clean,
            ]);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {

            Log::error("Error enrichLap: " . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }









}
