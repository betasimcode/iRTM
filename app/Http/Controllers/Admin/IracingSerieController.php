<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\IracingSerie;
use App\Models\Car;
use Illuminate\Http\Request;

class IracingSerieController extends Controller
{
    public function index()
    {
        $series = IracingSerie::orderBy('name')->paginate(20);
        $cars = Car::orderBy('name')->get();

        return view('admin.iracing_series.index', compact('series','cars'));
    }

    public function create()
    {
        $cars = Car::orderBy('name')->get();
        return view(

            'admin.iracing_series.create',

            compact('cars')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ir_url' => 'nullable|string|max:255',
            'stats_url' => 'nullable|string|max:255',
            'serie_info' => 'nullable|string',
            'official_forum' => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'iracing_series_id' => 'nullable|integer|unique:iracing_series,iracing_series_id',
            'iracing_class' => 'required|string|max:25',
            // 🔥 clase del coche real
            'discipline' => 'required|string|max:50',
            'category' => 'required|string|max:50',

            'logo' => 'nullable|image|max:2048',
            'background' => 'nullable|image|max:2048',

            'cars' => 'nullable|array',
            'cars.*' => 'exists:cars,id',

            'race_type' => 'required|in:laps,time',
            'start_type' => 'required|in:standing,rolling',

            'race_length' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:race_type,time',
            ],

            'fuel_limit' => 'nullable|numeric|min:1|max:100',
            'tank_capacity_override' => 'nullable|numeric|min:1',
            'mandatory_pit' => 'nullable|boolean',
            'refuel_allowed' => 'nullable|boolean',
            'setup_type' => 'nullable|in:fixed,open',
            'fast_repair' => 'nullable|boolean',
            'drive_through_limit' => 'nullable|integer',
            'disqualification_limit' => 'nullable|integer',
            'has_additional_penalties' => 'nullable|boolean',
            'network_quality_rule' => 'nullable|boolean',
            'grid_by_class' => 'nullable|boolean',
            'tire_rules' => 'nullable|boolean',
            'quali_tires' => 'nullable|boolean',
            'joker_laps' => 'nullable|boolean',
            'team_rules' => 'nullable|boolean',
            'quali_scrutiny' => 'nullable|in:none,lenient,strict',

            'week_start_day' => 'nullable|string',
            'week_start_time' => 'nullable',
            'race_interval_minutes' => 'nullable|integer',
            'registration_open_minutes' => 'nullable|integer',
        ]);

        // Logo
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('iracing-series', 'public');
        }
        if ($request->hasFile('background')) {
            $data['background_img'] = $request->file('background')->store('iracing-series', 'public');
        }
            $data['fast_repair'] = $request->has('fast_repair');
            $data['has_additional_penalties'] = $request->has('has_additional_penalties');
            $data['network_quality_rule'] = $request->has('network_quality_rule');
            $data['grid_by_class'] = $request->has('grid_by_class');
            $data['tire_rules'] = $request->has('tire_rules');
            $data['quali_tires'] = $request->has('quali_tires');
            $data['joker_laps'] = $request->has('joker_laps');
            $data['team_rules'] = $request->has('team_rules');

            $series = IracingSerie::create($data);

            $series->cars()->sync(

                $request->cars ?? []
            );

        return redirect()
            ->route('admin.iracing-series.index')
            ->with('success', 'Serie creada correctamente');
    }

    public function edit(IracingSerie $iracingSeries)
    {
        $cars = Car::orderBy('name')->get();

        return view(

            'admin.iracing_series.edit',

            compact(
                'iracingSeries',
                'cars'
            )
        );
    }

    public function update(Request $request, IracingSerie $iracingSeries)
    {
        $data = $request->validate([
        'name' => 'required|string|max:255',
        'ir_url' => 'nullable|string|max:255',
        'stats_url' => 'nullable|string|max:255',
        'serie_info' => 'nullable|string',
        'official_forum' => 'nullable|string|max:255',
        'short_name' => 'nullable|string|max:50',
        'iracing_series_id' => [
            'nullable',
            'integer',
            Rule::unique('iracing_series', 'iracing_series_id')
                ->ignore($iracingSeries->id),
        ],
        'iracing_class' => 'required|string|max:25',
        // 🔥 clase del coche real
        'discipline' => 'required|string|max:50',
        'category' => 'required|string|max:50',

        'logo' => 'nullable|image|max:2048',
        'background' => 'nullable|image|max:2048',

        'cars' => 'nullable|array',
        'cars.*' => 'exists:cars,id',

        'race_type' => 'required|in:laps,time',
        'start_type' => 'required|in:standing,rolling',

        'race_length' => [
            'nullable',
            'integer',
            'min:1',
            'required_if:race_type,time',
        ],

        'setup_type' => 'nullable|in:fixed,open',
        'fast_repair' => 'nullable|boolean',
        'drive_through_limit' => 'nullable|integer',
        'disqualification_limit' => 'nullable|integer',
        'has_additional_penalties' => 'nullable|boolean',
        'network_quality_rule' => 'nullable|boolean',
        'grid_by_class' => 'nullable|boolean',
        'tire_rules' => 'nullable|boolean',
        'quali_tires' => 'nullable|boolean',
        'joker_laps' => 'nullable|boolean',
        'team_rules' => 'nullable|boolean',
        'quali_scrutiny' => 'nullable|in:none,lenient,strict',

        'week_start_day' => 'nullable|string',
        'week_start_time' => 'nullable',
        'race_interval_minutes' => 'nullable|integer',
        'registration_open_minutes' => 'nullable|integer',

        'fuel_limit' => 'nullable|numeric|min:1|max:100',
        'tank_capacity_override' => 'nullable|numeric|min:1',
        'mandatory_pit' => 'nullable|boolean',
        'refuel_allowed' => 'nullable|boolean',
    ]);

        // Logo
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('iracing-series', 'public');
        }
        if ($request->hasFile('background')) {
            $data['background_img'] = $request->file('background')->store('iracing-series', 'public');
        }

        $data['fast_repair'] = $request->has('fast_repair');
        $data['has_additional_penalties'] = $request->has('has_additional_penalties');
        $data['network_quality_rule'] = $request->has('network_quality_rule');
        $data['grid_by_class'] = $request->has('grid_by_class');
        $data['tire_rules'] = $request->has('tire_rules');
        $data['quali_tires'] = $request->has('quali_tires');
        $data['joker_laps'] = $request->has('joker_laps');
        $data['team_rules'] = $request->has('team_rules');

        $iracingSeries->update($data);

        $iracingSeries->cars()->sync(
            $request->cars ?? []
        );

        return redirect()
            ->route('admin.iracing-series.index')
            ->with('success', 'Serie actualizada');
    }

    public function destroy(IracingSerie $iracingSeries)
    {
        $iracingSeries->delete();

        return back()->with('success', 'Serie eliminada');
    }
}
