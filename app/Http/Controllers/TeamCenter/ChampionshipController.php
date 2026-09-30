<?php

namespace App\Http\Controllers\TeamCenter;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\SeriesEntry;
use App\Models\SeriesStanding;
use App\Models\SeriesStandingDriver;
use App\Services\TeamCenter\TeamCenterContext;

class ChampionshipController extends Controller
{
    /**
     * TeamCenter Championship catalog.
     *
     * Shows all championships available in the TeamCenter
     * context and identifies the championships in which
     * the current Team participates.
     */
    public function index(TeamCenterContext $context)
    {
        $team = $context->team;

        /*
        |--------------------------------------------------------------------------
        | Championships del Team
        |--------------------------------------------------------------------------
        */

        $championships = Series::query()
            ->with([
                'iracingSeries',
            ])
            ->whereHas(
                'entries',
                function ($query) use ($team) {

                    $query
                        ->whereIn('status', [
                            'pending',
                            'active',
                        ])
                        ->whereHas(
                            'workspace',
                            function ($workspaceQuery) use ($team) {

                                $workspaceQuery
                                    ->where('type', 'team')
                                    ->where('team_id', $team->id)
                                    ->where('is_active', true);
                            }
                        );
                }
            )
            ->orderByDesc('season_year')
            ->orderByDesc('season_number')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'teamcenter.championships.index',
            [
                'team' => $team,
                'championships' => $championships,
            ]
        );
    }


    public function show(
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
                'workspace',
                'competitionCar',
                'members.user',
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
        | Championship data
        |--------------------------------------------------------------------------
        */

        $series->load([
            'iracingSeries',
            'rounds.track',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Round reference
        |--------------------------------------------------------------------------
        */

        $currentRound = $series->currentRound();

        $displayRound = $series->displayRound();


        /*
        |--------------------------------------------------------------------------
        | Team car
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | This is the TeamCar, not the generic CompetitionCar.
        |
        */

        $teamCar = $team->teamCars()
            ->where(
                'car_id',
                $entry->competition_car_id
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Team members participating
        |--------------------------------------------------------------------------
        */

        $competitionMembers = $entry->members
            ->where('status', 'active')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Team members
        |--------------------------------------------------------------------------
        */

        $teamMembers = $team->users;


        /*
        |--------------------------------------------------------------------------
        | Competition permissions
        |--------------------------------------------------------------------------
        */

        $canManageCompetition = in_array(
            $context->user->driver_role,
            [
                'team_owner',
                'team_director',
            ],
            true
        );


        /*
        |--------------------------------------------------------------------------
        | Competition statistics
        |--------------------------------------------------------------------------
        */

        $reportScope = request(
            'report_scope',
            'season'
        );

        $competitionStats = app(
            \App\Services\CompetitionStatsService::class
        )->build(
            $entry,
            $currentRound,
            $reportScope
        );


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'teamcenter.championships.show',
            [
                'team' => $team,
                'series' => $series,
                'entry' => $entry,

                'currentRound' => $currentRound,
                'displayRound' => $displayRound,

                'competitionStats' => $competitionStats,
                'reportScope' => $reportScope,

                'teamCar' => $teamCar,

                'teamMembers' => $teamMembers,
                'competitionMembers' => $competitionMembers,

                'canManageCompetition' => $canManageCompetition,
            ]
        );
    }


    /**
     * Championship standings.
     *
     * Shows the season standings for the current TeamCenter
     * championship.
     */
    public function standings(
        Series $series,
        TeamCenterContext $context
    ) {
        $user = $context->user;

        /*
        |--------------------------------------------------------------------------
        | Clasificación general de temporada
        |--------------------------------------------------------------------------
        */

        $overallStanding = SeriesStanding::query()
            ->where('series_id', $series->id)
            ->where('scope', 'overall')
            ->where('division_key', -1)
            ->where('race_week_num', -1)
            ->first();

        if (!$overallStanding) {
            return response()->view(
                'teamcenter.championships.partials.standings',
                [
                    'overallDrivers' => collect(),
                    'divisionDrivers' => collect(),
                    'division' => null,
                    'currentCustId' => $user->iracing_user_id,
                    'hasStandings' => true,
                    'series' => $series,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Piloto actual dentro de Overall
        |--------------------------------------------------------------------------
        */

        $currentDriver = SeriesStandingDriver::query()
            ->where('series_standing_id', $overallStanding->id)
            ->where('cust_id', $user->iracing_user_id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | División del piloto
        |--------------------------------------------------------------------------
        */

        $division = $currentDriver?->division;

        /*
        |--------------------------------------------------------------------------
        | Overall
        |--------------------------------------------------------------------------
        */

        $overallDrivers = SeriesStandingDriver::query()
            ->where('series_standing_id', $overallStanding->id)
            ->orderBy('rank')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Clasificación de la división del piloto
        |--------------------------------------------------------------------------
        */

        $divisionDrivers = collect();

        if ($division !== null) {

            $divisionStanding = SeriesStanding::query()
                ->where('series_id', $series->id)
                ->where('scope', 'division')
                ->where('division_key', (int) $division)
                ->where('race_week_num', -1)
                ->first();

            if ($divisionStanding) {
                $divisionDrivers = SeriesStandingDriver::query()
                    ->where('series_standing_id', $divisionStanding->id)
                    ->orderBy('rank')
                    ->get();
            }
        }

        return response()->view(
            'teamcenter.championships.partials.standings',
            [
                'overallDrivers' => $overallDrivers,
                'divisionDrivers' => $divisionDrivers,
                'division' => $division,
                'currentCustId' => $user->iracing_user_id,
                'hasStandings' => true,
                'series' => $series,
            ]
        );
    }









}
