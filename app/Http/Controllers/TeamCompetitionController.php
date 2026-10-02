<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Series;
use App\Models\SeriesEntry;
use App\Models\Workspace;
use App\Models\SeriesEntryMember;
use App\Services\CompetitionStrategyService;
use App\Services\TeamCompetitionStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamCompetitionController extends Controller
{
    /**
     * Show the competitions in which the current Team participates.
     */

    /**
 * Determine whether the current user
 * can manage Team competition drivers.
 */
    private function canManageCompetition(
        User $user
    ): bool {
        return in_array(
            $user->driver_role,
            [
                'team_owner',
                'team_director',
            ],
            true
        );
    }

    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        |
        | The Team Competition area requires the user to
        | belong to a Team.
        |
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team competitions
        |--------------------------------------------------------------------------
        |
        | The Team Workspace is the source of context.
        | We retrieve active/pending entries belonging
        | to the current Team Workspace.
        |
        */

        $entries = SeriesEntry::query()
            ->with([
                'workspace',
                'series.iracingSeries',
                'competitionCar',
                'members.user',
            ])
            ->whereIn('status', [
                'pending',
                'active',
            ])
            ->whereHas(
                'workspace',
                function ($query) use ($user) {

                    $query
                        ->where(
                            'type',
                            'team'
                        )
                        ->where(
                            'team_id',
                            $user->team_id
                        )
                        ->where(
                            'is_active',
                            true
                        );
                }
            )
            ->orderBy('joined_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'team.competitions.index',
            [
                'competitions' => $entries,
            ]
        );
    }


    /**
     * Show competitions available for Team registration.
     */
    public function create()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Existing Team registrations
        |--------------------------------------------------------------------------
        */

        $registeredSeriesIds = SeriesEntry::query()
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->pluck('series_id');

        /*
        |--------------------------------------------------------------------------
        | Available competitions
        |--------------------------------------------------------------------------
        */

        $series = Series::query()
            ->with([
                'iracingSeries.cars',
            ])
            ->whereNotIn(
                'id',
                $registeredSeriesIds
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Registration view
        |--------------------------------------------------------------------------
        */

        return view(
            'team.competitions.create',
            [
                'series' => $series,
            ]
        );
    }


    /**
     * Show Team competition registration form.
     */
    public function register(Series $series)
    {
        $user = auth()->user();

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate Team registration
        |--------------------------------------------------------------------------
        */

        $alreadyRegistered = SeriesEntry::query()
            ->where('workspace_id', $workspace->id)
            ->where('series_id', $series->id)
            ->whereIn('status', [
                'pending',
                'active',
            ])
            ->exists();

        abort_if(
            $alreadyRegistered,
            409,
            'Team is already registered in this competition.'
        );

        /*
        |--------------------------------------------------------------------------
        | Competition cars
        |--------------------------------------------------------------------------
        */

        $series->load([
            'iracingSeries.cars',
            'rounds.track',
        ]);

        return view(
            'team.competitions.register',
            [
                'series' => $series,
            ]
        );
    }


    /**
     * Store a Team competition registration.
     */
    public function store(Request $request, Series $series)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validate registration data
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'competition_car_id' => [
                'required',
                'integer',
                Rule::exists(
                    'iracing_series_cars',
                    'car_id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'series_id',
                            $series->iracing_series_id
                        )
                ),
            ],

            'data_policy' => [
                'required',
                Rule::in([
                    'shared',
                    'compartmented',
                ]),
            ],

            'commitment' => [
                'accepted',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Existing Team participation
        |--------------------------------------------------------------------------
        |
        | A Team cannot have more than one active/pending
        | participation in the same series.
        |
        */

        $existingEntry = SeriesEntry::query()
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->first();

        if (
            $existingEntry
            && in_array(
                $existingEntry->status,
                [
                    'pending',
                    'active',
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'team.competitions.register',
                    $series
                )
                ->withErrors([
                    'competition' =>
                        'Your team is already registered for this competition.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reactivate withdrawn participation
        |--------------------------------------------------------------------------
        */

        if (
            $existingEntry
            && $existingEntry->status === 'withdrawn'
        ) {
            DB::transaction(
                function () use (
                    $existingEntry,
                    $data,
                    $user
                ) {

                    $existingEntry->update([
                        'competition_car_id' =>
                            $data['competition_car_id'],

                        'data_policy' =>
                            $data['data_policy'],

                        'status' =>
                            'active',

                        'joined_at' =>
                            now(),

                        'left_at' =>
                            null,

                        'created_by' =>
                            $user->id,
                    ]);
                }
            );

            return redirect()
                ->route(
                    'team.competitions.index'
                )
                ->with(
                    'success',
                    'Your team is now registered for this competition.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create new Team participation
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $series,
                $workspace,
                $data,
                $user
            ) {

                SeriesEntry::create([
                    'workspace_id' =>
                        $workspace->id,

                    'series_id' =>
                        $series->id,

                    'competition_car_id' =>
                        $data['competition_car_id'],

                    'data_policy' =>
                        $data['data_policy'],

                    'status' =>
                        'active',

                    'joined_at' =>
                        now(),

                    'created_by' =>
                        $user->id,
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'team.competitions.index'
            )
            ->with(
                'success',
                'Your team is now registered for this competition.'
            );
    }


    /**
     * Show a Team competition.
     */
    public function show(Series $series)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->with([
                'workspace.team',
                'series.iracingSeries',
                'series.rounds.track',
                'competitionCar',
                'members.user',
            ])
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->firstOrFail();

        $series->load([
            'iracingSeries',
            'rounds.track',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Round
        |--------------------------------------------------------------------------
        */

        $currentRound = $series->currentRound();

        $displayRound = $series->displayRound();

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
            TeamCompetitionStatsService::class
        )->build(
            $entry,
            $currentRound,
            $reportScope
        );

        /*
        |--------------------------------------------------------------------------
        | Team data
        |--------------------------------------------------------------------------
        */

        $team = $entry->workspace->team;

        $teamCar = $team->teamCars()
            ->where(
                'car_id',
                $entry->competition_car_id
            )
            ->first();

        $teamMembers = $entry->workspace
            ->team
            ->members()
            ->orderBy('name')
            ->get();

        $canManageCompetition = $this->canManageCompetition($user);

        return view(
            'team.competitions.show',
            [
                'series' => $series,
                'entry' => $entry,
                'currentRound' => $currentRound,
                'displayRound' => $displayRound,
                'competitionStats' => $competitionStats,
                'reportScope' => $reportScope,
                'team' => $team,
                'teamCar' => $teamCar,
                'teamMembers' => $teamMembers,
                'canManageCompetition' => $canManageCompetition,
            ]
        );
    }


public function strategy(Series $series)
{
    $workspace = workspace()->workspace();

    $entry = SeriesEntry::query()
        ->with([
            'workspace.team',
            'series.iracingSeries',
            'series.rounds.track',
            'competitionCar',
            'members.user',
        ])
        ->where('workspace_id', $workspace->id)
        ->where('series_id', $series->id)
        ->where('status', 'active')
        ->firstOrFail();

    $currentRound = $entry->series->activeRound();

    $reportScope = request(
        'report_scope',
        'season'
    );

    $stats = app(
        TeamCompetitionStatsService::class
    )->build(
        $entry,
        $currentRound,
        $reportScope
    );

    $config = session(
        'team_strategy_config',
        []
    );

    $plan = app(
        CompetitionStrategyService::class
    )->build(
        $entry,
        $currentRound,
        $reportScope,
        $stats,
        $config
    );

    return view(
        'team.competitions.strategy',
        compact(
            'series',
            'entry',
            'currentRound',
            'plan',
            'reportScope'
        )
    );
}


public function strategyUpdate(Request $request, Series $series)
{
    $margin = (int) $request->input('margin_laps', 0);
    $fuel   = (float) $request->input('extra_fuel', 0);

    session([
        'team_strategy_config' => [
            'margin_laps' => $margin,
            'extra_fuel'  => $fuel,
        ]
    ]);

    return back()->with('success', 'Contingencia aplicada');
}


public function strategyReset(Series $series)
{
    session()->forget('team_strategy_config');

    return back()->with('success', 'Contingencia eliminada');
}

public function strategyFavorite(Request $request, Series $series)
{
    session([
        'team_strategy_favorite' => $request->pattern,
    ]);

    return back();
}

/**
 * Show Team competition sessions.
 */
public function sessions(Series $series)
{
    $user = auth()->user();

    abort_unless(
        $user->team_id !== null,
        403
    );

    $workspace = Workspace::query()
        ->where('type', 'team')
        ->where('team_id', $user->team_id)
        ->where('is_active', true)
        ->firstOrFail();

    $entry = SeriesEntry::query()
        ->with([
            'series.rounds.track',
            'competitionCar',
            'workspace',
            'members',
        ])
        ->where('workspace_id', $workspace->id)
        ->where('series_id', $series->id)
        ->where('status', 'active')
        ->firstOrFail();

    $activeRound = $entry->series->activeRound();

    if (! $activeRound || ! $activeRound->track) {
        return redirect()
            ->route('team.competitions.show', $series)
            ->with(
                'error',
                'No hay combinación activa de circuito.'
            );
    }

    $competitionCar = $entry->competitionCar;

    if (! $competitionCar) {
        return redirect()
            ->route('team.competitions.show', $series)
            ->with(
                'error',
                'No hay coche asociado a la participación.'
            );
    }

    $driverIds = $entry->members
        ->where('status', 'active')
        ->pluck('user_id')
        ->unique()
        ->values();

    if ($driverIds->isEmpty()) {
        $sessions = collect();

        return view(
            'sessions.index',
            compact(
                'series',
                'entry',
                'activeRound',
                'competitionCar',
                'sessions'
            )
        );
    }

    $carId = $competitionCar->iracing_car_id;

    $trackId = $activeRound
        ->track
        ->iracing_track_id;

    $subsessionIds = app(
        \App\Services\StintAccessService::class
    )
        ->query()
        ->whereIn('user_id', $driverIds)
        ->where('car_id', $carId)
        ->where('track_id', $trackId)
        ->whereNotNull('iracing_subsession_id')
        ->pluck('iracing_subsession_id')
        ->unique()
        ->values();

    $sessions = \App\Models\IrSession::query()
        ->whereIn(
            'iracing_subsession_id',
            $subsessionIds
        )
        ->withCount('stints')
        ->orderByDesc('created_at')
        ->paginate(15);

    return view(
        'sessions.index',
        compact(
            'series',
            'entry',
            'activeRound',
            'competitionCar',
            'sessions'
        )
    );
}


/**
 * Show Team competition stints.
 */
public function stints(Series $series)
{
    $user = auth()->user();

    $reportScope = request(
        'report_scope',
        'season'
    );

    abort_unless(
        $user->team_id !== null,
        403
    );

    $workspace = Workspace::query()
        ->where('type', 'team')
        ->where('team_id', $user->team_id)
        ->where('is_active', true)
        ->firstOrFail();

    $entry = SeriesEntry::query()
        ->with([
            'series.rounds.track',
            'competitionCar',
            'workspace',
            'members',
        ])
        ->where('workspace_id', $workspace->id)
        ->where('series_id', $series->id)
        ->where('status', 'active')
        ->firstOrFail();

    $activeRound = $entry->series->activeRound();

    if (! $activeRound || ! $activeRound->track) {
        return redirect()
            ->route('team.competitions.show', $series)
            ->with(
                'error',
                'No hay combinación activa de circuito.'
            );
    }

    $competitionCar = $entry->competitionCar;

    if (! $competitionCar) {
        return redirect()
            ->route('team.competitions.show', $series)
            ->with(
                'error',
                'No hay coche asociado a la participación.'
            );
    }

    $driverIds = $entry->members
        ->where('status', 'active')
        ->pluck('user_id')
        ->unique()
        ->values();

    $carId = $competitionCar->iracing_car_id;

    $trackId = $activeRound
        ->track
        ->iracing_track_id;

    $stints = app(
        \App\Services\StintAccessService::class
    )
        ->query()
        ->with([
            'laps',
            'session',
            'track',
            'user',
        ])
        ->withCount('laps')
        ->whereIn('user_id', $driverIds)
        ->where('car_id', $carId)
        ->where('track_id', $trackId)
        ->having('laps_count', '>=', 2)
        ->orderByDesc('created_at')
        ->paginate(15);

    return view(
        'stints.index',
        compact(
            'series',
            'entry',
            'activeRound',
            'competitionCar',
            'stints'
        )
    );
}




    /**
     * Show Team driver registration form.
     */
    public function createDriver(Series $series)
    {
        $user = auth()->user();

        abort_unless(
            $this->canManageCompetition($user),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->with('members')
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team members already assigned
        |--------------------------------------------------------------------------
        */

        $assignedUserIds = $entry->members
            ->where(
                'status',
                'active'
            )
            ->pluck('user_id');

        /*
        |--------------------------------------------------------------------------
        | Available Team members
        |--------------------------------------------------------------------------
        */

        $teamMembers = $workspace->team
            ->members()
            ->whereNotIn(
                'id',
                $assignedUserIds
            )
            ->orderBy('name')
            ->get();

        return view(
            'team.competitions.drivers.create',
            [
                'series' => $series,
                'entry' => $entry,
                'teamMembers' => $teamMembers,
            ]
        );
    }


    /**
     * Add a Team member to a competition.
     */
    public function addDriver(Request $request, Series $series)
    {
        $user = auth()->user();

        abort_unless(
            $this->canManageCompetition($user),
            403
        );



        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validate selected Team member
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists(
                    'users',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'team_id',
                            $user->team_id
                        )
                ),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate active membership
        |--------------------------------------------------------------------------
        */

        $existingMember = SeriesEntryMember::query()
            ->where(
                'series_entry_id',
                $entry->id
            )
            ->where(
                'user_id',
                $data['user_id']
            )
            ->first();

        if (
            $existingMember
            && $existingMember->status === 'active'
        ) {
            return redirect()
                ->route(
                    'team.competitions.show',
                    $series
                )
                ->withErrors([
                    'driver' =>
                        'This driver is already participating in this competition.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Restore withdrawn membership
        |--------------------------------------------------------------------------
        */

        if (
            $existingMember
            && $existingMember->status === 'withdrawn'
        ) {
            $existingMember->update([
                'status' => 'active',
                'joined_at' => now(),
                'left_at' => null,
            ]);

            return redirect()
                ->route(
                    'team.competitions.show',
                    $series
                )
                ->with(
                    'success',
                    'Driver added to the competition.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create competition membership
        |--------------------------------------------------------------------------
        */

        SeriesEntryMember::create([
            'series_entry_id' => $entry->id,
            'user_id' => $data['user_id'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'team.competitions.show',
                $series
            )
            ->with(
                'success',
                'Driver added to the competition.'
            );
    }


    /**
     * Update a Team driver's competition role.
     */
    public function updateDriver(
        Request $request,
        Series $series,
        SeriesEntryMember $member
    ) {
        $user = auth()->user();

        abort_unless(
            $this->canManageCompetition($user),
            403
        );



        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Member must belong to this competition
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $member->series_entry_id === $entry->id,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Validate competition role
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'role' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update competition label
        |--------------------------------------------------------------------------
        */

        $member->update([
            'role' => $data['role'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'team.competitions.show',
                $series
            )
            ->with(
                'success',
                'Driver competition role updated.'
            );
    }

    /**
     * Cancel a driver's participation in a Team competition.
     */
    public function removeDriver(
        Series $series,
        SeriesEntryMember $member
    ) {

        $user = auth()->user();

        abort_unless(
            $this->canManageCompetition($user),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team access
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->team_id !== null,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Team Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'team')
            ->where('team_id', $user->team_id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Team participation
        |--------------------------------------------------------------------------
        */

        $entry = SeriesEntry::query()
            ->where(
                'workspace_id',
                $workspace->id
            )
            ->where(
                'series_id',
                $series->id
            )
            ->whereIn(
                'status',
                [
                    'pending',
                    'active',
                ]
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Member must belong to this competition
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $member->series_entry_id === $entry->id,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Cancel participation
        |--------------------------------------------------------------------------
        */
        if ($member->status !== 'active') {
            return redirect()
                ->route(
                    'team.competitions.show',
                    $series
                )
                ->withErrors([
                    'driver' =>
                        'This driver is not currently participating in this competition.',
                ]);
        }

        $member->update([
            'status' => 'withdrawn',
            'left_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Finish
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'team.competitions.show',
                $series
            )
            ->with(
                'success',
                'Driver participation cancelled.'
            );
    }





}
