<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit()
{
    return view(
        'profile.edit',
        [
            'user' => auth()->user()
        ]
    );
}

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
{
    $request->validate([

        'name' => 'required|string|max:255',

        'email' => 'required|email',

        'timezone' => 'required|string',

        'iracing_helmet_path' => 'nullable|string',

    ]);

    auth()->user()->update([

        'name' => $request->name,

        'email' => $request->email,

        'timezone' => $request->timezone,

        'iracing_helmet_path' => $request->iracing_helmet_path,

    ]);

    return back()->with(
        'success',
        'Profile updated'
    );
}

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
