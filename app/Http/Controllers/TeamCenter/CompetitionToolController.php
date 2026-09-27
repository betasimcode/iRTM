<?php

namespace App\Http\Controllers\TeamCenter;

use App\Http\Controllers\Controller;
use App\Models\IrSession;
use App\Models\Series;
use App\Models\SeriesEntry;
use App\Services\StintAccessService;
use App\Services\TeamCenter\TeamCenterContext;
use Illuminate\Http\Request;
use App\Services\CompetitionStrategyService;
use App\Services\TeamCompetitionStatsService;

class CompetitionToolController extends Controller
{
    /**
     * Show Team competition sessions inside TeamCenter.
     */
    public function sessions(
        Series $series,
        TeamCenterContext $context
    ) {
        $team = $context->team;

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->with([
                'series.rounds.track',
                'competitionCar',
                'workspace',
                'members',
            ])
            ->where('series_id', $series->id)
            ->whereIn('status', [
                'pending',
                'active',
            ])
            ->whereHas(
                'workspace',
                function ($query) use ($team) {
                    $query
                        ->where('type', 'team')
                        ->where('team_id', $team->id)
                        ->where('is_active', true);
                }
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Active round
        |--------------------------------------------------------------------------
        */

        $activeRound = $series->currentRound();

        if (! $activeRound || ! $activeRound->track) {
            return view(
                'teamcenter.championships.partials.sessions',
                [
                    'sessions' => collect(),
                    'activeRound' => $activeRound,
                    'competitionCar' => $entry->competitionCar,
                    'message' => 'No hay una ronda activa en este momento.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Competition car
        |--------------------------------------------------------------------------
        */

        $competitionCar = $entry->competitionCar;

        if (! $competitionCar) {
            return view(
                'teamcenter.championships.partials.sessions',
                [
                    'sessions' => collect(),
                    'activeRound' => $activeRound,
                    'competitionCar' => null,
                    'message' => 'No hay coche asociado a la participación.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Active Team drivers
        |--------------------------------------------------------------------------
        */

        $driverIds = $entry->members
            ->where('status', 'active')
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($driverIds->isEmpty()) {
            return view(
                'teamcenter.championships.partials.sessions',
                [
                    'sessions' => collect(),
                    'activeRound' => $activeRound,
                    'competitionCar' => $competitionCar,
                    'message' => 'No hay pilotos activos en esta competición.',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Session access
        |--------------------------------------------------------------------------
        |
        | ir_stints.car_id and ir_stints.track_id store the iRacing IDs.
        |
        | cars.id and tracks.id are internal database IDs and must not
        | be used to filter the telemetry stints.
        |
        */

        $carId = $competitionCar->iracing_car_id;

        $trackId = $activeRound->track->iracing_track_id;

        $stintQuery = app(
            StintAccessService::class
        )
            ->query()
            ->whereIn(
                'user_id',
                $driverIds
            )
            ->where(
                'car_id',
                $carId
            )
            ->where(
                'track_id',
                $trackId
            )
            ->whereNotNull(
                'iracing_subsession_id'
            );

        /*
        |--------------------------------------------------------------------------
        | Debug
        |--------------------------------------------------------------------------
        */

        logger()->info('TEAMCENTER STINT FILTER', [
            'team_id' => $team->id,
            'series_id' => $series->id,
            'round_id' => $activeRound->id,
            'track_id' => $trackId,
            'car_id' => $carId,
            'driver_ids' => $driverIds,
            'stint_count' => (clone $stintQuery)->count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Subsessions
        |--------------------------------------------------------------------------
        */

        $subsessionIds = $stintQuery
            ->pluck('iracing_subsession_id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        */

        $sessions = IrSession::query()
            ->whereIn(
                'iracing_subsession_id',
                $subsessionIds
            )
            ->withCount('stints')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view(
            'teamcenter.championships.partials.sessions',
            [
                'sessions' => $sessions,
                'activeRound' => $activeRound,
                'competitionCar' => $competitionCar,
                'message' => null,
            ]
        );
    }


    public function strategy(
        Series $series,
        TeamCenterContext $context
    ) {
        $team = $context->team;

        $entry = SeriesEntry::query()
            ->with([
                'series.iracingSeries',
                'series.rounds.track',
                'competitionCar',
                'workspace',
                'members.user',
            ])
            ->where('series_id', $series->id)
            ->whereIn('status', ['pending', 'active'])
            ->whereHas('workspace', function ($query) use ($team) {
                $query
                    ->where('type', 'team')
                    ->where('team_id', $team->id)
                    ->where('is_active', true);
            })
            ->firstOrFail();

        $series = $entry->series;

        $currentRound = $series->currentRound();
        $displayRound = $series->displayRound();

        $reportScope = request('report_scope', 'season');

        abort_unless(
            in_array($reportScope, ['week', 'season', 'all'], true),
            422
        );

        $stats = app(
            TeamCompetitionStatsService::class
        )->build(
            $entry,
            $currentRound,
            $reportScope
        );

        $configKey = 'teamcenter.strategy.' . $series->id;

        $config = session()->get(
            $configKey . '.config',
            []
        );

        $plan = app(
            CompetitionStrategyService::class
        )->build(
            $entry,
            $displayRound,
            $reportScope,
            $stats,
            $config
        );

        $favorite = session()->get(
            $configKey . '.favorite'
        );

        $viewData = compact(
            'team',
            'series',
            'entry',
            'currentRound',
            'displayRound',
            'reportScope',
            'plan',
            'config',
            'favorite'
        );

        if (request()->ajax()) {
            return view(
                'teamcenter.championships.partials.strategy',
                $viewData
            );
        }

        return view(
            'teamcenter.championships.strategy',
            $viewData
        );
            }


    public function strategyUpdate(
        Request $request,
        Series $series,
        TeamCenterContext $context
    ) {
        $team = $context->team;

        // Validar que el equipo está inscrito.
        $this->resolveStrategyEntry($series, $team);

        $validated = $request->validate([
            'margin_laps' => ['required', 'integer', 'min:0'],
            'extra_fuel' => ['required', 'numeric', 'min:0'],
        ]);

        $key = 'teamcenter.strategy.' . $series->id;

        session()->put($key . '.config', [
            'margin_laps' => (int) $validated['margin_laps'],
            'extra_fuel' => (float) $validated['extra_fuel'],
        ]);

        return redirect()
            ->route('teamcenter.championships.strategy', $series)
            ->with('success', 'Contingencia aplicada.');
    }


    public function strategyReset(
        Series $series,
        TeamCenterContext $context
    ) {
        $this->resolveStrategyEntry(
            $series,
            $context->team
        );

        $key = 'teamcenter.strategy.' . $series->id;

        session()->forget($key . '.config');

        return redirect()
            ->route('teamcenter.championships.strategy', $series)
            ->with('success', 'Contingencia eliminada.');
    }


    public function strategyFavorite(
        Request $request,
        Series $series,
        TeamCenterContext $context
    ) {
        $this->resolveStrategyEntry(
            $series,
            $context->team
        );

        $validated = $request->validate([
            'pattern' => [
                'required',
                'in:base,conservative,aggressive,safety_car',
            ],
        ]);

        $key = 'teamcenter.strategy.' . $series->id;

        session()->put(
            $key . '.favorite',
            $validated['pattern']
        );

        return redirect()
            ->route('teamcenter.championships.strategy', $series);
    }


    private function resolveStrategyEntry(
        Series $series,
        $team
    ): SeriesEntry {
        return SeriesEntry::query()
            ->where('series_id', $series->id)
            ->whereIn('status', ['pending', 'active'])
            ->whereHas('workspace', function ($query) use ($team) {
                $query
                    ->where('type', 'team')
                    ->where('team_id', $team->id)
                    ->where('is_active', true);
            })
            ->firstOrFail();
    }








}
