@extends('layouts.app')

@section('title', 'Detalle Stint')
@section('page-title', $stint->user?->name. ' at ' .  $stint->track->display_name. ' - ' . $stint->track->variant. ' with ' . $stint->car_name)

@section('content')

{{-- INFO CARD --}}
<div class="bg-[var(--bg)] border border-[var(--border)] rounded-xl p-6 shadow-sm shadow-black/20 mb-6">

    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 text-base pt-2">
        <div>
            <div class="text-[var(--text)]">{{ __('ui.driver') }}</div>
            <div class="text-[var(--value-data)] text-base font-medium">
                {{ $stint->user?->iracing_name ?? '—' }}
            </div>
        </div>

        <div>
            <div class="text-[var(--text)]">{{ __('ui.team') }}</div>
            <div class="text-[var(--value-data)] font-medium mt-1">
                <img src="/storage/{{ $stint->user->team->logo_path }}" width="145" height="32">
            </div>
        </div>
        <div>
               @if($stint->track?->logo)

                                <div x-data="{ open: false }">
                                <img @click="open = true" src="{{ asset('storage/' . themedlogo($stint->track )) }}"
                                    title="{{ $stint->track->variant }}"
                                    alt="{{ $stint->track->name }}"
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
            class="w-full max-w-[1000px] bg-[var(--card)] rounded-xl shadow-2xl border border-[var(--border)] overflow-hidden">

            <!-- Encabezado -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--b-card)]">
                <h3 class="text-lg font-semibold text-[var(--text-card-title)]">
                    @if( $stint->track->variant )
                {{ $stint->track->name . ' / ' . $stint->track->variant }}
                @else
                {{ $stint->track->name }}
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

            @if( $stint->track )
            <div class="text-[var(--accent)] capitalize">
            @include('circuits.svg.' . $stint->track->id)
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

        <div>
            <div class="text-[var(--text)]">{{ __('ui.type') }}</div>
            <div class="text-[var(--value-info)] text-base font-medium mt-1 capitalize">{{ $stint->session_phase }} </div>
        </div>
        {{-- <div>
            <div class="text-gray-400">Circuito</div>
            <div class="text-white font-medium mt-1">{{ $stint->track }}</div>
        </div> --}}

        {{-- <div>
            <div class="text-gray-400">Coche</div>
            <div class="text-white font-medium mt-1">{{ $stint->car }}</div>
        </div> --}}

        <div>
            <div class="text-[var(--text)]">{{ __('ui.date') }}</div>
            <div class="text-[var(--text)] font-medium mt-1">{{ $stint->created_at }}</div>
        </div>
    </div>
</div>

<div class="bg-[var(--bg)] border border-[var(--border)] rounded-xl p-4 shadow-sm shadow-black/20 mb-6">

    <table class="w-full text-sm text-center border-collapse">

        {{-- HEADER --}}
        <thead class="text-[var(--text)] border-b border-[var(--border)] text-xs uppercase tracking-wide">
            <tr>
                <th class="py-2">{{ __('ui.laps') }}</th>
                <th>{{ __('ui.duration') }}</th>
                <th>{{ __('ui.timing') }}</th>
                <th>{{ __('ui.consumption') }}</th>
                <th>{{ __('ui.weather') }}</th>
                <th>{{ __('ui.trackstate') }}</th>
                <th>{{ __('ui.fastestlap') }}</th>
            </tr>
        </thead>

        {{-- DATA --}}
        <tbody class="text-white">
            <tr class="transition">

                {{-- Vueltas --}}
                <td class="py-3 text-indigo-400 font-semibold">
                    {{ $stint->laps->count() }}
                </td>

                {{-- Duración --}}
                <td class="text-[var(--text)] font-semibold font-mono-timing">
                    {{ round($stint->duration_seconds / 60, 1) }} {{ __('ui.min') }}
                </td>

                {{-- Ritmo --}}
                <td class="text-[var(--lap-avg)]  font-semibold font-mono-timing">
                    {{ lapTime($stint->avg_lap) }}
                </td>

                {{-- Consumo --}}
                <td class="text-[var(--fuel)] font-semibold">
                    {{ number_format($metrics['avg_fuel'],3) }} L/{{ __('ui.lap') }}
                </td>

                {{-- Clima --}}
                <td class="text-[var(--text)] font-semibold">
                    @if($stint->current_sky == 0)
                        ☀️ {{ __('ui.clear') }}
                    @elseif($stint->current_sky == 1)
                        🌤 {{ __('ui.pcloudy') }}
                    @elseif($stint->current_sky == 2)
                        ⛅ {{ __('ui.mcloudy') }}
                    @elseif($stint->current_sky == 3)
                        ☁️ {{ __('ui.overcast') }}
                    @else
                        -
                    @endif
                </td>

                {{-- Estado pista --}}
                <td class="text-[var(--text)] font-semibold ">

                    @if($stint->humidity > 0.94)
                    <div class="m-auto align-middle text-center border border-[var(--wet-b)]  bg-[var(--wet-bg)] rounded-md w-16">

                    <strong class="text-[var(--wet-text)]">WET</strong>
                    </div>
                    @else
                    <div class="m-auto align-middle text-center border border-[var(--dry-b)]  bg-[var(--dry-bg)] rounded-md w-16">

                    <strong class="text-[var(--dry-text)]">DRY</strong>
                    </div>
                    @endif
                    </div>
                </td>

                {{-- Fastest --}}
                <td class="text-[var(--lap-best)] font-mono text-sm">
                    <strong>
                        {{ laptime($metrics['best_lap']) }}
                    </strong>
                </td>

            </tr>
        </tbody>

    </table>

</div>


{{-- LAPS TABLE --}}
<div class="bg-[var(--bg)] rounded-xl shadow-sm shadow-black/20 border border-[var(--border)] overflow-hidden">
    <div x-data="{ showSectors: false }">
    <div class="px-6 py-4 flex items-center justify-between">

        <h3 class="text-lg font-semibold">
            {{ __('ui.reglaps') }}
        </h3>

        <div x-data="{ compact: false }" class="flex items-center gap-3">
            {{-- @if($stint->tyres()->where('snapshot_type', 'end')->exists()) --}}
            @if($stint->setup)
            <x-ui.button variant="option" size="xs" title="Car Setup" href="{{ route('setups.show', $stint->setup->id) }}">
                <svg width="32" height="32" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M797.866667 524.8l-247.466667-46.933333-83.2-238.933334-81.066667 27.733334L469.333333 503.466667l-166.4 192 64 55.466666 166.4-192 247.466667 46.933334 17.066667-81.066667z" fill="currentColor"></path><path d="M512 405.333333c-59.733333 0-106.666667 46.933333-106.666667 106.666667s46.933333 106.666667 106.666667 106.666667 106.666667-46.933333 106.666667-106.666667-46.933333-106.666667-106.666667-106.666667z m0 149.333334c-23.466667 0-42.666667-19.2-42.666667-42.666667s19.2-42.666667 42.666667-42.666667 42.666667 19.2 42.666667 42.666667-19.2 42.666667-42.666667 42.666667z" fill="currentColor"></path><path d="M868.266667 576c4.266667-21.333333 6.4-42.666667 6.4-64s-2.133333-42.666667-6.4-64l70.4-51.2c8.533333-6.4 12.8-19.2 6.4-29.866667L853.333333 209.066667c-6.4-10.666667-17.066667-14.933333-27.733333-8.533334L746.666667 234.666667c-32-27.733333-70.4-49.066667-110.933334-64l-8.533333-87.466667c-2.133333-10.666667-10.666667-19.2-21.333333-19.2h-183.466667c-10.666667 0-21.333333 8.533333-21.333333 19.2L388.266667 170.666667c-40.533333 14.933333-78.933333 36.266667-110.933334 64L198.4 198.4c-10.666667-4.266667-23.466667 0-27.733333 10.666667l-91.733334 157.866666c-6.4 10.666667-2.133333 23.466667 6.4 29.866667L155.733333 448c-4.266667 21.333333-6.4 42.666667-6.4 64s2.133333 42.666667 6.4 64L85.333333 627.2c-8.533333 6.4-12.8 19.2-6.4 29.866667L170.666667 814.933333c6.4 10.666667 17.066667 14.933333 27.733333 8.533334L277.333333 789.333333c32 27.733333 70.4 49.066667 110.933334 64l8.533333 87.466667c2.133333 10.666667 10.666667 19.2 21.333333 19.2h183.466667c10.666667 0 21.333333-8.533333 21.333333-19.2l8.533334-87.466667c40.533333-14.933333 78.933333-36.266667 110.933333-64l78.933333 36.266667c10.666667 4.266667 23.466667 0 27.733334-8.533333l91.733333-157.866667c6.4-10.666667 2.133333-23.466667-6.4-29.866667L868.266667 576zM512 746.666667c-130.133333 0-234.666667-104.533333-234.666667-234.666667s104.533333-234.666667 234.666667-234.666667 234.666667 104.533333 234.666667 234.666667-104.533333 234.666667-234.666667 234.666667z" fill="#FF9800"></path></g></svg>
            </x-ui.button>
            @else
            <form action="{{ route('stints.repair-setup', $stint) }}" method="POST">

                @csrf
                <x-ui.button
                    variant="option"
                    size="xs"
                    title="Repair Setup">

                    repair

                </x-ui.button>
            </form>
            @endif
            {{-- 🔗 BOTÓN ANÁLISIS --}}
            <x-ui.button variant="option" size="xs" title="Stint analysis" href="{{ route('stints.analysis', [$stint]) }}">
                <svg width="32" height="32" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M789.333333 384h128v512h-128zM618.666667 554.666667h128v341.333333h-128zM448 469.333333h128v426.666667h-128zM277.333333 682.666667h128v213.333333h-128zM106.666667 597.333333h128v298.666667H106.666667z" fill="#00BCD4"></path><path d="M170.666667 341.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="currentColor"></path><path d="M341.333333 384m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="currentColor"></path><path d="M512 234.666667m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="currentColor"></path><path d="M682.666667 277.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="currentColor"></path><path d="M853.333333 192m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="currentColor"></path><path d="M834.133333 153.6l-155.733333 78.933333-177.066667-44.8-170.666666 149.333334-149.333334-36.266667-21.333333 81.066667 192 49.066666 170.666667-149.333333 164.266666 40.533333 185.6-91.733333z" fill="currentColor"></path></g></svg>
            </x-ui.button>

                {{-- 🔗 BOTÓN PRINT --}}
            <div x-data="{ open:false }">
            <x-ui.button variant="option" size="xs" title="Data prints" @click="open=true" class="pt-2">
                <svg width="30" height="30" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M192 234.666667h640v64H192z" fill="#424242"></path><path d="M85.333333 533.333333h853.333334v-149.333333c0-46.933333-38.4-85.333333-85.333334-85.333333H170.666667c-46.933333 0-85.333333 38.4-85.333334 85.333333v149.333333z" fill="#616161"></path><path d="M170.666667 768h682.666666c46.933333 0 85.333333-38.4 85.333334-85.333333v-170.666667H85.333333v170.666667c0 46.933333 38.4 85.333333 85.333334 85.333333z" fill="#424242"></path><path d="M853.333333 384m-21.333333 0a21.333333 21.333333 0 1 0 42.666667 0 21.333333 21.333333 0 1 0-42.666667 0Z" fill="#00E676"></path><path d="M234.666667 85.333333h554.666666v213.333334H234.666667z" fill="currentColor"></path><path d="M800 661.333333h-576c-17.066667 0-32-14.933333-32-32s14.933333-32 32-32h576c17.066667 0 32 14.933333 32 32s-14.933333 32-32 32z" fill="#242424"></path><path d="M234.666667 661.333333h554.666666v234.666667H234.666667z" fill="#90CAF9"></path><path d="M234.666667 618.666667h554.666666v42.666666H234.666667z" fill="#42A5F5"></path><path d="M341.333333 704h362.666667v42.666667H341.333333zM341.333333 789.333333h277.333334v42.666667H341.333333z" fill="#1976D2"></path></g></svg>
            </x-ui.button>
            <x-action-modal title="Print Options">

                <div class="space-y-2 bg-[var(--card)]">

                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">

                    Print Stint Data

                    </button>

                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">

                    Print Comparative Stint

                    </button>


                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">
                     <a href="{{ route('setups.sheet.pdf', $stint->setup) }}" target="_blank">
                        Print Setup Sheet
                    </a>
                    </button>


                    <button

                    Print Setup Data

                    </button>

                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">

                    Print Comparative Setup

                    </button>

                </div>

            </x-action-modal>
            </div>

            <x-ui.button class="hover:text-orange-500" variant="option" title="Show/Hide data" size="xs" @click="showSectors = !showSectors">
                <span x-show="!showSectors"><svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M20 20V4H4V20H20ZM18.5 18.5H16V5.5H18.5V18.5ZM14.5 5.5V18.5H5.5V5.5H14.5Z" fill="#808080"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M10.4434 12.0041L7.96967 9.53033L9.03033 8.46967L12.5647 12.0041L9.03098 15.5378L7.97032 14.4771L10.4434 12.0041Z" fill="currentcolor"></path> </g></svg></span>
                <span x-show="showSectors"><svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" transform="matrix(-1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M20 20V4H4V20H20ZM18.5 18.5H16V5.5H18.5V18.5ZM14.5 5.5V18.5H5.5V5.5H14.5Z" fill="#ffaa00"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M10.4434 12.0041L7.96967 9.53033L9.03033 8.46967L12.5647 12.0041L9.03098 15.5378L7.97032 14.4771L10.4434 12.0041Z" fill="currentColor"></path> </g></svg></span>
            </x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto">
@php
$maxSectors = $laps->max(function($lap){
    return $lap->sectors->count();
});
@endphp

<div x-data="{
    open: false,
    lap: null,

    formatTime(seconds) {
        if (!seconds) return '-';

        let mins = Math.floor(seconds / 60);
        let secs = (seconds % 60).toFixed(3).padStart(6, '0');

        return mins + ':' + secs;
    }
}">
        {{-- 🔥 REVISIÓN: toggle sectores --}}

        <table class="min-w-full text-sm">
            <div x-data="{ compact: false }">
            <thead class="bg-[var(--card)] text-[var(--text-card-title)] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">{{ __('ui.lap') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.time') }}</th>
                    @for ($i = 1; $i <= $maxSectors; $i++)
                    <th class="px-6 py-3 text-left" x-show="showSectors">S{{ $i }}</th>
                    @endfor
                    <th class="px-6 py-3 text-left">{{ __('ui.fuel') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.tracktemp') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.windtemp') }}</th>
                    <th class="px-6 py-3 text-left" x-show="showSectors">{{ __('ui.hummidity') }}</th>
                    <th class="px-6 py-3 text-left" x-show="showSectors">{{ __('ui.wind') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.weather') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.track') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[var(--div)]">

            @php
            // 🟣 Mejor vuelta
            $bestLap = $laps->min('lap_time');

            // 🟣 Mejores sectores
            $bestSectors = [];

            for ($i = 0; $i < $maxSectors; $i++) {
                $bestSectors[$i] = $laps
                    ->filter(fn($lap) => isset($lap->sectors[$i]))
                    ->map(fn($lap) => $lap->sectors[$i]->sector_time)
                    ->min();
                }
                @endphp

                @foreach($laps as $lap)
                <tr class="hover:bg-[var(--card-hover)] transition">
                    @if($lap->offtrack_count > 0)
                    <td class="flex flex-auto px-6 py-4 text-[var(--danger)] font-medium">
                    @else
                    <td class="flex flex-auto px-6 py-4 font-medium">
                    @endif
                        <x-lap-status-badge :lap="$lap" />
                    @if($lap->pit_in)
                        <x-ui.button size="xs" variant="pit_in">
                          pit in
                        </x-ui.button>
                    @elseif ($lap->pit_out)
                    <x-ui.button size="xs" variant="pit_out">
                        pit out
                    </x-ui.button>
                    @endif

                    </td>
                    {{-- @if(!$lap->is_valid_lap)
                    <td class="px-6 py-4 text-red-600 dark:text-red-300 font-medium">
                    @else
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium">
                    @endif
                        <div class="flex items-center gap-1">
                        {{ $lap->lap_number }}
                        @if($lap->is_pit_lap)
                            <span class=" text-red-400 ml-1">(P)</span>
                        @endif
                        @if(!$lap->is_valid_lap)
                        <span
                            class="hover:text-red-500 dark:text-gray-600 text-gray-400 cursor-help ml-1"
                            title="Vuelta inválida, {{ $lap->offtrack_count }} offtracks"
                        >
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13.0619 4.4295C12.6213 3.54786 11.3636 3.54786 10.9229 4.4295L3.89008 18.5006C3.49256 19.2959 4.07069 20.2317 4.95957 20.2317H19.0253C19.9142 20.2317 20.4923 19.2959 20.0948 18.5006L13.0619 4.4295ZM9.34196 3.6387C10.434 1.45376 13.5508 1.45377 14.6429 3.63871L21.6758 17.7098C22.6609 19.6809 21.2282 22 19.0253 22H4.95957C2.75669 22 1.32395 19.6809 2.3091 17.7098L9.34196 3.6387Z" fill="CurrentColor"></path> <path d="M12 8V13" stroke="#DF1463" stroke-width="1.7" stroke-linecap="round"></path> <path d="M12 16L12 16.5" stroke="#DF1463" stroke-width="1.7" stroke-linecap="round"></path> </g></svg>
                        </span>
                    @endif
                    </td> --}}
                   {{-- LAP TIME --}}
                    <td class="px-6 py-4 cursor-pointer "
                        @click='
                            open = true;
                            lap = @json([
                                "lap" => $lap->lap,
                                "lap_time" => (float) $lap->lap_time,
                                "sectors" => $lap->sectors->map(fn($s) => [
                                "sector_time" => (float) $s->sector_time
                                ])->values()
                            ])
                        '
                    >

                    <span class="
                            font-mono-timing
                            hover:text-[var(--text-h)]
                            transition
                            {{ abs($lap->lap_time - $bestLap) < 0.001
                                ? 'text-[var(--lap-best)] hover:text[var(--text-h)] font-semibold'
                                : '' }}
                        ">
                        {{ lapTime($lap->lap_time) }}
                    </span>
                    </td>
                    {{-- SECTORS --}}
                    @php
                    $sectors = is_array($lap->sectors) ? $lap->sectors : json_decode($lap->sectors, true);
                    @endphp

                    @for ($i = 0; $i < $maxSectors; $i++)
                    <td class="px-6 py-4 font-mono-timing" x-show="showSectors"
                    @if(isset($lap->sectors[$i]) && $lap->sectors[$i]->sector_time == $bestSectors[$i])
                        text-purple-700 dark:text-purple-400 font-semibold
                    @else
                        text-gray-500 dark:text-gray-300
                    @endif
                    ">
                    @if(isset($lap->sectors[$i]))
                        {{ number_format($lap->sectors[$i]->sector_time,3) }}
                    @else
                        -
                    @endif
                    </td>
                    @endfor
                    <td class="px-6 py-2 font-mono-timing">

                        @if($lap->fuel_consumed)

                            {{-- 🔥 Consumo --}}
                            <div class="
                                {{ $lap->fuel_consumed < $stint->fuel_per_lap * 0.97 ? 'text-[var(--success)]' : '' }}
                                {{ $lap->fuel_consumed > $stint->fuel_per_lap * 1.03 ? 'text-[var(--danger)]' : 'text-[var(--lap-best)]' }}
                            ">
                                {{ number_format($lap->fuel_consumed, 3) }} L
                            </div>

                            {{-- 🧠 Fuel restante (calculado) --}}
                            <div class="text-xs">
                                {{ number_format($lap->fuel_lap_start, 1) }} L
                            </div>

                        @else
                            -
                        @endif

                    </td>

                    <td class="px-6 py-4">
                        {{ number_format($lap->track_temp, 2) }} °C
                    </td>

                    <td class="px-6 py-4">
                        {{ number_format($lap->air_temp, 2) }} °C
                    </td>
                    <td class="px-6 py-4" x-show="showSectors">
@if($lap->humidity)
{{ number_format($lap->humidity,2)*100 }} %
@endif
</td>

<td class="flex px-6 py-4 w-48" x-show="showSectors">
<span class="flex w-12 text-[var(--value-info)]">
    @php

    // WindDir llega en radianes
    $dir = rad2deg($lap->wind_dir);

    // Invertimos 180º para mostrar
    // hacia dónde sopla el viento
    $dir = ($dir + 180) % 360;

    @endphp

@if($dir >= 337 || $dir < 22)
S <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23178 8.97353C4.87822 8.54926 4.93554 7.91869 5.35982 7.56513L9.35982 4.23179C9.7841 3.87823 10.4147 3.93555 10.7682 4.35983C11.1218 4.78411 11.0645 5.41467 10.6402 5.76824L6.64019 9.10157C6.21591 9.45513 5.58535 9.39781 5.23178 8.97353Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M14.7682 8.97353C14.4147 9.39781 13.7841 9.45513 13.3598 9.10157L9.35982 5.76824C8.93554 5.41467 8.87822 4.78411 9.23178 4.35983C9.58535 3.93555 10.2159 3.87823 10.6402 4.23179L14.6402 7.56513C15.0645 7.91869 15.1218 8.54926 14.7682 8.97353Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 6C10.5523 6 11 6.44772 11 7V15C11 15.5523 10.5523 16 10 16C9.44772 16 9 15.5523 9 15V7C9 6.44772 9.44772 6 10 6Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 67)
SW <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M7.35412 5.90263C7.40412 5.35261 7.89053 4.94727 8.44054 4.99727L13.626 5.46868C14.176 5.51868 14.5814 6.00509 14.5314 6.5551C14.4814 7.10512 13.9949 7.51046 13.4449 7.46046L8.25947 6.98906C7.70946 6.93906 7.30411 6.45264 7.35412 5.90263Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M14.0974 12.6459C13.5474 12.6959 13.061 12.2905 13.011 11.7405L12.5396 6.55505C12.4896 6.00503 12.8949 5.51862 13.4449 5.46862C13.9949 5.41862 14.4814 5.82396 14.5314 6.37397L15.0028 11.5594C15.0528 12.1094 14.6474 12.5959 14.0974 12.6459Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8284 7.17156C13.2189 7.56208 13.2189 8.19524 12.8284 8.58577L7.17153 14.2426C6.78101 14.6331 6.14784 14.6331 5.75732 14.2426C5.36679 13.8521 5.36679 13.2189 5.75732 12.8284L11.4142 7.17156C11.8047 6.78103 12.4379 6.78103 12.8284 7.17156Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 112)
W<svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0264 5.23175C11.4507 4.87819 12.0813 4.93551 12.4348 5.35979L15.7682 9.35979C16.1217 9.78407 16.0644 10.4146 15.6401 10.7682C15.2159 11.1218 14.5853 11.0644 14.2317 10.6402L10.8984 6.64016C10.5448 6.21588 10.6022 5.58532 11.0264 5.23175Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0264 14.7682C10.6022 14.4146 10.5448 13.7841 10.8984 13.3598L14.2317 9.35979C14.5853 8.93551 15.2159 8.87819 15.6401 9.23175C16.0644 9.58532 16.1217 10.2159 15.7682 10.6402L12.4348 14.6402C12.0813 15.0644 11.4507 15.1218 11.0264 14.7682Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M14 10C14 10.5523 13.5523 11 13 11L5 11C4.44771 11 4 10.5523 4 10C4 9.44772 4.44771 9 5 9L13 9C13.5523 9 14 9.44772 14 10Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 157)
NW<svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M14.0975 7.35427C14.6475 7.40427 15.0528 7.89068 15.0028 8.4407L14.5314 13.6261C14.4814 14.1762 13.995 14.5815 13.445 14.5315C12.895 14.4815 12.4896 13.9951 12.5396 13.4451L13.011 8.25963C13.061 7.70961 13.5474 7.30427 14.0975 7.35427Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M7.35418 14.0975C7.30418 13.5475 7.70952 13.0611 8.25953 13.0111L13.445 12.5397C13.995 12.4897 14.4814 12.895 14.5314 13.445C14.5814 13.995 14.1761 14.4814 13.6261 14.5314L8.44061 15.0029C7.89059 15.0529 7.40418 14.6475 7.35418 14.0975Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8284 12.8285C12.4379 13.2191 11.8048 13.2191 11.4142 12.8285L5.75738 7.17168C5.36685 6.78116 5.36685 6.14799 5.75738 5.75747C6.1479 5.36695 6.78107 5.36695 7.17159 5.75747L12.8284 11.4143C13.219 11.8048 13.219 12.438 12.8284 12.8285Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 202)
N <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M14.7682 11.0265C15.1218 11.4507 15.0645 12.0813 14.6402 12.4349L10.6402 15.7682C10.2159 16.1218 9.58535 16.0644 9.23179 15.6402C8.87822 15.2159 8.93555 14.5853 9.35983 14.2318L13.3598 10.8984C13.7841 10.5449 14.4147 10.6022 14.7682 11.0265Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23179 11.0265C5.58535 10.6022 6.21592 10.5449 6.64019 10.8984L10.6402 14.2318C11.0645 14.5853 11.1218 15.2159 10.7682 15.6402C10.4147 16.0644 9.7841 16.1218 9.35983 15.7682L5.35983 12.4349C4.93555 12.0813 4.87822 11.4507 5.23179 11.0265Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 14C9.44772 14 9 13.5523 9 13V5C9 4.44771 9.44772 4 10 4C10.5523 4 11 4.44771 11 5V13C11 13.5523 10.5523 14 10 14Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 247)
NE <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6458 14.0975C12.5958 14.6475 12.1094 15.0529 11.5594 15.0029L6.37394 14.5314C5.82393 14.4814 5.41859 13.995 5.46859 13.445C5.51859 12.895 6.005 12.4897 6.55502 12.5397L11.7405 13.0111C12.2905 13.0611 12.6958 13.5475 12.6458 14.0975Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.90254 7.35427C6.45255 7.30427 6.93896 7.70961 6.98897 8.25963L7.46037 13.4451C7.51037 13.9951 7.10503 14.4815 6.55501 14.5315C6.005 14.5815 5.51859 14.1762 5.46858 13.6261L4.99718 8.4407C4.94718 7.89068 5.35252 7.40427 5.90254 7.35427Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M7.17155 12.8286C6.78103 12.438 6.78103 11.8049 7.17155 11.4144L12.8284 5.7575C13.2189 5.36697 13.8521 5.36697 14.2426 5.7575C14.6331 6.14802 14.6331 6.78119 14.2426 7.17171L8.58577 12.8286C8.19524 13.2191 7.56208 13.2191 7.17155 12.8286Z" fill="CurrentColor"></path> </g></svg>
@elseif($dir < 292)
E <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.97353 14.7682C8.54926 15.1218 7.91869 15.0645 7.56513 14.6402L4.23179 10.6402C3.87823 10.2159 3.93555 9.58537 4.35983 9.2318C4.78411 8.87824 5.41467 8.93556 5.76824 9.35984L9.10157 13.3598C9.45513 13.7841 9.39781 14.4147 8.97353 14.7682Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.97353 5.2318C9.39781 5.58537 9.45513 6.21593 9.10157 6.64021L5.76824 10.6402C5.41467 11.0645 4.78411 11.1218 4.35983 10.7682C3.93555 10.4147 3.87823 9.78412 4.23179 9.35984L7.56513 5.35984C7.91869 4.93556 8.54926 4.87824 8.97353 5.2318Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99997 10C5.99997 9.44772 6.44768 9 6.99997 9L15 9C15.5523 9 16 9.44772 16 10C16 10.5523 15.5523 11 15 11L6.99997 11C6.44768 11 5.99997 10.5523 5.99997 10Z" fill="CurrentColor"></path> </g></svg>
@else
SE <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" transform="rotate(0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.90254 12.6459C5.35252 12.5959 4.94718 12.1094 4.99718 11.5594L5.46858 6.37397C5.51858 5.82396 6.005 5.41862 6.55501 5.46862C7.10503 5.51862 7.51037 6.00503 7.46037 6.55505L6.98897 11.7405C6.93896 12.2905 6.45255 12.6959 5.90254 12.6459Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6458 5.90263C12.6958 6.45264 12.2905 6.93906 11.7405 6.98906L6.55502 7.46046C6.005 7.51046 5.51859 7.10512 5.46859 6.5551C5.41858 6.00509 5.82393 5.51868 6.37394 5.46868L11.5594 4.99727C12.1094 4.94727 12.5958 5.35261 12.6458 5.90263Z" fill="CurrentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M7.17156 7.17158C7.56208 6.78106 8.19524 6.78106 8.58577 7.17158L14.2426 12.8284C14.6331 13.219 14.6331 13.8521 14.2426 14.2427C13.8521 14.6332 13.2189 14.6332 12.8284 14.2427L7.17156 8.5858C6.78103 8.19527 6.78103 7.56211 7.17156 7.17158Z" fill="CurrentColor"></path> </g></svg>
@endif
</span>
{{ number_format($lap->wind_speed,1)*4,56 }} K/h

</td>

<td class="px-6 py-4">

@if($lap->sky == 0)
    ☀️ {{ __('ui.clear') }}

@elseif($lap->sky == 1)
    🌤 {{ __('ui.pcloudy') }}

@elseif($lap->sky == 2)
    ⛅ {{ __('ui.mcloudy') }}

@elseif($lap->sky == 3)
    ☁️ {{ __('ui.overcast') }}

@else
    -
@endif

</td>

<td class="px-6 py-4">

@if($lap->weather_declared_wet == 1)
    <div class="align-middle text-center border border-[var(--wet-b)]  bg-[var(--wet-bg)] rounded-md w-16">

    <strong class="text-[var(--wet-text)]">WET</strong>
    </div>
@else
    <div class="align-middle text-center border border-[var(--dry-b)]  bg-[var(--dry-bg)] rounded-md w-16">
    <strong class="text-[var(--dry-text)]">DRY</strong>
    </div>
 @endif
    </div>

</td>
                </tr>
                @endforeach

            </tbody>

        </table>

{{-- popup --}}
<div
    x-show="open"
    x-transition
    @keydown.escape.window="open = false"
    @click.self="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
>

    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl font-mono-timing p-8 w-80 shadow-2xl relative">

        {{-- cerrar --}}
        <button
            @click="open = false"
            class="absolute top-3 right-3 text-[var(--text)]"
        >
            ✕
        </button>

        {{-- VUELTA --}}
        <div class="text-center mb-6">
            <div class="text-4xl font-bold font-mono-timing dark:text-white">
                <span x-text="formatTime(lap?.lap_time)"></span>
            </div>

            <div class="text-sm text-gray-400 mt-1">
                Lap <span x-text="lap?.lap"></span>
            </div>
        </div>

        {{-- GAP --}}
        @php $bestLapJs = $bestLap; @endphp

        <div class="text-center mb-6 text-m font-mono-timing"
            :class="lap && (lap.lap_time - {{ $bestLap }}) <= 0.001
                ? 'text-purple-400'
                : (lap.lap_time - {{ $bestLap }}) < 0.5
                    ? 'text-emerald-400'
                    : 'text-red-400'
            "
        >
            <span x-text="
                lap && (lap.lap_time - {{ $bestLap }}) <= 0.001
                ? 'BEST LAP'
                : '+' + (lap.lap_time - {{ $bestLap }}).toFixed(3) + 's'
            "></span>
        </div>

        {{-- SECTORES --}}
        <div class="grid grid-cols-1 gap-3 text-center">

            <template x-for="(sector, i) in lap?.sectors" :key="i">
                <div class="text-3xl font-mono-timing text-gray-500 dark:text-gray-300">
                    <span x-text="sector.sector_time.toFixed(3)"></span>
                </div>
            </template>

        </div>

    </div>
</div>

{{-- fin popup --}}
</div>
    </div>

</div>

</div>

{{-- ACTIONS --}}
<div class="mt-6 flex gap-4">



    <div class="mt-8">
        @php

        $backRoute = $series
            ? route('series.stints', $series)
            : route('stints.index');

        @endphp

        <x-ui.button variant="exit" href="{{ $backRoute }}">
            <x-heroicon-o-arrow-turn-left-up class="w-4 h-4 mr-2"/>

            {{ $series ? $series->name : __('ui.back') }}

        </x-ui.button>
    </div>

    <div class="mt-8">
        <x-ui.button variant="tec" href="{{ route('stints.analysis', $stint) }}">
        <x-heroicon-o-chart-bar class="w-4 h-4 mr-2"/> {{ __('ui.analysis') }}
        </x-ui.button>

    </div>
</div>





@endsection
