<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IrTelemetryTyre;

class TelemetryTyreController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'stint_id' => 'required|integer',
                'lap_number' => 'nullable|integer',
                'snapshot_type' => 'required|string',

                'temp_fl' => 'nullable|numeric',
                'temp_fr' => 'nullable|numeric',
                'temp_rl' => 'nullable|numeric',
                'temp_rr' => 'nullable|numeric',

                'temp_fl_o' => 'nullable|numeric',
                'temp_fl_m' => 'nullable|numeric',
                'temp_fl_i' => 'nullable|numeric',

                'temp_fr_o' => 'nullable|numeric',
                'temp_fr_m' => 'nullable|numeric',
                'temp_fr_i' => 'nullable|numeric',

                'temp_rl_o' => 'nullable|numeric',
                'temp_rl_m' => 'nullable|numeric',
                'temp_rl_i' => 'nullable|numeric',

                'temp_rr_o' => 'nullable|numeric',
                'temp_rr_m' => 'nullable|numeric',
                'temp_rr_i' => 'nullable|numeric',

                'wear_fl' => 'nullable|numeric',
                'wear_fr' => 'nullable|numeric',
                'wear_rl' => 'nullable|numeric',
                'wear_rr' => 'nullable|numeric',

            ]);

            $tyre = IrTelemetryTyre::create($data);

            return response()->json([
                'status' => 'ok',
                'id' => $tyre->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error guardando telemetría',
                'debug' => $e->getMessage()
            ], 500);
        }
    }
}
