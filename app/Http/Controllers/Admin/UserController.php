<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('team')->paginate(15);
        $team = team::where('id')->team_id;
        return view('admin.users.index', compact('users','team'));
    }

    public function create()
    {
        $teams = Team::all();

        return view('admin.users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role'     => 'required|in:admin,user',
            'team_id'  => 'nullable|exists:teams,id',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'team_id'  => $request->team_id,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success','User created successfully.');
    }

    public function edit(User $user)
    {
        $teams = Team::all();

        return view('admin.users.edit', compact('user','teams'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,'.$user->id,
            'role'    => 'required|in:admin,user',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'role'    => $request->role,
            'team_id' => $request->team_id,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success','User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success','User deleted.');
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|string'
        ]);

        $user = auth()->user();
        $user->theme = $request->theme;
        $user->save();

        return response()->json(['ok' => true]);
    }








}
