<?php

namespace App\Http\Controllers;

use App\Models\TeamCar;
use App\Models\Car;
use Illuminate\Http\Request;

class TeamCarController extends Controller
{
    // 🔹 LISTADO
    public function index()
    {
        $team = auth()->user()->team;

        $cars = Car::orderBy('name')->get();

        $teamCars = $team->teamCars()->with('car')->get();

        return view('team.tabs.cars', compact('teamCars', 'cars'));
    }


    // 🔹 FORM CREATE
    public function create()
    {
        $cars = Car::orderBy('name')->get();

        return view('team.cars.create', compact('cars'));
    }

    public function update(Request $request, TeamCar $teamCar)
    {
        if ($teamCar->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $data = $request->validate([
            'number' => 'nullable|string|max:10',
            'image_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('team_cars', 'public');
        }

        $teamCar->update($data);

        return back()->with('success', 'Car updated');
    }

    // 🔹 STORE
    public function store(Request $request)
    {
        $data = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'number' => 'nullable|string|max:10',
            'image_path' => 'nullable|image|max:2048',
        ]);

        $data['team_id'] = auth()->user()->team_id;

        // upload imagen
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('team_cars', 'public');
        }

        TeamCar::create($data);

        return back()->with('success', 'Car added');
    }


    public function destroy(TeamCar $teamCar)
    {
        if ($teamCar->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $teamCar->delete();

        return back()->with('success', 'Car removed');
    }
}
