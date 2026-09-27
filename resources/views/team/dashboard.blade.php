@extends('layouts.app')

@section('title', 'Team Dashboard')

@section('page-title')
   Team Administration
@endsection

@section('content')

<div class="max-w py-5 space-y-5">

    {{-- TEAM HEADER --}}
    <div class="bg-[var(--card)] border border-[var(--border)] shadow-lg rounded p-6 flex items-center justify-between">

        <div>
            <img src="{{ asset('storage/' . themedBanner($team)) }}" class="h-24 w-auto">
        </div>



        <div class="grid grid-cols-1 text-center text-sm uppercase">
            <img src="{{ asset('storage/'.auth()->user()->iracing_helmet_path) }}"
                        class="w-20 m-auto align-middle">
            <p class="text-[var(--text-title)] text-lg font-semibold">{{ auth()->user()->iracing_name }}</p>
            @if(auth()->user()->driver_role === 'team_owner')
            <p class="text-[var(--info)]">Team Owner</p>
            @else
            Driver
            @endif
        </div>

    </div>


    <div
    x-data="{ tab: localStorage.getItem('teamTab') || 'about' }"
    x-init="$watch('tab', value => localStorage.setItem('teamTab', value))">

        {{-- MENU --}}
        <div class="flex gap-4 mb-6">

            <x-ui.button
                variant="menu"
                @click="tab = 'about'"
                x-bind:class="tab === 'about' ? 'bg-[var(--hover)] text-[var(--text-h)]' : ''"
            >
                ABOUT
            </x-ui.button>

            <x-ui.button href="{{ route('team.competitions.index') }}"
                variant="menu"
                x-bind:class="'bg-[var(--hover)] text-[var(--text-h)]' : ''"
            >
            COMPETITIONS
            </x-ui.button>

            <x-ui.button
                variant="menu"
                @click="tab = 'members'"
                x-bind:class="tab === 'members' ? 'bg-[var(--hover)] text-[var(--text-h)]' : ''"
            >
            MEMBERS
            </x-ui.button>

            <x-ui.button
                variant="menu"
                @click="tab = 'cars'"
                x-bind:class="tab === 'cars' ? 'bg-[var(--hover)] text-[var(--text-h)]' : ''"
            >
            CARS
            </x-ui.button>

            <x-ui.button
                variant="menu"
                @click="tab = 'calendar'"
                x-bind:class="tab === 'calendar' ? 'bg-[var(--hover)] text-[var(--text-h)]' : ''"
            >
            CALENDAR
            </x-ui.button>

        </div>

        {{-- CONTENIDO --}}
        <div class="p-0">

            <div x-show="tab === 'about'">
                @include('team.tabs.about')
            </div>

            <div x-show="tab === 'competitions'">
                @include('team.tabs.competitions')
            </div>

            <div x-show="tab === 'members'">
                @include('team.tabs.members')
            </div>

            <div x-show="tab === 'cars'">
                @include('team.tabs.cars')
            </div>

            <div x-show="tab === 'calendar'">
                @include('team.tabs.calendar')
            </div>

        </div>

    </div>




    {{-- SERIES --}}


</div>

@endsection


{{--

<div>

        <h3 class="text-white text-lg font-semibold mb-4">
            Series del equipo
        </h3>

        @if($team->series->isEmpty())

            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 text-gray-400">
                No estás inscrito en ninguna serie.
            </div>

        @else

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

            @foreach($team->series as $serie)

                @php
                    $teamCar = $serie->teamCar($team->id);
                    $canEdit = auth()->user()->isTeamDirector() && $serie->status !== 'active';
                @endphp

                <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 space-y-4">


                    <div>
                        <h4 class="text-white font-semibold">
                            {{ $serie->name }}
                        </h4>

                        <p class="text-gray-400 text-sm">
                            {{ $serie->season_year }} - S{{ $serie->season_number }}
                        </p>
                    </div>

                    <div class="text-sm">


                            <p class="text-white">
                                @forelse($teamCars as $teamCar)

                                <p class="text-white">
                                    🚗 {{ $teamCar->car->name }}

                                    @if($teamCar->number)
                                        #{{ $teamCar->number }}
                                    @endif
                                </p>

                            @empty

                                <p class="text-gray-400">
                                    No hay coches registrados
                                </p>

                            @endforelse
                            </p>

                            <p class="text-yellow-400">
                                ⚠ No asignado
                            </p>


                    </div>



                    <div class="pt-2">

                        @if($canEdit)

                            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 rounded-lg">
                                Configurar coche
                            </button>

                        @else

                            <div class="text-gray-500 text-xs">
                                🔒 Configuración bloqueada
                            </div>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>

        @endif

    </div>

--}}
