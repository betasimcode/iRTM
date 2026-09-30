<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LoggerController extends Controller
{
    public function getToken(Request $request)
    {
        // 🚩 ESTO ES EL MONITOR: Veremos qué envía Python exactamente
        Log::info('--- Intento de conexión del Logger ---');
        // Log::info('Headers:', $request->headers->all());
        // Log::info('Body:', $request->all());

        // Forzamos a que busque tanto en el body como en el query
        $iracingId = $request->input('iracing_user_id');

        if (!$iracingId) {
            return response()->json(['error' => 'iracing_user_id missing'], 400);
        }

        // Buscamos al usuario
        $user = User::where('iracing_user_id', $iracingId)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Driver not found',
                'id_recibido' => $iracingId // Útil para debuguear en el log de Python
            ], 404);
        }

        // Si el usuario existe pero no tiene token, lo creamos
        if (empty($user->api_token)) {
            $user->api_token = Str::random(60);
            $user->save();
        }

        return response()->json([
            'api_token' => $user->api_token,
            'user_name' => $user->name,
            'user_id'   => (int) $user->id
        ]);
    }

    public function status(Request $request)
    {
        $token = $request->header('X-API-TOKEN');

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $user->update([
            'current_status' => $request->input('current_status', 'active'),
            'last_logger_ping' => now()
        ]);

        return response()->json(['status' => 'ok']);
    }


}

