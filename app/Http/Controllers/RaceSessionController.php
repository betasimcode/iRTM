<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RaceSession;
use App\Models\User;
use App\Models\IrSession;
use App\Models\Car;
use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use App\Models\Circuit;

class RaceSessionController extends Controller
{

    public function index()
    {

        $sessions = IrSession::with([
            'stints.laps'
        ])->withCount('stints')->get();

        return view('sessions.index', compact('sessions'));
    }



    public function update(Request $request, RaceSession $race_session)
{
    $race_session->update($request->all());

    // calcular consumo real
    if($race_session->fuel_start && $race_session->fuel_end && $race_session->laps_done > 0){
        $race_session->fuel_used = $race_session->fuel_start - $race_session->fuel_end;
        $race_session->save();
    }

    return redirect('/calendar');
}

    public function show(RaceSession $race_session)
    {
        return redirect()->route('race_sessions.edit', $race_session->id);
    }


    public function edit(RaceSession $race_session)
{
    return view('race_sessions.edit', compact('race_session'));
}

    public function averageConsumption($car_id, $circuit_id)
    {
        $sessions = RaceSession::where('car_id',$car_id)
            ->where('circuit_id',$circuit_id)
            ->whereNotNull('fuel_used')
            ->where('laps_done','>',0)
            ->get();

        if($sessions->count()==0)
            return response()->json(['avg'=>null]);

        $avg = $sessions->avg(function($s){
            return $s->fuel_used / $s->laps_done;
        });

        return response()->json(['avg'=>round($avg,3)]);
    }

}
