<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index()
    {
        if(auth()->user()->role === 'admin') {
            $teams = Team::latest()->paginate(20);
        } else {
            $teams = Team::where('id', auth()->user()->team_id)->paginate(1);
        }

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        $user = auth()->user();

        if($user->team_id && $user->role !== 'admin'){
            abort(403,'Ya perteneces a un equipo.');
        }

        return view('teams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'logo_light' => 'nullable|image|max:2048',
            'logo_dark' => 'nullable|image|max:2048',
            'banner_dark' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('teams/logos','public');
        }

         if ($request->hasFile('logo_light')) {
            $data['logo_light'] = $request->file('logo_light')->store('teams/logos','public');
        }

         if ($request->hasFile('logo_dark')) {
            $data['logo_dark'] = $request->file('logo_dark')->store('teams/logos','public');
        }


        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('teams/banners','public');
        }

        if ($request->hasFile('banner_dark')) {
            $data['banner_dark'] = $request->file('banner_dark')->store('teams/banners','public');
        }

        $data['owner_id'] = auth()->id();

        DB::transaction(function () use ($data) {

            $team = Team::create($data);

            auth()->user()->update([
                'team_id' => $team->id,
                'driver_role' => 'team_owner'
            ]);

        });

        return redirect()->route('teams.index')
            ->with('success','Equipo creado correctamente');
    }


    public function edit(Team $team)
    {
        if(auth()->user()->role !== 'admin'){
            abort(403);
        }

        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'logo_light' => 'nullable|image|max:2048',
            'logo_dark' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'banner_dark' => 'nullable|image|max:2048',
        ]);

        /*
        * Logo principal
        */
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request
                ->file('logo')
                ->store('teams/logos', 'public');
        }

        /*
        * Logo para tema claro
        */
        if ($request->hasFile('logo_light')) {
            $data['logo_light'] = $request
                ->file('logo_light')
                ->store('teams/logos', 'public');
        }

        /*
        * Logo para tema oscuro
        */
        if ($request->hasFile('logo_dark')) {
            $data['logo_dark'] = $request
                ->file('logo_dark')
                ->store('teams/logos', 'public');
        }

        /*
        * Banner para tema claro
        */
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request
                ->file('banner')
                ->store('teams/banners', 'public');
        }

        /*
        * Banner para tema oscuro
        */
        if ($request->hasFile('banner_dark')) {
            $data['banner_dark'] = $request
                ->file('banner_dark')
                ->store('teams/banners', 'public');
        }

        /*
        * Nunca dejamos que los UploadedFile lleguen
        * directamente al modelo.
        */
        unset(
            $data['logo'],
            $data['banner']
        );

        $team->update($data);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Equipo actualizado');
    }


    public function destroy(Team $team)
    {
        if(auth()->user()->role !== 'admin'){
            abort(403);
        }

        $team->delete();

        return back()->with('success','Equipo eliminado');
    }

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function members(Team $team)
    {
        if(auth()->user()->role !== 'admin'
            && auth()->user()->team_id !== $team->id)
        {
            abort(403);
        }

        $members = $team->members;

        return view('teams.members',compact('team','members'));
    }




}
