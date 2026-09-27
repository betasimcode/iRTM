<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    // GET: ¿Dónde empiezan los sectores?
    public function getSectors($track_id)
    {
        // Buscamos los cortes en nuestra tabla maestra 'track_maps'
        $splits = DB::table('track_maps')
            ->where('track_id', $track_id)
            ->orderBy('sector_number', 'asc')
            ->pluck('start_pct');

        if ($splits->isEmpty()) {
            // Si no hay sectores manuales, avisamos al Logger
            return response()->json([
                'message' => "El circuito $track_id no tiene sectores definidos manualmente."
            ], 404);
        }

        return response()->json([
            'track_id' => $track_id,
            'sector_splits' => $splits
        ]);
    }

    // POST: Guardar el mapa del circuito desde el IBT
    public function registerSectors(Request $request)
    {
        $trackId = $request->track_id;
        $splits = $request->splits;

        // Ahora borramos de la tabla correcta que SÍ tiene track_id
        DB::table('track_maps')->where('track_id', $trackId)->delete();

        foreach ($splits as $index => $pct) {
            DB::table('track_maps')->insert([
                'track_id' => $trackId,
                'sector_number' => $index + 1,
                'start_pct' => $pct,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }
}

