<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IrSessionResult;
use App\Models\IrSession;
use App\Models\User;
use App\Models\UserSeriesDivision;
use Carbon\Carbon;

class SessionResultController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([

            'iracing_subsession_id' =>
                'required|integer',

            'results' =>
                'required|array'
        ]);

        // ============================================
        // LIMPIAR RESULTADOS ANTERIORES
        // ============================================

        IrSessionResult::where(
            'iracing_subsession_id',
            $request->iracing_subsession_id
        )->delete();

        // ============================================
        // SESSION
        // ============================================

        $session = IrSession::where(

            'iracing_subsession_id',
            $request->iracing_subsession_id

        )->first();

        // ============================================
        // INSERTAR RESULTADOS
        // ============================================

        foreach ($request->results as $r) {

            IrSessionResult::updateOrCreate(
        
                [
                    'iracing_subsession_id' =>
                        $request->iracing_subsession_id,
        
                    'car_idx' =>
                        $r['car_idx'] ?? null,
                ],
        
                [
                    'position' =>
                        $r['position'] ?? null,
        
                    'class_position' =>
                        $r['class_position'] ?? null,
        
                    'fastest_time' =>
                        $r['fastest_time'] ?? null,
        
                    'user_name' =>
                        $r['user_name'] ?? null,
        
                    'car_name' =>
                        $r['car_name'] ?? null,
        
                    'car_number' =>
                        $r['car_number'] ?? null,
        
                    'irating' =>
                        $r['irating'] ?? null,
        
                    'iracing_user_id' =>
                        $r['iracing_user_id'] ?? null,
        
                    'license_class' =>
                        $r['license_class'] ?? null,
        
                    'country' =>
                        $r['country'] ?? null,
        
                    'country_id' =>
                        $r['country_id'] ?? null,
        
                    'division_name' =>
                        $r['division_name'] ?? null,
        
                    'division_id' =>
                        $r['division_id'] ?? null,
                ]
            );
        }

        // ============================================
        // USER DIVISION TRACKING
        // ============================================

        if (! empty($r['iracing_user_id'])) {

            $user = User::where(

                'iracing_user_id',
                $r['iracing_user_id']

            )->first();

            if ($user && $session?->serie_id) {

                $existing = UserSeriesDivision::where(

                    'user_id',
                    $user->id

                )->where(

                    'series_id',
                    $session->serie_id

                )->where(

                    'division_id',
                    $r['division_id'] ?? null

                )->first();

                // ====================================
                // UPDATE EXISTING
                // ====================================

                if ($existing) {

                    $existing->update([

                        'division_name' =>
                            $r['division_name'] ?? null,

                        'irating_avg' =>
                            $r['irating'] ?? null,

                        'last_seen_at' =>
                            now(),
                    ]);

                } else {

                    UserSeriesDivision::create([

                        'user_id' =>
                            $user->id,

                        'series_id' =>
                            $session->serie_id,

                        'division_id' =>
                            $r['division_id'] ?? null,

                        'division_name' =>
                            $r['division_name'] ?? null,

                        'irating_avg' =>
                            $r['irating'] ?? null,

                        'first_seen_at' =>
                            now(),

                        'last_seen_at' =>
                            now(),
                    ]);
                }
            }
        }


        return response()->json([
            'success' => true,
            'count' => count($request->results)
        ]);
    }
}