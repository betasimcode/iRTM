<?php

namespace App\Http\Controllers;

use App\Models\Series;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\IracingSerie;
use App\Services\Racing\RaceScheduleService;
use App\Services\Racing\RaceTimelineService;
use App\Models\Track;
use App\Models\User;
use App\Models\Stint;
use App\Models\TeamCarSerie;
use App\Models\TeamCar;
use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use Carbon\Carbon;

class SeriesController extends Controller
{

    public function index()
    {
    $series = Series::with([
            'teamCar.car',
            'iracingSeries',
            'rounds.track'
        ])
        ->where('team_id', auth()->user()->team_id)
        ->latest()
        ->get();

    return view('series.index', compact('series'));
}

    public function create()
    {
        $team = auth()->user()->team;

        $teamCars = $team->teamCars()
            ->with('car')
            ->get();

        $iracingSeries = IracingSerie::orderBy('name')->get();

        return view('series.create', compact(
            'teamCars',
            'iracingSeries'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'iracing_series_id' => 'required|exists:iracing_series,id',
            'season_year' => 'required|integer',
            'season_number' => 'required|integer',
            'team_car_id' => 'required|exists:team_cars,id',
        ]);

        $team = auth()->user()->team;

        // 🔒 Seguridad
        $teamCar = TeamCar::where('id', $data['team_car_id'])
            ->where('team_id', $team->id)
            ->firstOrFail();

        $iracingSeries = IracingSerie::findOrFail($data['iracing_series_id']);

        $exists = Series::where('team_id', auth()->user()->team_id)
            ->where('iracing_series_id', $data['iracing_series_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors('Your team already has a car registered in this series');
        }

        // ✅ Crear serie
        Series::create([
            'name' => $iracingSeries->name,
            'iracing_series_id' => $iracingSeries->id,
            'season_year' => $data['season_year'],
            'season_number' => $data['season_number'],
            'team_id' => $team->id,
            'team_car_id' => $teamCar->id,
            'car_id' => $teamCar->car_id,
        ]);

        return redirect()
            ->route('series.index')
            ->with('success', 'Serie creada correctamente');
    }

    public function edit(Series $series)
    {

        return view('series.edit', compact('series'));
    }


    public function update(Request $request, Series $series)
    {
        if ($series->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => 'required|in:draft,active'
        ]);

        $series->update($data);

        return redirect()
            ->route('series.show', $series)
            ->with('success', 'Serie actualizada');
    }


    public function destroy(Series $series)
    {
        if ($series->rounds()->count() > 0) {
            return redirect()
                ->route('series.edit', $series)
                ->with('error', 'No puedes eliminar una serie que tiene semanas configuradas.');
        }

        $series->delete();

        return redirect()
            ->route('series.index')
            ->with('success', 'Serie eliminada correctamente.');
    }


    public function show(Series $series)
    {

        if ($series->team_id !== auth()->user()->team_id) {
            abort(403);
        }
        $series->load(['rounds.track','car','iracingSeries']);
        $workspace = workspace();
        $raceTimeline = app(RaceTimelineService::class)
            ->build(
                $series->id,
                false
            );
        $user = $workspace->user();

        $stats = $series->strategicStats($user->id, request('mode','season'));
        $plan  = $series->racePlan($user->id);

        $activeRound = $series->activeRound();
        $series->load('car');

        return view('series.show', compact(

            'series',
            'stats',
            'plan',
            'raceTimeline',
            'activeRound'
        ));
    }


    public function chooseCar(Request $request, $seriesId)
    {
        $user = $request->user();

        SeriesEntry::updateOrCreate(
            [
                'series_id' => $seriesId,
                'user_id'   => $user->id
            ],
            [
                'car_id' => $request->car_id
            ]
        );

        return back()->with('success','Coche asignado correctamente');
    }



public function manage(Series $series)
{
    $series->load('rounds.track'); // 🔥 cambio clave

    $tracks = Track::orderBy('name')->get(); // 🔥 usar tracks

    return view('series.manage', compact('series', 'tracks'));
}

    /// CREAR ROUND
    public function storeRound(Request $request, Series $series)
    {

        $request->validate([
            'week' => 'required|integer|min:1',
            'week_start' => 'required|date',
            'circuit_id' => 'required|exists:tracks,id',

            // Solo requerido si la serie es por vueltas
            'race_length' => 'nullable|integer|min:1',
        ]);

        $seriesConfig = $series->iracingSeries;

        $data = $request->only([
            'week',
            'week_start',
            'circuit_id',
        ]);

        // 🔥 Calcular automáticamente week_end
        $start = Carbon::parse($request->week_start);
        $data['week_end'] = $start->copy()->addDays(7);

        // 🔒 Siempre heredado
        $data['race_type'] = $seriesConfig->race_type;

        // 🔒 Lógica según tipo
        if ($seriesConfig->race_type === 'time') {
            $data['race_length'] = $seriesConfig->race_length;
        } else {
            // laps → obligatorio aquí
            if (!$request->race_length) {
                return back()->withErrors(['race_length' => 'Debes definir las vueltas']);
            }

            $data['race_length'] = $request->race_length;
        }

        $series->rounds()->create($data);

        return back()->with('success', 'Semana añadida');
    }

    /// EDITAR ROUND
    public function updateRound(Request $request, SeriesRound $round)
    {
        $seriesConfig = $round->series->iracingSeries;

        $data = $request->only([
            'week',
            'week_start',
            'circuit_id',
        ]);

        $start = Carbon::parse($request->week_start);
        $data['week_end'] = $start->copy()->addDays(7);

        $data['race_type'] = $seriesConfig->race_type;

        if ($seriesConfig->race_type === 'time') {
            $data['race_length'] = $seriesConfig->race_length;
        } else {
            if (!$request->race_length) {
                return back()->withErrors(['race_length' => 'Debes definir las vueltas']);
            }

            $data['race_length'] = $request->race_length;
        }

        $round->update($data);

        return back()->with('success', 'Semana actualizada');
    }

    /// ELIMINAR ROUND
    public function destroyRound(SeriesRound $round)
    {
        $round->delete();
        return back()->with('success', 'Semana eliminada');
    }

    public function plan(Series $series)
    {
        $series->load('rounds.track');

        $plan = $series->racePlan(auth()->id());

        return view('series.plan', compact('series', 'plan'));
    }

    public function favorite(Request $request, $series)
    {
        session(['strategy_favorite' => $request->pattern]);

        return back();
    }

    public function resetPlan($series)
    {
        session()->forget('strategy_config');

        return back()->with('success', 'Contingencia eliminada');
    }

    public function plan_update(Request $request, Series $series)
    {
        $margin = (int) $request->input('margin_laps', 0);
        $fuel   = (float) $request->input('extra_fuel', 0);

        session([
            'strategy_config' => [
                'margin_laps' => $margin,
                'extra_fuel'  => $fuel,
            ]
        ]);

        return back()->with('success', 'Contingencia aplicada');
    }

    public function stints(Series $series)
    {
        $series->load(['rounds.track', 'car']);
        $activeRound = $series->activeRound();
        $activeCar   = $series->car;
        if (!$activeRound || !$activeCar || !$activeRound->track) {
            return redirect()
                ->route('series.show', $series)
                ->with('error', 'No hay combinación activa.');
        }
        $stints = Stint::with([
            'laps',
            'session',
            'track',
            'user.team'
        ])
        ->withCount('laps') // 👈 cuenta automática
        ->where('car_id', $activeCar->iracing_car_id)
        ->where('track_id', $activeRound->track->iracing_track_id)
        ->where('user_id', auth()->id())
        ->having('laps_count', '>=', 2) // 👈 filtro limpio
        ->orderByDesc('created_at')
        ->paginate(15);

        return view('series.stints.index', compact(
            'series',
            'activeRound',
            'activeCar',
            'stints'
        ));
    }


    public function stintShow(Series $series, Stint $stint)
    {
        return redirect()->route(
            'stints.show',
            [
                'stint' => $stint,
                'from_series' => $series->id
            ]
        );
    }



}
