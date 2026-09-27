<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use App\Models\Circuit;
use App\Models\RaceSession;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $car = Car::create([
            'name' => 'Porsche 911 GT3 R',
            'category' => 'GT3',
            'fuel_capacity' => 120,
            'fuel_consumption' => 2.8
        ]);

        $circuit = Circuit::create([
            'name' => 'Spa-Francorchamps',
            'country' => 'Belgium',
            'length_km' => 7.004,
            'fuel_per_lap' => 2.9
        ]);

        RaceSession::create([
            'car_id' => $car->id,
            'circuit_id' => $circuit->id,
            'lap_time' => 140,
            'fuel_used' => 29,
            'laps_done' => 10,
            'session_type' => 'Práctica'
        ]);
    }
}
