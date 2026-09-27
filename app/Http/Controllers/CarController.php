<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::orderBy('name')->get();
        return view('cars.index', compact('cars'));
    }

    public function edit(Car $car)
    {
        return view('cars.edit', compact('car'));
    }

    public function update(Request $request, Car $car)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'logo_path' => 'nullable|image|max:2048',
            'image_path' => 'nullable|image|max:2048',
            'category' => 'nullable|string',
            'weight_kg' => 'nullable|integer',
            'power_hp' => 'nullable|integer',
            'drive_type' => 'nullable|string',
            'engine_position' => 'nullable|string',
            'wheelbase_mm' => 'nullable|integer',
            'front_track_mm' => 'nullable|integer',
            'rear_track_mm' => 'nullable|integer',
            'aero_level' => 'nullable|string',
            'mechanical_grip' => 'nullable|string',
            'tyre_model' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('cars/logos','public');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('cars/images','public');
        }
        $car->update($data);

        return redirect()->route('cars.index')->with('ok','Coche actualizado');
    }

    public function syncFromLogger(Request $request)
    {
        $data = $request->validate([
            'iracing_car_id' => 'required|integer',
            'name' => 'required|string',
            'tank_capacity' => 'nullable|numeric',
            'iracing_setup_folder' => 'nullable|string'
        ]);

        $car = Car::where('iracing_car_id', $data['iracing_car_id'])->first();

        // 🔥 SI YA EXISTE Y TIENE TANK → NO TOCAR
        if (

            $car &&
        
            $car->tank_capacity &&
        
            $car->iracing_setup_folder
        
        ) {
        
            return response()->json([
        
                'status' => 'exists',
        
                'id' => $car->id,
        
                'tank_capacity' => $car->tank_capacity
            ]);
        }

        // 🔥 SI NO EXISTE O NO TIENE TANK → ACTUALIZAR
        $updateData = [
            'name' => $data['name']
        ];
        
        if (!empty($data['iracing_setup_folder'])) {
        
            $updateData['iracing_setup_folder'] =
                $data['iracing_setup_folder'];
        }

        if (!empty($data['tank_capacity'])) {
            $updateData['tank_capacity'] = $data['tank_capacity'];
        }

        $car = Car::updateOrCreate(
            ['iracing_car_id' => $data['iracing_car_id']],
            $updateData
        );

        return response()->json([
            'status' => 'ok',
            'id' => $car->id,
            'tank_capacity' => $car->tank_capacity
        ]);
    }


}
