@extends('layouts.app')

@section('title', 'User settings')

@section('page-title', 'User settings')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="max-w-6xl mx-auto px-4 py-6">

        <div class="mb-6">
    
            <h1 class="text-2xl font-bold text-[var(--card-title)]">
                User Settings
            </h1>
    
            <p class="text-[var(--text-soft)]">
                Manage your personal preferences and iRacing account information.
            </p>
    
        </div>
    
        @if(session('success'))
    
            <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500 text-green-400">
                {{ session('success') }}
            </div>
    
        @endif
    
        <form method="POST" action="{{ route('profile.update') }}">
    
            @csrf
            @method('PUT')
    
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
                {{-- USER PROFILE --}}
                <div class="bg-[var(--card)] border border-[var(--b-card)] rounded-lg p-6">
    
                    <h2 class="text-lg font-semibold text-[var(--card-title)] mb-6">
                        User Profile
                    </h2>
    
                    <div class="space-y-4">
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                Name
                            </label>
    
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-[var(--bg)] px-3 py-2"
                            >
    
                        </div>
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                Email
                            </label>
    
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-[var(--bg)] px-3 py-2"
                            >
    
                        </div>
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                Timezone
                            </label>
    
                            <select
                                name="timezone"
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-[var(--bg)] px-3 py-2"
                            >
    
                                <option value="UTC" @selected($user->timezone === 'UTC')>
                                    UTC
                                </option>
    
                                <option value="Europe/Madrid" @selected($user->timezone === 'Europe/Madrid')>
                                    Spain
                                </option>
    
                                <option value="Europe/London" @selected($user->timezone === 'Europe/London')>
                                    United Kingdom
                                </option>
    
                                <option value="America/New_York" @selected($user->timezone === 'America/New_York')>
                                    USA East
                                </option>
    
                                <option value="America/Chicago" @selected($user->timezone === 'America/Chicago')>
                                    USA Central
                                </option>
    
                                <option value="America/Los_Angeles" @selected($user->timezone === 'America/Los_Angeles')>
                                    USA West
                                </option>
    
                                <option value="America/Mexico_City" @selected($user->timezone === 'America/Mexico_City')>
                                    Mexico
                                </option>
    
                                <option value="America/Argentina/Buenos_Aires" @selected($user->timezone === 'America/Argentina/Buenos_Aires')>
                                    Argentina
                                </option>
    
                                <option value="Australia/Sydney" @selected($user->timezone === 'Australia/Sydney')>
                                    Australia
                                </option>
    
                            </select>
    
                        </div>
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                Helmet Image
                            </label>
    
                            <input
                                type="text"
                                name="iracing_helmet_path"
                                value="{{ old('iracing_helmet_path', $user->iracing_helmet_path) }}"
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-[var(--bg)] px-3 py-2"
                            >
    
                        </div>
    
                    </div>
    
                </div>
    
                {{-- IRACING ACCOUNT --}}
                <div class="bg-[var(--card)] border border-[var(--b-card)] rounded-lg p-6">
    
                    <h2 class="text-lg font-semibold text-[var(--card-title)] mb-6">
                        iRacing Account
                    </h2>
    
                    <div class="space-y-4">
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                iRacing Name
                            </label>
    
                            <input
                                type="text"
                                value="{{ $user->iracing_name }}"
                                readonly
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-black/20 px-3 py-2 opacity-70"
                            >
    
                        </div>
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                iRacing User ID
                            </label>
    
                            <input
                                type="text"
                                value="{{ $user->iracing_user_id }}"
                                readonly
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-black/20 px-3 py-2 opacity-70"
                            >
    
                        </div>
    
                        <div>
    
                            <label class="block text-sm mb-2">
                                API Token
                            </label>
    
                            <input
                                type="text"
                                value="{{ $user->api_token }}"
                                readonly
                                class="w-full rounded-lg border border-[var(--b-card-light)] bg-black/20 px-3 py-2 opacity-70 font-mono text-xs"
                            >
    
                        </div>
    
                    </div>
    
                </div>
    
            </div>
    
            <div class="mt-6 flex justify-end">
    
                <button
                    type="submit"
                    class="px-6 py-2 rounded-lg bg-[var(--value-info)] text-black font-semibold"
                >
                    Save Changes
                </button>
    
            </div>
    
        </form>
    
    </div>
    
</div>

@endsection
