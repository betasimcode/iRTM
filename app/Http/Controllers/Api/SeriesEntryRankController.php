<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeriesEntryMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeriesEntryRankController extends Controller
{
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Autenticación del logger
        |--------------------------------------------------------------------------
        */

        $token = $request->bearerToken()
            ?? $request->header('X-API-TOKEN');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Validación de los datos de iRacing
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [
            'series_id' => ['required', 'integer', 'min:1'],
            'season_id' => ['required', 'integer', 'min:1'],
            'division_id' => ['nullable', 'integer', 'min:0'],
            'division_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de Rank no válidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        /*
        |--------------------------------------------------------------------------
        | 3. Localizar la participación del usuario
        |--------------------------------------------------------------------------
        |
        | SeriesEntry -> Series -> iracing_series_id
        |
        | Solo buscamos miembros activos de la inscripción.
        */

        $members = SeriesEntryMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('entry.series', function ($query) use ($data) {
                $query
                    ->where('iracing_season_id', $data['season_id'])
                    ->whereHas('iracingSeries', function ($iracingQuery) use ($data) {
                        $iracingQuery->where(
                            'iracing_series_id',
                            $data['series_id']
                        );
                    });
            })
            ->with('entry.series')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. Comprobar que existe una participación inequívoca
        |--------------------------------------------------------------------------
        */

        if ($members->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No existe una participación activa para esta serie.',
                'series_id' => $data['series_id'],
                'season_id' => $data['season_id'],
                'user_id' => $user->id,
            ], 404);
        }

        if ($members->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Existe más de una participación activa para esta serie. No se ha actualizado ningún registro.',
                'series_id' => $data['series_id'],
                'season_id' => $data['season_id'],
                'matches' => $members->map(function ($member) {
                    return [
                        'member_id' => $member->id,
                        'series_entry_id' => $member->series_entry_id,
                        'local_series_id' => $member->entry->series_id,
                        'season_year' => $member->entry->series->season_year,
                        'season_number' => $member->entry->series->season_number,
                    ];
                })->values(),
            ], 409);
        }

        $member = $members->first();

        /*
        |--------------------------------------------------------------------------
        | 5. Guardar Rank
        |--------------------------------------------------------------------------
        */

        $member->iracing_series_id = $data['series_id'];
        $member->iracing_season_id = $data['season_id'];
        $member->iracing_division_id = $data['division_id'] ?? null;
        $member->iracing_division_name = $data['division_name'] ?? null;
        $member->rank_updated_at = now();

        $member->save();

        /*
        |--------------------------------------------------------------------------
        | 6. Respuesta
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Rank actualizado correctamente.',
            'member_id' => $member->id,
            'series_entry_id' => $member->series_entry_id,
            'iracing_series_id' => $member->iracing_series_id,
            'iracing_season_id' => $member->iracing_season_id,
            'iracing_division_id' => $member->iracing_division_id,
            'iracing_division_name' => $member->iracing_division_name,
        ]);
    }
}
