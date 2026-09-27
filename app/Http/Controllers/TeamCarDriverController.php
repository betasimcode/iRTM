<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamCarUser;
use Illuminate\Support\Facades\DB;

class TeamCarDriverController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'user_id' => 'required|exists:users,id',
        'team_car_id' => 'required|exists:team_cars,id',
        'series_id' => 'required|exists:series,id',
        'role' => 'required|in:driver1,driver2,driver3,reserve'
    ]);

    $exists = DB::table('team_car_user')
        ->where('user_id', $data['user_id'])
        ->where('team_car_id', $data['team_car_id'])
        ->where('series_id', $data['series_id'])
        ->exists();

    if ($exists) {
        return back()->withErrors('Already assigned');
    }

    DB::table('team_car_user')->insert($data);

    return back()->with('success','Driver assigned');
}

    public function destroy(Request $request)
    {
        DB::table('team_car_user')
            ->where('user_id', $request->user_id)
            ->where('team_car_id', $request->team_car_id)
            ->delete();

        return back()->with('success', 'Driver removed');
    }
}