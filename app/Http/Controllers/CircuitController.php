<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Services\BS90\TracksExporter;
use App\Models\Track;

class CircuitController extends Controller
{
    public function index()
    {
        $circuits = Track::orderBy('name', 'asc')->get();
        return view('circuits.index', compact('circuits')

    );
    }

    public function create()
    {
        return view('circuits.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'display_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'iracing_track_id' => 'required',
            'variant' => 'nullable|string|max:255',
            'length_km' => 'nullable|numeric',
            'logo' => 'nullable|image|max:2048',
            'logo_dark' => 'nullable|image|max:2048',
            'logo_light' => 'nullable|image|max:2048',
            'map' => 'nullable|image|max:2048',
            'map_svg' => 'nullable|string',
        ]);

        Track::create($request->all());
        return redirect()->route('circuits.index')->with('success', 'Circuito creado correctamente');
    }

    public function edit(Track $circuit)
    {
        return view('circuits.edit', compact('circuit'));
    }

    public function update(Request $request, Track $circuit)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'name' => 'string|max:255',
            'iracing_track_id' => 'required',
            'short_name' => 'nullable|string|max:50',
            'variant' => 'nullable|string|max:255',
            'length_km' => 'nullable|numeric',
            'logo' => 'nullable|image|max:2048',
            'logo_dark' => 'nullable|image|max:2048',
            'logo_light' => 'nullable|image|max:2048',
            'map' => 'nullable|image|max:2048',
            'map_svg' => 'nullable|string',
            'type' => 'nullable|string',
            'layout_type' => 'nullable|string',
            'direction' => 'nullable|string',
            'longest_straight_m' => 'nullable|integer',
            'avg_speed_kmh' => 'nullable|integer',
            'grip_level' => 'nullable|string',
            'surface_type' => 'nullable|string',
            'braking_intensity' => 'nullable|string',
            'traction_zones' => 'nullable|string',
            'climate_type' => 'nullable|string',
            'altitude_m' => 'nullable|integer',
        ]);


        if ($request->hasFile('logo')) {
            if ($circuit->logo) {
                Storage::disk('public')->delete($circuit->logo);
            }

            $data['logo'] = $request->file('logo')->store('track/logo', 'public');
        }

        if ($request->hasFile('logo_dark')) {
            if ($circuit->logo_dark) {
                Storage::disk('public')->delete($circuit->logo_dark);
            }

            $data['logo_dark'] = $request->file('logo_dark')->store('track/logo_dark', 'public');
        }

        if ($request->hasFile('logo_light')) {
            if ($circuit->logo_light) {
                Storage::disk('public')->delete($circuit->logo_light);
            }

            $data['logo_light'] = $request->file('logo_light')->store('track/logo_light', 'public');
        }

        if ($request->hasFile('map')) {
            if ($circuit->map) {
                Storage::disk('public')->delete($circuit->map);
            }

            $data['map'] = $request->file('map')->store('track/mapsgo', 'public');
        }

        $circuit->update($data);

        return redirect()->route('circuits.index')
            ->with('success', 'Circuito actualizado correctamente');
    }

    public function destroy(Track $circuit)
    {
        $circuit->delete();
        return redirect()->route('circuits.index')->with('success', 'Circuito eliminado correctamente');
    }

    public function exportBs90(TracksExporter $exporter)
    {
        $content = $exporter->build();

        return response($content, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="BS90.Data.Tracks.js"',
        ]);
    }











}
