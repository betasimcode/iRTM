<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackDataController extends Controller
{
    public function sync(Request $request)
    {
        try {
            $trackData = $request->input('track');

            if (!$trackData) {
                return response()->json(['error' => 'No track data'], 400);
            }

            // IMPORTANTE: Asegúrate de que estos nombres de columna existen en tu DB.
            // Si tu tabla usa 'track_country', cámbialo abajo.
            DB::table('tracks')->updateOrInsert(
                ['iracing_track_id' => $trackData['iracing_track_id']],
                [
                    'name'         => $trackData['name'],
                    'display_name' => $trackData['display_name'] ?? $trackData['name'],
                    'variant'  => $trackData['variant'] ?? null,
                    'city'   => $trackData['city'] ?? null,
                    'country'=> $trackData['country'] ?? null,
                    'region' => $trackData['region'] ?? null,
                    'length_km'    => isset($trackData['length_meters']) ? round($trackData['length_meters'] / 1000, 3) : 0,
                    'updated_at'   => now(),

                ]
            );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            // Esto escribirá el error exacto de SQL en tu log
            Log::error("Error Sync: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
