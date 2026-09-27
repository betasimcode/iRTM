<?php

namespace App\Http\Controllers;

use App\Models\IrSession;
use App\Models\Series;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\IracingSerie;
use App\Models\Track;
use App\Models\User;
use App\Models\UserSeriesDivision;
use App\Models\IrSessionResult;
use App\Models\Stint;
use App\Models\TeamCarSerie;
use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use Carbon\Carbon;
    
    class SessionController extends Controller
    {
        // =========================
        // INDEX → lista sesiones
        // =========================
        public function index()
        {
            $sessions = \App\Models\IrSession::with([
                'series',
                'stints.track',
                'stints.car'
            ])
            ->withCount('stints')
            ->orderBy('created_at', 'desc')
            ->paginate(4);

            return view('sessions.index', compact('sessions'));
        }
    
    
        // =========================
        // SHOW → detalle sesión
        // =========================
        public function show($id)
        {
            $session = \App\Models\IrSession::with([
        
                'series',
        
                'stints.laps',
        
                'stints.track',
        
                'stints.car',
        
                'results'
        
            ])->findOrFail($id);
        
            // ========================================
            // USER DIVISION
            // ========================================
        
            $userDivision = UserSeriesDivision::where(
        
                'user_id',
                auth()->id()
        
            )->where(
        
                'serie_id',
                $session->serie_id
        
            )->latest('last_seen_at')
        
            ->first();
        
            // ========================================
            // DIVISION RESULTS
            // ========================================
        
            $divisionResults = collect();
        
            if ($userDivision) {
        
                $divisionResults = IrSessionResult::where(

                    'iracing_subsession_id',
                    $session->iracing_subsession_id
                
                )->where(
                
                    'division_id',
                    $userDivision->division_id
                
                )->whereNotNull(
                
                    'class_position'
                
                )->orderByRaw(
                
                    'CAST(class_position AS UNSIGNED)'
                
                )->get();
            }
        
            return view(
        
                'sessions.show',
        
                compact(
        
                    'session',
        
                    'userDivision',
        
                    'divisionResults'
                )
            );
        }


    public function register(Request $request)
    {
        $data = $request->validate([
            'iracing_subsession_id' => 'required|string',
            'track_id' => 'required|integer',
            'session_type' => 'required|string',
        ]);

        $session = \App\Models\IrSession::where('iracing_subsession_id', $data['iracing_subsession_id'])
            ->where('user_id', auth()->id() ?? 1) // ajusta si usas token
            ->first();

        if ($session) {
            return response()->json([
                'status' => 'exists',
                'session_id' => $session->id
            ]);
        }

        $session = \App\Models\IrSession::create([
            'user_id' => auth()->id() ?? 1,
            'track_id' => $data['track_id'],
            'iracing_subsession_id' => $data['iracing_subsession_id'],
            'session_type' => $data['session_type'],
            'sof' => 0,
            'session_at' => now(),
        ]);

        return response()->json([
            'status' => 'created',
            'session_id' => $session->id
        ]);
    }





}


