@extends('teamcenter.layouts.a')

@section('title', $series->iracingSeries->name . ' ' . $series->season_year)
@section('page-title', $series->iracingSeries->name . ' ' . $series->season_year)

@section('teamcenter-main')

<div class="max-w-7xl mx-auto px-4 py-8">

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
                       md:grid-cols-3
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

                    <p
                        class="text-4xl font-bold
                               font-microsport
                               text-[var(--text-title)]"
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

                    <div class="mt-4">

                        <p
                            class="mt-1 text-xl
                                   font-light
                                   uppercase
                                   font-microsport
                                   text-[var(--card-title)]"
                        >
                            Team Competition
                        </p>

                        <p
                            class="mt-1 text-sm
                                   text-[var(--text-muted)]"
                        >
                            {{ ucfirst($entry->status) }}
                        </p>

                    </div>

                </div>


                {{-- TRACK --}}
                <div class="flex flex-col items-center justify-center text-center md:border-r md:border-[var(--border)] md:pr-6">

                    <div>
               @if($currentRound?->track?->logo)

                                <div x-data="{ open: false }">
                                <img @click="open = true" src="{{ asset('storage/' . themedlogo($currentRound->track )) }}"
                                    title="{{ $currentRound->track->variant }}"
                                    alt="{{ $currentRound->track->name }}"
                                    class="w-36 py-1 h-auto object-contain">

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
            class="w-full max-w-[1000px] bg-[var(--card)] rounded-xl shadow-2xl border border-[var(--border)] overflow-hidden">

            <!-- Encabezado -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--b-card)]">
                <h3 class="text-lg font-semibold text-[var(--text-card-title)]">
                    @if( $currentRound->track->variant )
                {{ $currentRound->track->display_name . ' / ' . $currentRound->track->variant }}
                @else
                {{ $currentRound->track->display_name }}
                @endif
                </h3>
                <button
                    @click="open = false"
                    class="text-[var(--text-card-title)] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <!-- Cuerpo -->
            <div class="text-[var(--text)]">

            @if( $currentRound->track )
            <div class="text-[var(--accent)] capitalize">
            @include('circuits.svg.' . $currentRound->track->id)
            {{-- <div> {!! $stint->track->map_svg !!}      </div> --}}
            </div>
            @else
            <div class="p-8 m-auto text-center">No track image</div>
            @endif

            </div>

            <!-- Pie / Acciones -->
            {{-- <div>
                <h4 class="text-xs mb-0  px-3 text-[var(--text-soft)]">Image by motorsportmagazine.com</h4>
            </div> --}}
            <div class="flex justify-end gap-3 px-6 py-4 bg-[var(--card)]">
                <button
                    @click="open = false"
                    class="px-4 w-28 py-2 text-sm font-medium text-[var(--text)] bg-[var(--btn-cancel)] hover:bg-[var(--btn-cancel-h)] rounded-lg transition">
                    Back
                </button>
                {{-- <button
                    @click="open = false"
                    class="px-4 w-28 py-2 text-sm font-medium text-[var(--text)] bg-[var(--btn-confirm)] hover:bg-[var(--btn-confirm-h)] rounded-lg transition">
                    Confirmar
                </button> --}}
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

                    <p
                        class="mt-3 text-lg
                            font-semibold
                            text-[var(--value-data)]"
                    >
                        {{ $round?->track?->variant ?? 'Próxima carrera' }}
                    </p>

                    <p
                        class="mt-1 text-sm
                            text-[var(--text-muted)]"
                    >
                        {{ $currentRound ? 'Current round' : 'Next round' }}
                    </p>

                    </div>


                {{-- CAR --}}
                <div class="flex flex-col items-center justify-center text-center md:border-r md:border-[var(--border)] md:pr-6">

                    <div class="h-28 w-full flex items-center justify-center">

                        @if($teamCar->image_path)

                            <img
                                src="{{ asset('storage/' . $teamCar->image_path ) }}"
                                alt="{{ $teamCar->display_name }}"
                                class="max-w-56 object-contain">

                        @else

                            <span
                                class="text-xs text-[var(--text-muted)]">
                                CAR
                            </span>

                        @endif

                    </div>

                    <p
                        class="mt-3 text-lg
                            font-semibold
                            text-[var(--value-data)]"
                    >
                        {{ $teamCar?->display_name
                            ?? $entry->competitionCar?->name
                            ?? 'Competition Car' }}
                    </p>

                    <p
                        class="mt-1 text-xs uppercase
                            tracking-wider
                            text-[var(--text-muted)]"
                    >
                        Competition Car
                    </p>

                </div>

            </div>

        </div>


        {{-- QUICK REPORTS PLACEHOLDER --}}
        <div
            class="border-t border-[var(--border)]
                   px-7 py-6"
        >
            <div class="grid grid-cols-2 md:grid-cols-4 gap-10">

        <div class="rounded-xl bg-[var(--bg)] border border-[var(--border)] p-4">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.totalstint') }}</p>
            <p class="text-2xl font-bold text-[var(--value-data)] mt-2 pl-10">
                {{ $competitionStats['total_stints'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl bg-[var(--bg)] border border-[var(--border)] p-4">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.bestlap') }}</p>
            <p class="text-2xl font-bold text-[var(--lap-best)] mt-2 pl-10">
                {{ lapTime($competitionStats['best_lap']) }}
            </p>
        </div>

        <div class="rounded-xl bg-[var(--bg)] border border-[var(--border)] p-4">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.timingtarget') }}</p>
            <p class="text-2xl font-bold text-[var(--value-info)] mt-2 pl-10">
                {{ lapTime($competitionStats['representative_pace']) }}
            </p>
        </div>

        <div class="rounded-xl bg-[var(--bg)] border border-[var(--border)] p-4">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.avg_cons') }}</p>
            <p class="text-2xl font-bold text-[var(--value-data)] mt-2 pl-10">
                {{ $competitionStats['avg_fuel'] !== null ? number_format($competitionStats['avg_fuel'], 2).' L' : '—' }}
            </p>
        </div>

    </div>

    <div class="rounded-xl bg-[var(--bg)] border border-[var(--border)] mt-2 p-4">

        <div class="flex justify-between items-center">

            <div>
                <p class="text-xs uppercase text-[var(--text-title)]">
                    {{ __('ui.bestlong') }}
                </p>

                <p class="text-xl font-semibold text-[var(--value-data)] mt-2">
                    {{ $competitionStats['long_run_best'] . ' Laps' ?? '—' }}
                </p>
            </div>

            <div>
                <form method="GET">

                    <select
                        name="report_scope"
                        onchange="this.form.submit()"
                        class="bg-[var(--badge)] border-[var(--border)] text-sm rounded pr-10 px-3 py-1 text-[var(--text)]"
                    >

                        <option
                            value="week"
                            {{ request('report_scope') === 'week' ? 'selected' : '' }}
                        >
                            {{ __('ui.week') }}
                        </option>

                        <option
                            value="season"
                            {{ request('report_scope', 'season') === 'season' ? 'selected' : '' }}
                        >
                            {{ __('ui.season') }}
                        </option>

                        <option
                            value="all"
                            {{ request('report_scope') === 'all' ? 'selected' : '' }}
                        >
                            {{ __('ui.historic') }}
                        </option>

                    </select>

                </form>
            </div>

        </div>

    </div>

        </div>

    </section>



{{-- COMPETITION TOOLS --}}

<div
    x-data="{
        activeTool: null,
        toolContent: '',
        loading: false,

        async loadTool(url, tool) {
            this.activeTool = tool;
            this.loading = true;
            this.toolContent = '';

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                this.toolContent = await response.text();

            } catch (error) {
                console.error(error);

                this.toolContent =
                    '<div class=\'rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center text-red-400\'>'
                    + 'No se ha podido cargar el contenido.'
                    + '</div>';

            } finally {
                this.loading = false;
            }
        },

        open(tool) {
            this.activeTool = tool;
            this.toolContent = '';
            this.loading = false;
        },

        openSessions(url) {
            this.loadTool(url, 'sessions');
        },

        openStrategy(url) {
            this.loadTool(url, 'strategy');
        },

        async submitStrategy(event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            event.preventDefault();

            const method = (form.method || 'GET').toUpperCase();
            const formData = new FormData(form);
            let url = form.action;

            const options = {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            };

            if (method === 'GET') {
                const params = new URLSearchParams();

                for (const [key, value] of formData.entries()) {
                    params.append(key, value);
                }

                if (params.toString() !== '') {
                    url += (url.includes('?') ? '&' : '?')
                        + params.toString();
                }

            } else {
                options.body = formData;
            }

            this.loading = true;

            try {
                const response = await fetch(url, options);

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                this.toolContent = await response.text();

            } catch (error) {
                console.error(error);

                this.toolContent =
                    '<div class=\'rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center text-red-400\'>'
                    + 'No se ha podido actualizar la estrategia.'
                    + '</div>';

            } finally {
                this.loading = false;
            }
        },

        close() {
            this.activeTool = null;
            this.toolContent = '';
            this.loading = false;
        }
    }"

    data-sessions-url="{{ route(
        'teamcenter.championships.sessions',
        ['series' => $series->id]
    ) }}"

    data-strategy-url="{{ route(
        'teamcenter.championships.strategy',
        ['series' => $series->id]
    ) }}"

    class="mt-5"
>

    {{-- TOOL BUTTONS --}}

    <div class="flex flex-wrap gap-3">

        {{-- SESSIONS --}}

        <button
            type="button"
            data-sessions-url="{{ route(
                'teamcenter.championships.sessions',
                ['series' => $series->id]
            ) }}"
            @click="openSessions($el.dataset.sessionsUrl)"
            class="w-32 px-4 py-2 uppercase font-microsport
                   text-center rounded-lg
                   border border-[var(--border)]
                   bg-[var(--btn-app)]
                   text-sm text-[var(--text-title)]
                   hover:bg-[var(--btn-app-hov)] transition"
        >
            Sessions
        </button>


        {{-- STINTS --}}

        <button
            type="button"
            @click="open('stints')"
            class="w-32 px-4 py-2 uppercase font-microsport
                   text-center rounded-lg
                   border border-[var(--border)]
                   bg-[var(--btn-app)]
                   text-sm text-[var(--text-title)]
                   hover:bg-[var(--btn-app-hov)] transition"
        >
            Stints
        </button>


        {{-- STRATEGY --}}

        <button
            type="button"
            data-strategy-url="{{ route(
                'teamcenter.championships.strategy',
                ['series' => $series->id]
            ) }}"
            @click="openStrategy($el.dataset.strategyUrl)"
            class="w-32 px-4 py-2 uppercase font-microsport
                   text-center rounded-lg
                   border border-[var(--border)]
                   bg-[var(--btn-app)]
                   text-sm text-[var(--text-title)]
                   hover:bg-[var(--btn-app-hov)] transition"
        >
            Strategy
        </button>

    </div>


    {{-- TOOL MODAL --}}

    <div
        x-show="activeTool !== null"
        x-transition.opacity
        @keydown.escape.window="close()"
        @click.self="close()"
        class="fixed inset-0 z-50 flex items-center justify-center
               bg-gray-900/60 backdrop-blur-sm p-4"
        style="display: none;"
    >

        <div
            x-show="activeTool !== null"
            x-transition
            class="w-full max-w-6xl max-h-[90vh]
                   overflow-hidden
                   rounded-xl
                   border border-[var(--border)]
                   bg-[var(--card)]
                   shadow-2xl"
        >

            {{-- HEADER --}}

            <div
                class="flex items-center justify-between
                       px-6 py-4
                       border-b border-[var(--border)]"
            >

                <div>

                    <h3
                        class="text-lg font-semibold
                               text-[var(--text-card-title)]"
                    >

                        <span x-show="activeTool === 'sessions'">
                            Sessions
                        </span>

                        <span x-show="activeTool === 'stints'">
                            Stints
                        </span>

                        <span x-show="activeTool === 'strategy'">
                            Strategy
                        </span>

                    </h3>

                    <p
                        class="mt-1 text-xs
                               text-[var(--text-muted)]"
                    >
                        {{ $series->iracingSeries->name }}
                        ·
                        {{ $series->season_year }}
                        S{{ $series->season_number }}
                    </p>

                </div>


                {{-- CLOSE BUTTON --}}

                <button
                    type="button"
                    @click="close()"
                    class="text-[var(--text-muted)]
                           hover:text-[var(--text)]
                           transition"
                    aria-label="Close"
                >
                    <i
                        data-lucide="x"
                        class="h-5 w-5"
                    ></i>
                </button>

            </div>


            {{-- MODAL CONTENT --}}

            <div
                class="max-h-[calc(90vh-8rem)]
                       overflow-y-auto"
            >

                {{-- SESSIONS --}}

                <div
                    x-show="activeTool === 'sessions'"
                    class="p-6"
                >

                    <template x-if="loading">
                        <div
                            class="rounded-xl
                                   border border-[var(--border)]
                                   bg-[var(--bg)]
                                   p-8 text-center"
                        >
                            <p class="text-sm text-[var(--text-muted)]">
                                Cargando sesiones...
                            </p>
                        </div>
                    </template>

                    <div
                        x-show="!loading && toolContent !== ''"
                        x-html="toolContent"
                    ></div>

                </div>


                {{-- STINTS --}}

                <div
                    x-show="activeTool === 'stints'"
                    class="p-6"
                >

                    <div
                        class="rounded-xl
                               border border-[var(--border)]
                               bg-[var(--bg)]
                               p-8 text-center"
                    >

                        <p class="text-sm text-[var(--text-muted)]">
                            Stints
                        </p>

                    </div>

                </div>


                {{-- STRATEGY --}}

                <div
                    x-show="activeTool === 'strategy'"
                    class="p-6"
                    @submit.prevent="submitStrategy($event)"
                >

                    {{-- LOADING --}}

                    <template x-if="loading">
                        <div
                            class="rounded-xl
                                   border border-[var(--border)]
                                   bg-[var(--bg)]
                                   p-8 text-center"
                        >
                            <p class="text-sm text-[var(--text-muted)]">
                                Cargando estrategia...
                            </p>
                        </div>
                    </template>


                    {{-- STRATEGY CONTENT --}}

                    <div
                        x-show="!loading && toolContent !== ''"
                        x-html="toolContent"
                    ></div>

                </div>

            </div>


            {{-- FOOTER --}}

            <div
                class="flex justify-end
                       px-6 py-4
                       border-t border-[var(--border)]"
            >

                <button
                    type="button"
                    @click="close()"
                    class="px-4 w-28 py-2
                           text-sm font-medium
                           text-[var(--text)]
                           bg-[var(--btn-cancel)]
                           hover:bg-[var(--btn-cancel-h)]
                           rounded-lg transition"
                >
                    Back
                </button>

            </div>

        </div>

    </div>

</div>


    {{-- TEAM --}}
    <section class="mt-8">

        <div class="flex items-center justify-between mb-4">

            <div>

                <h2
                    class="text-lg font-semibold
                        text-[var(--text)]"
                >
                    Team Drivers
                </h2>

                <p
                    class="text-sm
                        text-[var(--text-muted)]"
                >
                    Drivers participating in this competition
                </p>

            </div>

            {{-- ADD DRIVER: UI ONLY FOR NOW --}}
            <a href="{{ route('team.competitions.drivers.create', $series) }}"
                class="inline-flex items-center rounded-lg bg-[var(--accent)] px-4 py-2 text-sm font-semibold
                     text-white hover:opacity-90 transition">
            + Add Driver
            </a>

        </div>


        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] overflow-hidden">
            @php
                $competitionRoles = [
                    '' => 'No Role',
                    'First Driver' => 'First Driver',
                    'Second Driver' => 'Second Driver',
                    'Reserve Driver' => 'Reserve Driver',
                    'Test Driver' => 'Test Driver',
                ];
            @endphp
            @forelse($teamMembers as $member)

                @php
                    $competitionMember = $entry->members
                        ->firstWhere('user_id', $member->id);
                @endphp

                <div
                    class="flex items-center
                        justify-between
                        px-5 py-4
                        border-b
                        border-[var(--border)]
                        last:border-b-0"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="w-10 h-10
                                overflow-hidden
                                shrink-0"
                        >

                            @if($member->iracing_helmet_path)

                                <img
                                    src="{{ asset("/storage/" .
                                        $member->iracing_helmet_path
                                    ) }}"
                                    alt="{{ $member->name }}"
                                    class="w-full h-full
                                        object-cover"
                                >

                            @endif

                        </div>


                        <div>

                            <div
                                class="font-medium
                                    text-[var(--text)]"
                            >
                                {{ $member->name }}
                            </div>

                            <div
                                class="text-xs
                                    text-[var(--text-muted)]"
                            >
                                @if($competitionMember)

                                    @if($competitionMember->role)

                                        {{ $competitionMember->role }}

                                    @else

                                        Driver

                                    @endif

                                @else

                                    Team Member

                                @endif
                            </div>

                        </div>

                    </div>

                    @if($canManageCompetition)
                    <div class="flex items-center gap-3">

                        @if($competitionMember)

                            <div class="flex items-center gap-3">

                                <span
                                    class="rounded-full
                                        px-3 py-1
                                        text-xs
                                        bg-[var(--accent)]
                                        text-white"
                                >
                                    {{ ucfirst($competitionMember->status) }}
                                </span>

                                <a
                                    href="#edit-driver-{{ $competitionMember->id }}"
                                    class="text-sm
                                        text-[var(--text-muted)]
                                        hover:text-[var(--text)]
                                        transition"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'team.competitions.drivers.destroy',
                                        [
                                            'series' => $series,
                                            'member' => $competitionMember,
                                        ]
                                    ) }}"
                                    onsubmit="return confirm(
                                        'Cancel this driver participation?'
                                    );"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-sm
                                            text-red-500
                                            hover:text-red-400
                                            transition"
                                    >
                                        Cancel
                                    </button>

                                </form>

                            </div>

                        @else

                            <span
                                class="rounded-full
                                    px-3 py-1
                                    text-xs
                                    border border-[var(--border)]
                                    text-[var(--text-muted)]"
                            >
                                Available
                            </span>

                        @endif



@if($competitionMember)

    <div
        id="edit-driver-{{ $competitionMember->id }}"
        class="mt-4 ml-14 mr-5 mb-4
               rounded-lg
               border border-[var(--border)]
               bg-[var(--bg)]
               p-4"
    >

        <form
            method="POST"
            action="{{ route(
                'team.competitions.drivers.update',
                [
                    'series' => $series,
                    'member' => $competitionMember,
                ]
            ) }}"
            class="flex flex-col
                   md:flex-row
                   md:items-end
                   gap-4"
        >

            @csrf
            @method('PATCH')

            <div class="flex-1">

                <label
                    for="role-{{ $competitionMember->id }}"
                    class="block text-xs uppercase
                           tracking-wider
                           text-[var(--text-muted)]
                           mb-2"
                >
                    Competition Role
                </label>

                <select id="role-{{ $competitionMember->id }}" name="role" class="w-full rounded-lg border border-[var(--border)]
                        bg-[var(--card)] px-3 py-2 text-sm text-[var(--text)]"
                >
                    @foreach($competitionRoles as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected($competitionMember->role === $value)
                        >
                            {{ $label }}
                        </option>

                    @endforeach
                </select>

            </div>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="rounded-lg
                           bg-[var(--accent)]
                           px-4 py-2
                           text-sm font-semibold
                           text-white"
                >
                    Save
                </button>

                <a
                    href="{{ route(
                        'team.competitions.show',
                        $series
                    ) }}"
                    class="rounded-lg
                           border border-[var(--border)]
                           px-4 py-2
                           text-sm
                           text-[var(--text)]"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

@endif




                    </div>




                    @endif




                </div>

            @empty

                <div class="px-5 py-8 text-center">

                    <div
                        class="text-sm
                            text-[var(--text-muted)]"
                    >
                        This Team currently has no members.
                    </div>

                </div>

            @endforelse

        </div>

    </section>


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
                    class="px-5 py-2
                           border-b
                           border-[var(--border)]
                           last:border-b-0"
                >

                    <div
                        class="flex items-center gap-4"
                    >

                        <div
                            class="w-16 shrink-0
                                   text-sm font-semibold
                                   text-[var(--text-title)]"
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
                            <img @click="open = true" src="{{ asset('storage/' . themedlogo($round->track)) }}"
                                    alt="{{ $round->track->name }}"
                                    class="w-24 py-1
                                           h-auto object-contain">

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
            class="w-full max-w-[1000px] bg-[var(--card)] rounded-xl shadow-2xl border border-[var(--border)] overflow-hidden">

            <!-- Encabezado -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--b-card)]">
                <h3 class="text-lg font-semibold text-[var(--text-card-title)]">
                    @if( $round->track->variant )
                {{ $round->track->display_name . ' / ' . $round->track->variant }}
                @else
                {{ $round->track->display_name }}
                @endif
                </h3>
                <button
                    @click="open = false"
                    class="text-[var(--text-card-title)] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <!-- Cuerpo -->
            <div class="text-[var(--text)]">

        @if( $round->track )
            <div class="text-[var(--accent)] capitalize">
            @include('circuits.svg.' . $round->track->id)
            {{-- <div> {!! $stint->track->map_svg !!}      </div> --}}
            </div>
            @else
            <div class="p-8 m-auto text-center">No track image</div>
            @endif

            </div>

            <!-- Pie / Acciones -->
            {{-- <div>
                <h4 class="text-xs mb-0  px-3 text-[var(--text-soft)]">Image by motorsportmagazine.com</h4>
            </div> --}}
            <div class="flex justify-end gap-3 px-6 py-4 bg-[var(--card)]">
                <button
                    @click="open = false"
                    class="px-4 w-28 py-2 text-sm font-medium text-[var(--text)] bg-[var(--btn-cancel)] hover:bg-[var(--btn-cancel-h)] rounded-lg transition">
                    Back
                </button>
                {{-- <button
                    @click="open = false"
                    class="px-4 w-28 py-2 text-sm font-medium text-[var(--text)] bg-[var(--btn-confirm)] hover:bg-[var(--btn-confirm-h)] rounded-lg transition">
                    Confirmar
                </button> --}}
            </div>
        </div>
    </div>
</div>












                            @else

                                <span
                                    class="text-[9px]
                                           px-4 py-2
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
                                       text-[var(--text-title)]"
                            >

                                {{ $round->track?->display_name
                                    ?? 'Track unavailable' }}

                                @if($round->track?->variant)

                                    <span
                                        class="text-[var(--text-muted)]"
                                    >
                                       - {{ $round->track->variant }}
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


    {{-- TEAM ACTIONS --}}
    <section class="mt-8 pb-10">

        <div
            class="flex flex-wrap gap-3"
        >

            <a
                href="{{ route('teamcenter.championships.index') }}"
                class="px-4 py-2
                       rounded-lg
                       border border-[var(--border)]
                       text-[var(--text)]
                       hover:bg-[var(--card-hover)]
                       transition"
            >
                Back to Team Competitions
            </a>

        </div>

    </section>

</div>

@endsection
