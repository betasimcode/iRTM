<?php

namespace App\Http\Controllers;

use App\Models\RaceSession;
use App\Models\Car;
use App\Models\Circuit;

class CalendarController extends Controller
{
    public function index()
    {
        $sessions = RaceSession::with('car','circuit')->get();
        $cars = Car::all();
        $circuits = Circuit::all();

        $events = $sessions->map(function($s){
            return [
                'id' => $s->id,
                'title' => $s->car->name . ' - ' . $s->circuit->name,
                'start' => $s->scheduled_at,
                'extendedProps' => [
                    'car_id' => $s->car_id,
                    'circuit_id' => $s->circuit_id,
                    'laps_done' => $s->laps_done
                ]
            ];
        });

        return view('calendar', compact('events','cars','circuits'));
    }
}
