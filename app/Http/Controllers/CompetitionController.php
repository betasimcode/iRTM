<?php

namespace App\Http\Controllers;
use App\Models\IracingSerie;
use App\Models\Car;
use App\Models\Series;
use App\Models\SeriesEntry;
use App\Models\Stint;
use App\Models\SeriesEntryMember;
use App\Models\Workspace;
use App\Models\IrSession;
use App\Services\StintAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompetitionController extends Controller
{
    /**
     * Mis competiciones.
     */
    /**
 * Competitions available to the current Driver.
 *
 * The main Championships area always operates
 * inside the Driver Workspace.
 *
 * Team competitions in which the Driver participates
 * remain visible for operational purposes, but Team
 * management is not performed from this resource.
 */
    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Driver Workspace
        |--------------------------------------------------------------------------
        */

        $driverWorkspace = app(
            \App\Services\Workspace\WorkspaceResolver::class
        )->driver($user);

        abort_unless(
            $driverWorkspace !== null,
            403
        );

        session()->put(
            'workspace_id',
            $driverWorkspace->id
        );

        /*
        |--------------------------------------------------------------------------
        | Driver competitions
        |--------------------------------------------------------------------------
        |
        | Private competitions belonging to this Driver.
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
            ->where(function ($query) use (
                $driverWorkspace,
                $user
            ) {

                /*
                * Private Driver participation.
                */
                $query->where(
                    'workspace_id',
                    $driverWorkspace->id
                );

                /*
                |--------------------------------------------------------------------------
                | Team participation
                |--------------------------------------------------------------------------
                |
                | A Driver may see a Team competition here only
                | when they are an active member of that entry.
                |
                | This is informational/operational access.
                | It is NOT Team management access.
                |
                */

                if ($user->team_id) {

                    $query->orWhere(function ($team) use ($user) {

                        $team
                            ->whereHas(
                                'workspace',
                                function ($workspace) use ($user) {

                                    $workspace
                                        ->where('type', 'team')
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
                            ->whereHas(
                                'members',
                                function ($member) use ($user) {

                                    $member
                                        ->where(
                                            'user_id',
                                            $user->id
                                        )
                                        ->where(
                                            'status',
                                            'active'
                                        );
                                }
                            );
                    });
                }
            })
            ->orderBy('joined_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Participation type
        |--------------------------------------------------------------------------
        */

        $competitions = $entries->map(
            function ($entry) {

                $entry->participation_type =
                    $entry->workspace->type === 'team'
                        ? 'team'
                        : 'private';

                return $entry;
            }
        );

        return view(
            'competitions.index',
            [
                'competitions' => $competitions,
            ]
        );
    }

    public function show(Series $series)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Resolve current Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = workspace();

        $currentWorkspace = $workspace->workspace();

        /*
        |--------------------------------------------------------------------------
        | Competition entry
        |--------------------------------------------------------------------------
        |
        | The competition is always resolved inside the
        | current workspace.
        |
        */

        $entry = SeriesEntry::query()
            ->with([
                'workspace',
                'competitionCar',
                'members.user',
            ])
            ->where('workspace_id', $currentWorkspace->id)
            ->where('series_id', $series->id)
            ->whereIn('status', ['pending', 'active'])
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Competition data
        |--------------------------------------------------------------------------
        */

        $series->load([
            'iracingSeries',
            'rounds.track',
        ]);

        $today = now();

        $currentRound = $series->rounds
            ->first(function ($round) use ($today) {

                if (!$round->week_start) {
                    return false;
                }

                $start = \Carbon\Carbon::parse(
                    $round->week_start
                );

                $end = $round->week_end
                    ? \Carbon\Carbon::parse($round->week_end)
                    : $start->copy()->addDays(6);

                return $today->betweenIncluded(
                    $start,
                    $end
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Report scope
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
        | Team management permissions
        |--------------------------------------------------------------------------
        */

        $isTeamWorkspace =
            $currentWorkspace->type === 'team';

        $canManageTeamCompetition =
            $isTeamWorkspace
            && in_array($user->driver_role, [
                'team_owner',
                'team_director',
            ], true);

        /*
        |--------------------------------------------------------------------------
        | Team members participating in this competition
        |--------------------------------------------------------------------------
        */

        $competitionMembers = collect();

        if ($isTeamWorkspace) {

            $competitionMembers = $entry->members
                ->where('status', 'active')
                ->values();
        }

        return view('competitions.show', [
            'series' => $series,
            'entry' => $entry,
            'currentRound' => $currentRound,
            'competitionStats' => $competitionStats,
            'reportScope' => $reportScope,

            /*
            * Workspace context
            */
            'workspace' => $currentWorkspace,

            /*
            * Team management
            */
            'isTeamWorkspace' => $isTeamWorkspace,
            'canManageTeamCompetition' => $canManageTeamCompetition,
            'competitionMembers' => $competitionMembers,
        ]);
    }


    public function stints(Series $series)
    {
        $workspace = workspace();

        $entry = SeriesEntry::query()
            ->with([
                'series.rounds.track',
                'competitionCar',
                'workspace',
            ])
            ->where('series_id', $series->id)
            ->where(
                'workspace_id',
                $workspace->workspace()->id
            )
            ->where('status', 'active')
            ->firstOrFail();

        $activeRound = $entry->series->activeRound();

        if (!$activeRound || !$activeRound->track) {
            return redirect()
                ->route('competitions.show', $series)
                ->with(
                    'error',
                    'No hay combinación activa de circuito.'
                );
        }

        $competitionCar = $entry->competitionCar;

        if (!$competitionCar) {
            return redirect()
                ->route('competitions.show', $series)
                ->with(
                    'error',
                    'No hay coche asociado a la participación.'
                );
        }

        /*
        * El driver de una participación privada es el propietario
        * del workspace.
        */
        $driverId = $workspace
            ->workspace()
            ->owner_user_id;

        /*
        * El coche utilizado por iracing debe ser el iracing_car_id
        * del CompetitionCar.
        */
        $carId = $competitionCar->iracing_car_id;

        $trackId = $activeRound
            ->track
            ->iracing_track_id;

        /*
        * Aplicamos primero la política de acceso.
        * Después restringimos al contexto de esta Competition.
        */

        $stints = app(StintAccessService::class)
            ->query()
            ->with([
                'laps',
                'session',
                'track',
                'user',
            ])
            ->withCount('laps')
            ->where('user_id', $driverId)
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
     * Catálogo de competiciones disponibles
     * para registro como Driver.
     */
    /**
 * Catálogo de competiciones disponibles
 * para registro como Driver o Team.
 */
    /**
 * Show the competitions available for private Driver registration.
 *
 * A Driver cannot register in a series if:
 *
 * 1. The Driver is already registered in that series.
 * 2. The Driver's Team is already registered in that series.
 */
    public function create(Request $request)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Driver Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'driver')
            ->where('owner_user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Driver registrations
        |--------------------------------------------------------------------------
        |
        | Series where the current Driver already has an
        | active or pending participation.
        |
        */

        $driverSeriesIds = SeriesEntry::query()
            ->where('workspace_id', $workspace->id)
            ->whereIn('status', [
                'pending',
                'active',
            ])
            ->pluck('series_id');

        /*
        |--------------------------------------------------------------------------
        | Team registrations
        |--------------------------------------------------------------------------
        |
        | A Driver cannot register privately in a series
        | where their Team is already participating.
        |
        */

        $teamSeriesIds = collect();

        if ($user->team_id) {

            $teamSeriesIds = SeriesEntry::query()
                ->whereIn('status', [
                    'pending',
                    'active',
                ])
                ->whereHas(
                    'workspace',
                    function ($query) use ($user) {

                        $query
                            ->where('type', 'team')
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
                ->pluck('series_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Combined exclusions
        |--------------------------------------------------------------------------
        */

        $excludedSeriesIds = $driverSeriesIds
            ->merge($teamSeriesIds)
            ->unique();

        /*
        |--------------------------------------------------------------------------
        | Available competitions
        |--------------------------------------------------------------------------
        */

        $series = Series::query()
            ->with('iracingSeries')
            ->whereNotIn(
                'id',
                $excludedSeriesIds
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Driver registration view
        |--------------------------------------------------------------------------
        */

        return view(
            'competitions.create',
            [
                'series' => $series,
            ]
        );
    }

    /**
     * Show the registration form for a Driver.
     */
    public function register(Series $series)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Driver Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'driver')
            ->where(
                'owner_user_id',
                $user->id
            )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Existing Driver participation
        |--------------------------------------------------------------------------
        */

        $driverEntryExists = SeriesEntry::query()
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
            ->exists();

        if ($driverEntryExists) {

            return redirect()
                ->route('competitions.index')
                ->with(
                    'error',
                    'Ya estás registrado en esta competición.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Team participation
        |--------------------------------------------------------------------------
        |
        | A Driver cannot register privately in a competition
        | where their Team is already participating.
        |
        */

        if ($user->team_id) {

            $teamEntryExists = SeriesEntry::query()
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
                ->exists();

            if ($teamEntryExists) {

                return redirect()
                    ->route('competitions.index')
                    ->with(
                        'error',
                        'Tu equipo ya está registrado en esta competición.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Competition dates
        |--------------------------------------------------------------------------
        */

        $competitionStart = $series->rounds
            ->min('week_start');

        $competitionEnd = $series->rounds
            ->max('week_start');

        /*
        |--------------------------------------------------------------------------
        | Competition cars
        |--------------------------------------------------------------------------
        */

        $cars = $series->iracingSeries?->cars ?? collect();

        /*
        |--------------------------------------------------------------------------
        | Registration view
        |--------------------------------------------------------------------------
        */

        return view(
            'competitions.register',
            [
                'series' => $series,
                'competitionStart' => $competitionStart,
                'competitionEnd' => $competitionEnd,
            ]
        );
    }

    public function store(Request $request, Series $series)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Driver Workspace
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::query()
            ->where('type', 'driver')
            ->where(
                'owner_user_id',
                $user->id
            )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validate registration data
        |--------------------------------------------------------------------------
        |
        | Driver registrations are always compartmented.
        | The policy is not supplied by the client.
        |
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

            'commitment' => [
                'accepted',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Driver data policy
        |--------------------------------------------------------------------------
        |
        | Private Driver data is always compartmented.
        |
        */

        $data['data_policy'] = 'compartmented';

        /*
        |--------------------------------------------------------------------------
        | Existing Driver participation
        |--------------------------------------------------------------------------
        |
        | A Driver cannot have more than one active/pending
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
                    'competitions.register',
                    $series
                )
                ->withErrors([
                    'competition' =>
                        'You are already registered for this competition.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Team participation
        |--------------------------------------------------------------------------
        |
        | A Driver cannot register privately in a competition
        | where their Team is already participating.
        |
        | This check is repeated here because store() must
        | remain protected even if someone bypasses the
        | create/register views and posts directly.
        |
        */

        if ($user->team_id) {

            $teamEntryExists = SeriesEntry::query()
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
                ->exists();

            if ($teamEntryExists) {

                return redirect()
                    ->route(
                        'competitions.index'
                    )
                    ->withErrors([
                        'competition' =>
                            'Your team is already registered for this competition.',
                    ]);
            }
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

                    /*
                    |--------------------------------------------------------------------------
                    | Restore Driver membership
                    |--------------------------------------------------------------------------
                    */

                    SeriesEntryMember::query()
                        ->where(
                            'series_entry_id',
                            $existingEntry->id
                        )
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->update([
                            'status' =>
                                'active',

                            'joined_at' =>
                                now(),

                            'left_at' =>
                                null,
                        ]);
                }
            );

            return redirect()
                ->route(
                    'competitions.index'
                )
                ->with(
                    'success',
                    'You are now registered for this competition.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create new Driver participation
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $series,
                $workspace,
                $data,
                $user
            ) {

                $entry = SeriesEntry::create([
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

                /*
                |--------------------------------------------------------------------------
                | Driver becomes the entry member
                |--------------------------------------------------------------------------
                */

                SeriesEntryMember::create([
                    'series_entry_id' =>
                        $entry->id,

                    'user_id' =>
                        $user->id,

                    'status' =>
                        'active',

                    'role' =>
                        'driver',

                    'joined_at' =>
                        now(),
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
                'competitions.index'
            )
            ->with(
                'success',
                'You are now registered for this competition.'
            );
    }

    public function cancel(Series $series)
    {
        $workspace = Workspace::query()
            ->where('type', 'driver')
            ->where('owner_user_id', auth()->id())
            ->where('is_active', true)
            ->firstOrFail();

        $entry = SeriesEntry::query()
            ->where('workspace_id', $workspace->id)
            ->where('series_id', $series->id)
            ->whereIn('status', ['pending', 'active'])
            ->firstOrFail();

        $entry->update([
            'status' => 'withdrawn',
            'left_at' => now(),
        ]);

        return redirect()
            ->route('competitions.index')
            ->with(
                'success',
                'Your participation has been cancelled.'
            );
    }

    public function sessions(Series $series)
    {
        $workspace = workspace();

        $entry = SeriesEntry::query()
            ->with([
                'series',
                'competitionCar',
                'workspace',
            ])
            ->where('series_id', $series->id)
            ->where(
                'workspace_id',
                $workspace->workspace()->id
            )
            ->where('status', 'active')
            ->firstOrFail();

        $activeRound = $entry->series->activeRound();

        if (!$activeRound || !$activeRound->track) {
            return redirect()
                ->route('competitions.show', $series)
                ->with(
                    'error',
                    'No hay combinación activa de circuito.'
                );
        }

        $competitionCar = $entry->competitionCar;

        if (!$competitionCar) {
            return redirect()
                ->route('competitions.show', $series)
                ->with(
                    'error',
                    'No hay coche asociado a la participación.'
                );
        }

        $driverId = $workspace
            ->workspace()
            ->owner_user_id;

        $carId = $competitionCar->iracing_car_id;

        $trackId = $activeRound
            ->track
            ->iracing_track_id;

        /*
        * Stints autorizados pertenecientes exactamente
        * a esta combinación Competition.
        */
        $subsessionIds = app(StintAccessService::class)
            ->query()
            ->where('user_id', $driverId)
            ->where('car_id', $carId)
            ->where('track_id', $trackId)
            ->whereNotNull('iracing_subsession_id')
            ->pluck('iracing_subsession_id')
            ->unique()
            ->values();

        $sessions = IrSession::query()
            ->whereIn(
                'iracing_subsession_id',
                $subsessionIds
            )
            ->withCount('stints')
            ->orderBy('created_at','Desc')
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


}
