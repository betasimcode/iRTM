<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Car;
use App\Models\Series;
use App\Models\Stint;
use Illuminate\Support\Facades\DB;
use App\Models\TeamCar; // 👈 IMPORTANTE
use App\Models\User;

class TeamWorkspaceController extends Controller
{



    public function dashboard()
    {
        $user = auth()->user();

        $workspace = app(
            \App\Services\Workspace\WorkspaceResolver::class
        )->team($user);

        if (! $workspace) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Activate Team Workspace
        |--------------------------------------------------------------------------
        */

        session()->put(
            'workspace_id',
            $workspace->workspace()->id
        );

        $team = $workspace->team();

        if (! $team) {
            abort(403);
        }

        $members = $team->members()
            ->withCount([
                'teamCars as cars_count'
            ])
            ->get();

        $cars = Car::orderBy('name')->get();

        $series = Series::where(
            'team_id',
            $team->id
        )->get();

        $teamCars = TeamCar::where(
            'team_id',
            $team->id
        )->get();

        return view(
            'team.dashboard',
            compact(
                'team',
                'series',
                'members',
                'cars',
                'teamCars'
            )
        );
    }

    public function members()
    {
        $team = auth()->user()->team;

        $members = $team->members()
            ->withCount([
                'teamCars as cars_count',

                'teamCars as series_count' => function ($q) {
                    $q->select(DB::raw('count(distinct team_car_user.series_id)'));
                }
            ])
            ->get();

        return view('team.tabs.members', compact('team', 'members'));
    }

    public function showMember(User $user)
    {
        $team = auth()->user()->team;

        // 🔒 seguridad
        if ($user->team_id !== $team->id) {
            abort(403);
        }

        return view('team.members.show', compact('team','user'));
    }

    public function updateHelmet(Request $request, User $user)
    {
        $auth = auth()->user();

        if (
            $auth->role !== 'admin' &&
            $auth->driver_role !== 'team_owner'
        ) {
            abort(403);
        }

        $data = $request->validate([
            'iracing_helmet' => 'required|image|max:2048',
        ]);

        $path = $request->file('iracing_helmet')->store('helmets','public');

        $user->update([
            'iracing_helmet_path' => $path
        ]);

        return back()->with('success','Helmet updated');
    }

    public function stints()
    {
        $team = auth()->user()->team;

        $stints = Stint::whereHas('user', function($q) use ($team){
            $q->where('team_id',$team->id);
        })
        ->latest()
        ->paginate(20);

        return view('team.stints',compact('team','stints'));
    }

    public function series()
    {
        $team = auth()->user()->team;

        return view('team.series',compact('team'));
    }

    public function calendar()
    {
        $team = auth()->user()->team;

        return view('team.calendar',compact('team'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'driver_role' => 'required|in:driver,team_director'
        ]);

        $user->update([
            'driver_role' => $request->driver_role
        ]);

        return response()->json(['ok' => true]);
    }

    public function removeMember(User $user)
    {
        $auth = auth()->user();

        if(!in_array($auth->driver_role,['team_owner','team_director'])){
            abort(403);
        }

        if($user->driver_role === 'team_owner'){
            abort(403);
        }

        if($user->team_id !== $auth->team_id){
            abort(403);
        }

        $user->update([
            'team_id' => null,
            'driver_role' => 'driver'
        ]);

        return back()->with('success','Miembro eliminado');
    }










}
