@extends('layouts.app')

{{-- @section('page_title', $series->name)
@section('title', $series->name) --}}

@section('title', $series->iracingSeries->name . ' ' . $series->season_year )
@section('page-title',  $series->iracingSeries->name . ' ' . $series->season_year )

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- HEADER --}}
{{-- COMPETITION CONTEXT --}}
<section
    class="rounded-2xl
           border border-[var(--border)]
           bg-[var(--card)]
           overflow-hidden"
>

    <div class="p-7">

        <div
            class="grid grid-cols-1
                   md:grid-cols-4
                   gap-6
                   items-stretch"
        >

            {{-- SEASON / WEEK --}}
            <div
                class="flex flex-col
                       justify-center
                       md:border-r
                       md:border-[var(--border)]
                       md:pr-6"
            >

            <div class="mx-auto">
           @if($series->iracingSeries->logo_path)

            <img
                src="{{ asset(
                    'storage/' . $series->iracingSeries->logo_path
                ) }}"
                alt=""
                class="h-auto w-32
                       object-contain"
            >

            @else
                        <span
                            class="text-xs
                                   text-[var(--text-muted)]"
                        >
                            {{ $series->name }}
                        </span>

            @endif
            </div>


                <p
                    class="text-4xl font-bold
                           font-microsport
                           text-center
                           text-[var(--card-title)]"
                >

                    SEASON {{ $series->season_number }}
                </p>

                @if($series->rounds->isNotEmpty())

                    @php
                        $firstRound = $series->rounds->first();
                        $lastRound = $series->rounds->last();

                        $startDate = $firstRound?->week_start;

                        $endDate = $lastRound?->week_start
                            ? \Carbon\Carbon::parse(
                                $lastRound->week_start
                            )->addDays(6)
                            : null;
                    @endphp

                    @if($startDate && $endDate)

                        <p
                            class="mt-2 text-sm
                            text-center
                                   text-[var(--text-muted)]"
                        >
                            {{ \Carbon\Carbon::parse(
                                $startDate
                            )->format('d M Y') }}

                            →

                            {{ $endDate->format('d M Y') }}
                        </p>

                    @endif

                @endif


                @if($currentRound)

                    <div class="mt-4">


                        <p class="mt-1 text-xl
                        text-center
                                   font-light
                                   uppercase
                                   font-microsport
                                   text-[var(--card-title)]">
                            Week {{ $currentRound->week }}
                        </p>

                        <p
                            class="mt-1 text-sm
                            text-center
                                   text-[var(--text-muted)]"
                        >
                            {{ \Carbon\Carbon::parse(
                                $currentRound->week_start
                            )->format('d M Y') }}
                        </p>

                    </div>

                @endif

            </div>


            {{-- TRACK --}}
            <div
                class="flex flex-col
                       items-center
                       justify-center
                       text-center
                       md:border-r
                       md:border-[var(--border)]
                       md:pr-6"
            >

                <div
                    class="h-28 w-full
                           flex items-center
                           justify-center"
                >

                    @if($currentRound?->track?->logo)

                        <img
                            src="{{ asset('storage/' . themedlogo($currentRound->track)
                            ) }}"
                            alt="{{ $currentRound->track->display_name }}"
                            class="max-h-28 max-w-44
                                   object-contain"
                        >

                    @else

                        <span
                            class="text-xs
                                   text-[var(--text-muted)]"
                        >
                            TRACK
                        </span>

                    @endif

                </div>

                <p
                    class="mt-3 text-lg
                           font-semibold
                           text-[var(--value-data)]"
                >
                    {{ $currentRound?->track?->display_name
                        ?? 'Track unavailable' }}
                </p>

                @if($currentRound?->track?->variant)

                    <p
                        class="mt-1 text-sm
                               text-[var(--value-data)]"
                    >
                        {{ $currentRound->track->variant }}
                    </p>

                @endif

            </div>


            {{-- CAR --}}
            <div
                class="flex flex-col
                       items-center
                       justify-center
                       text-center
                       md:border-r
                       md:border-[var(--border)]
                       md:pr-6"
            >

                <div
                    class="h-28 w-full
                           flex items-center
                           justify-center"
                >

                    @if($entry->competitionCar?->image_path)

                        <img
                            src="{{ asset('storage/' .
                                $entry->competitionCar->image_path
                            ) }}"
                            alt="{{ $entry->competitionCar->name }}"
                            class="max-h-28 max-w-48
                                   object-contain"
                        >

                    @else

                        <span
                            class="text-xs
                                   text-[var(--text-muted)]"
                        >
                            CAR
                        </span>

                    @endif

                </div>

                <p
                    class="mt-3 text-lg
                           font-semibold
                           text-[var(--value-data)]"
                >
                    {{ $entry->competitionCar?->name
                        ?? 'Car not specified' }}
                </p>

                <p
                    class="mt-1 text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Competition Car
                </p>

            </div>


            {{-- DRIVER --}}
            <div
                class="flex flex-col
                       items-center
                       justify-center
                       text-center"
            >

                <div
                    class="h-28 w-full
                           flex items-center
                           justify-center"
                >

                    @if(auth()->user()->iracing_helmet_path)

                        <img
                            src="{{ asset(
                                'storage/' .
                                auth()->user()->iracing_helmet_path
                            ) }}"
                            alt="{{ auth()->user()->name }}"
                            class="max-h-28 max-w-32
                                   object-contain"
                        >

                    @else

                        <span
                            class="text-xs
                                   text-[var(--text-muted)]"
                        >
                            DRIVER
                        </span>

                    @endif

                </div>

                <p
                    class="mt-3 text-lg
                           font-semibold
                           text-[var(--value-data)]"
                >
                    {{ auth()->user()->name }}
                </p>

                <p
                    class="mt-1 text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Private Driver
                </p>

            </div>

        </div>

    </div>

    {{-- QUICK REPORTS --}}
    <div
        class="border-t border-[var(--border)]
               px-7 py-6"
    >

        <div
            class="grid grid-cols-1
                   sm:grid-cols-2
                   xl:grid-cols-4
                   gap-4"
        >

            {{-- STINTS --}}
            <div
                class="rounded-xl bg-[var(--bg)] border border-[var(--border)] p-4">

                <p
                    class="text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Tandas totales
                </p>

                <p
                    class="mt-3 text-2xl
                           font-mono-timing
                           font-bold
                           text-[var(--text)]"
                >
                    {{ $competitionStats['total_stints'] }}
                </p>

            </div>


            {{-- BEST LAP --}}
            <div
                class="rounded-xl
                       bg-[var(--bg)]
                       border border-[var(--border)]
                       p-4"
            >

                <p
                    class="text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Mejor vuelta
                </p>

                <p
                    class="mt-3 text-2xl
                           font-bold
                           font-mono-timing
                           text-[var(--lap-best)]"
                >
                    {{ lapTime($competitionStats['best_lap']) }}
                </p>

            </div>


            {{-- TARGET --}}
            <div
                class="rounded-xl
                       bg-[var(--bg)]
                       border border-[var(--border)]
                       p-4"
            >

                <p
                    class="text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Objetivo
                </p>

                <p
                    class="mt-3 text-2xl
                           font-bold
                           font-mono-timing
                           text-[var(--lap-avg)]"
                >
                    {{ lapTime($competitionStats['representative_pace']) }}
                </p>

            </div>


            {{-- FUEL --}}
            <div
                class="rounded-xl
                       bg-[var(--bg)]
                       border border-[var(--border)]
                       p-4"
            >

                <p
                    class="text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Consumo medio
                </p>

                <p
                    class="mt-3 text-2xl
                           font-bold
                           text-[var(--text)]"
                >
                    {{ $competitionStats['avg_fuel']
                        ? number_format($competitionStats['avg_fuel'], 2) . ' L'
                        : '—' }}
                </p>

            </div>

        </div>


        <div
            class="mt-4
                   rounded-xl
                   bg-[var(--bg)]
                   border border-[var(--border)]
                   p-5
                   flex flex-col
                   md:flex-row
                   md:items-center
                   md:justify-between
                   gap-4"
        >

            <div>

                <p
                    class="text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]"
                >
                    Mejor tanda
                </p>

                <p
                    class="mt-2 text-xl
                           font-semibold
                           text-[var(--text)]"
                >
                    {{ $competitionStats['long_run_best'] ?? '—' }} Vueltas
                </p>

            </div>


            {{-- REPORT SCOPE --}}
            <div>

                <label
                    for="report-scope"
                    class="sr-only"
                >
                    Report scope
                </label>

                <form
                    method="GET"
                    action="{{ route('competitions.show', $series) }}"
                >
                    <label
                        for="report-scope"
                        class="sr-only"
                    >
                        Report scope
                    </label>

                    <select
                        id="report-scope"
                        name="report_scope"
                        onchange="this.form.submit()"
                        class="rounded-lg
                            w-40
                            border border-[var(--border)]
                            bg-[var(--card)]
                            px-4 py-2
                            text-sm
                            text-[var(--text)]
                            focus:outline-none"
                    >

                        <option
                            value="week"
                            @selected($reportScope === 'week')
                        >
                            Semana
                        </option>

                        <option
                            value="season"
                            @selected($reportScope === 'season')
                        >
                            Season
                        </option>

                        <option
                            value="historic"
                            @selected($reportScope === 'historic')
                        >
                            Histórico
                        </option>

                    </select>
                </form>

            </div>

        </div>

    </div>

</section>


    <div class="mt-5 flex flex-wrap gap-3">

    <a
        href="{{ route('competitions.sessions', $series) }}"
        class="w-32
               px-4 py-2
               uppercase
               font-microsport
               text-center
               rounded-lg
               border border-[var(--border)]
               bg-[var(--btn-app)]
               text-sm
               text-[var(--text-title)]
               hover:bg-[var(--btn-app-hov)]
               transition"
    >
        Sessions
    </a>

    <a
        href="{{ route('competitions.stints', $series) }}"
        class="w-32
               text-center
               px-4 py-2
               uppercase
               font-microsport
               rounded-lg
               border border-[var(--border)]
               bg-[var(--btn-app)]
               text-sm
               text-[var(--text-title)]
               hover:bg-[var(--btn-app-hov)]
               transition"
    >
        Stints
    </a>

    <a
        href="{{ route('competitions.strategy', $series) }}"
        class="w-32
               px-4 py-2
               uppercase
               font-microsport
               text-center
               rounded-lg
               border border-[var(--border)]
               bg-[var(--btn-app)]
               text-sm
               text-[var(--text-title)]
               hover:bg-[var(--btn-app-hov)]
               transition"
    >
        Strategy
    </a>

</div>


@if($isTeamWorkspace)

    <section class="mt-8">

        <div class="flex items-center justify-between mb-4">

            <div>
                <h2 class="text-lg font-semibold text-[var(--text)]">
                    Team
                </h2>

                <p class="text-sm text-[var(--text-muted)]">
                    Drivers participating in this competition
                </p>
            </div>

            @if($canManageTeamCompetition)

                <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white font-medium transition"
                >
                    + Add Driver
                </button>

            @endif

        </div>


        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] overflow-hidden">

            @forelse($competitionMembers as $member)

                <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--border)] last:border-b-0">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-full overflow-hidden bg-[var(--card-hover)]">

                            @if($member->user?->iracing_helmet_path)

                                <img
                                    src="{{ asset($member->user->iracing_helmet_path) }}"
                                    alt="{{ $member->user->name }}"
                                    class="w-full h-full object-cover"
                                >

                            @endif

                        </div>

                        <div>

                            <div class="font-medium text-[var(--text)]">
                                {{ $member->user?->name }}
                            </div>

                            <div class="text-xs text-[var(--text-muted)]">
                                {{ ucfirst($member->role ?? 'driver') }}
                            </div>

                        </div>

                    </div>


                    @if($canManageTeamCompetition)

                        <div class="flex items-center gap-2">

                            <button
                                type="button"
                                class="text-sm px-3 py-1.5 rounded-lg border border-[var(--border)] hover:bg-[var(--card-hover)] transition"
                            >
                                Manage
                            </button>

                        </div>

                    @endif

                </div>

            @empty

                <div class="px-5 py-8 text-center">

                    <div class="text-sm text-[var(--text-muted)]">
                        No drivers have been assigned to this competition yet.
                    </div>

                    @if($canManageTeamCompetition)

                        <div class="mt-4">

                            <button
                                type="button"
                                class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white font-medium transition"
                            >
                                + Add Driver
                            </button>

                        </div>

                    @endif

                </div>

            @endforelse

        </div>

    </section>

@endif


    {{-- CALENDAR --}}
    <section class="mt-8">

        <div class="mb-4">

            <h2
                class="text-xl font-semibold
                       text-[var(--text)]"
            >
                Competition Calendar
            </h2>

            <p
                class="mt-1 text-sm
                       text-[var(--text-muted)]"
            >
                Registered rounds for this competition.
            </p>

        </div>


        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--card)]
                   overflow-hidden"
        >

            @forelse($series->rounds as $round)

                <div
                    class="px-5 py-4
                           border-b
                           border-[var(--border)]
                           last:border-b-0"
                >

                    <div
                        class="flex items-center
                               gap-4"
                    >

                        <div
                            class="w-16 shrink-0
                                   text-sm font-semibold
                                   text-[var(--card-title)]"
                        >
                            Week {{ $round->week }}
                        </div>


                        <div
                            class="w-24 shrink-0
                                   text-sm
                                   text-[var(--text-muted)]"
                        >
                            {{ \Carbon\Carbon::parse(
                                $round->week_start
                            )->format('d M Y') }}
                        </div>


                        <div
                            class="h-28 w-auto shrink-0
                                   flex items-center
                                   justify-center
                                   overflow-hidden"
                        >

                            @if($round->track?->logo)

                                <div x-data="{ open: false }">
                                <img @click="open = true" src="{{ asset('storage/' . themedlogo($round->track )) }}"
                                    alt="{{ $round->track->name }}"
                                    class="w-24 py-1 h-auto object-contain">

                                    <!-- Fondo oscuro y contenedor del Modal -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
        style="display: none;">

        <!-- Tarjeta del Modal -->
        <div
            @click.away="open = false"
            @keydown.escape.window="open = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-[1000px] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">

            <!-- Encabezado -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-red-900 dark:text-white">
                    @if( $round->track )
                {{ $round->track->display_name }}
            @endif
                </h3>
                <button
                    @click="open = false"
                    class="text-red-900 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="text-[var(--text)]">

            @if( $round->track->map )
             {!! $round->track->map !!}
            @endif

            </div>

            <!-- Pie / Acciones -->
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                <button
                    @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Cancelar
                </button>
                <button
                    @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

                                </div>
                            @else

                                <span
                                    class="text-[9px]
                                           px-4
                                           py-2
                                           rounded-xl
                                           bg-[var(--bg)]
                                           text-[var(--text-muted)]"
                                >
                                    TRACK
                                </span>

                            @endif

                        </div>


                        <div class="flex-1 min-w-0">

                            <p
                                class="font-medium
                                       text-[var(--card-title)]"
                            >
                                {{ $round->track?->display_name
                                    ?? 'Track unavailable' }}

                                @if($round->track?->variant)

                                    <span
                                        class="text-[var(--text-muted)]"
                                    >
                                        {{ $round->track->variant }}
                                    </span>

                                @endif

                            </p>

                        </div>


                        @if($round->race_length)

                            <div
                                class="shrink-0
                                       text-sm
                                       text-[var(--text-muted)]"
                            >
                                {{ $round->race_length }}
                            </div>

                        @endif

                    </div>

                </div>

            @empty

                <div
                    class="p-8 text-center
                           text-sm
                           text-[var(--text-muted)]"
                >
                    No rounds are currently registered
                    for this competition.
                </div>

            @endforelse

        </div>

    </section>



    {{-- CANCEL --}}
    <section class="mt-8 pb-10">

        <form
            method="POST"
            action="{{ route(
                'competitions.cancel',
                $series
            ) }}"
            onsubmit="return confirm(
                'Are you sure you want to cancel your participation in this competition?'
            )"
        >

            @csrf

            <button
                type="submit"
                class="px-4 py-2
                       rounded-lg
                       border border-red-400/40
                       text-red-400
                       hover:bg-red-400/10
                       transition"
            >
                Cancel Participation
            </button>

        </form>

    </section>

</div>

@endsection
