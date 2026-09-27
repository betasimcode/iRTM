<?php

namespace App\Http\Controllers\TeamCenter;

use App\Http\Controllers\Controller;
use App\Models\IrSession;
use App\Models\Series;
use App\Models\SeriesEntry;
use App\Services\StintAccessService;
use App\Services\TeamCenter\TeamCenterContext;

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
}
