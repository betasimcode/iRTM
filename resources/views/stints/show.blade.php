@extends('layouts.app')

@section('title', 'Detalle Stint')
@section('page-title', $stint->user?->name. ' at ' .  $stint->track->display_name. ' - ' . $stint->track->variant. ' with ' . $stint->car_name)

@section('content')

    @php
    $jsonLaps = [];

    $stintDate = $stint->created_at ?? null;

    if ($stintDate && $stint->user_id && $stint->id) {
        $lapsJsonPath = storage_path(
            'app/private/telemetry/' .
            $stintDate->format('Y/m') .
            '/user_' . $stint->user_id .
            '/stint_' . $stint->id .
            '/stint_' . $stint->id . '_laps.json'
        );

        if (is_file($lapsJsonPath)) {
            $decoded = json_decode(
                file_get_contents($lapsJsonPath),
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE
                && isset($decoded['laps'])
                && is_array($decoded['laps'])
            ) {
                $jsonLaps = $decoded['laps'];
            }
        }
    }
@endphp

{{-- INFO CARD --}}
<div class="bg-[var(--bg)] border border-[var(--border)] align-middle items-center rounded-xl p-6 shadow-sm shadow-black/20 py-4 mb-6">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-base">
        <div class="flex mt-0 object-contain items-center">
            <div class="w-32 h-auto pt-2 object-contain align-middle rounded-lg text-center border border-[var(--border)] ">
                <div class=""><img class="m-auto h-20 w-auto" src="{{ asset('storage/'.auth()->user()->iracing_helmet_path) }}"></div>
                <div class="text-[var(--value-data)] bg-[var(--card)] border-t border-[var(--border)] text-base font-medium">
                    {{ $stint->user?->iracing_name ?? '—' }}
                </div>
            </div>

        <div class="py-5 px-4">
            <div class="text-[var(--text)]"></div>
            <div class="text-[var(--value-data)] font-medium mt-1">
                <img class="w-72 h-auto" src="/storage/{{ $stint->user->team->banner_path }}">
            </div>
        </div>

        </div>


        <div class="mx-auto">
               @if($stint->track?->logo)

                                <div x-data="{ open: false }">
                                <img @click="open = true" src="{{ asset('storage/' . themedlogo($stint->track )) }}"
                                    title="{{ $stint->track->variant }}"
                                    alt="{{ $stint->track->name }}"
                                    class="w-52 h-auto object-contain">

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

        <div class="my-auto px-4">
            <div class="text-[var(--value-info)] text-2xl align-middle font-medium uppercase">
                {{ $sessionJson['session']['type']
                    ?? $sessionJson['session']['event_type']
                    ?? $stint->session_phase
                    ?? '—' }}

            </div>
        </div>
        {{-- <div>
            <div class="text-gray-400">Circuito</div>
            <div class="text-white font-medium mt-1">{{ $stint->track }}</div>
        </div> --}}

        {{-- <div>
            <div class="text-gray-400">Coche</div>
            <div class="text-white font-medium mt-1">{{ $stint->car }}</div>
        </div> --}}

        <div class="my-auto">
            <div class="text-[var(--text)] text-lg font-medium mt-1">
                {{ $stint->created_at?->translatedFormat('l d \d\e F \d\e Y - H:i') }}
            </div>

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
        <tbody class="">
            <tr class="transition">

                {{-- Vueltas --}}
                <td class="py-3 text-indigo-400 font-semibold">
                    {{ $lapMetrics['valid_lap_count'] }}
                </td>

                {{-- Duración --}}
                <td class="text-[var(--text)] font-semibold font-mono-timing">
                    {{ round($stint->duration_seconds / 60, 1) }} {{ __('ui.min') }}
                </td>

                {{-- Ritmo medio competitivo: filtro del 107 % calculado en el controlador --}}
                <td class="text-[var(--lap-avg)] font-semibold font-mono-timing">
                    {{ $jsonAvgLap107 !== null ? lapTime($jsonAvgLap107) : '—' }}
                </td>

                {{-- Consumo medio por vuelta --}}
                <td class="text-[var(--fuel)] font-semibold">
                    {{ isset($metrics['avg_fuel']) && is_numeric($metrics['avg_fuel'])
                        ? number_format((float) $metrics['avg_fuel'], 3) . ' L/' . __('ui.lap')
                        : '—' }}
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
                <td class="text-[var(--text)] uppercase">
                    {{ $sessionJson['session']['track_rubber_state'] ?? '—' }}
                </td>



                {{-- Fastest --}}
                <td class="text-[var(--lap-best)] font-semibold font-mono-timing">
                    <strong>
                        {{ $jsonBestLap !== null ? lapTime($jsonBestLap) : '—' }}
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

        <div class="flex items-center gap-2">
            {{-- @if($stint->tyres()->where('snapshot_type', 'end')->exists()) --}}
            @if($stint->setup)
            <x-ui.button variant="option" size="xs" title="{{ __('ui.setup') }}" aria-label="{{ __('ui.setup') }}" class="ui-icon-button" href="{{ route('setups.show', $stint->setup->id) }}">
                <x-ui-icon name="settings" />
            </x-ui.button>
            @else
            <form action="{{ route('stints.repair-setup', $stint) }}" method="POST">

                @csrf
                <x-ui.button
                    variant="option"
                    size="xs"
                    title="{{ __('ui.repair_setup') }}"
                    aria-label="{{ __('ui.repair_setup') }}"
                    class="ui-icon-button">
                    <x-ui-icon name="wrench" />
                </x-ui.button>
            </form>
            @endif
            {{-- 🔗 BOTÓN ANÁLISIS --}}
            <x-ui.button variant="option" size="xs" title="{{ __('ui.analysis') }}" aria-label="{{ __('ui.analysis') }}" class="ui-icon-button" href="{{ route('stints.analysis', [$stint]) }}">
                <x-ui-icon name="chart" />
            </x-ui.button>

                {{-- 🔗 BOTÓN PRINT --}}
            <div x-data="{ open:false }">
            <x-ui.button type="button" variant="option" size="xs" title="{{ __('ui.print_options') }}" aria-label="{{ __('ui.print_options') }}" @click="open=true" class="ui-icon-button">
                <x-ui-icon name="printer" />
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


                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">


                    Print Setup Data

                    </button>

                    <button class="w-full text-left px-3 py-2 rounded-lg text-[var(--btn-text)] bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)]">

                    Print Comparative Setup

                    </button>

                </div>

            </x-action-modal>
            </div>

            <x-ui.button type="button" variant="option" size="xs" x-bind:title="showSectors ? @js(__('ui.hide_sectors')) : @js(__('ui.show_sectors'))" x-bind:aria-label="showSectors ? @js(__('ui.hide_sectors')) : @js(__('ui.show_sectors'))" x-bind:aria-pressed="showSectors ? 'true' : 'false'" @click="showSectors = !showSectors" class="ui-icon-button">
                <x-ui-icon name="columns" />
            </x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto">
@php
    $jsonLaps = collect($lapsJson['laps'] ?? []);

    $maxSectors = $jsonLaps
        ->map(fn ($lap) => count($lap['sectors'] ?? []))
        ->max() ?? 0;
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
                    <th class="px-3 py-3 text-left">{{ __('ui.lap') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.pit') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.time') }}</th>
                    @for ($i = 1; $i <= $maxSectors; $i++)
                    <th class="px-6 py-3 text-left" x-show="showSectors">S{{ $i }}</th>
                    @endfor
                    <th class="px-6 py-3 text-left">{{ __('ui.fuel') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.tracktemp') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.weather') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.wind') }}</th>

                </tr>
            </thead>

            <tbody class="divide-y divide-[var(--div)]">


@php
    $bestLap = collect($jsonLaps)
        ->pluck('lap_time')
        ->filter(fn ($time) => is_numeric($time) && (float) $time > 0)
        ->map(fn ($time) => (float) $time)
        ->min();

    $bestSectors = [];

    for ($i = 1; $i <= $maxSectors; $i++) {
        $sectorKey = 'S' . $i;

        $bestSectors[$i - 1] = $jsonLaps
            ->map(fn ($lap) => $lap['sectors'][$sectorKey] ?? null)
            ->filter(fn ($time) => is_numeric($time) && $time > 0)
            ->min();
    }
@endphp


                @foreach($jsonLaps as $jsonLap)

                @php
                    $displayLap = $jsonLap['candidate_lap']
                        ?? $jsonLap['lap']
                        ?? null;

                    $displayLapTime = $jsonLap['lap_time'] ?? null;
                @endphp


                @if (
                        !is_numeric($displayLapTime)
                        || (float) $displayLapTime <= 0
                    )
                        @continue
                    @endif

                <tr class="hover:bg-[var(--card-hover)] transition">

                   <td class="w-12 text-center align-middle px-3 py-4 font-medium">
                        {{ $displayLap ?? '—' }}
                    </td>

                    <td class="w-12 text-center align-middle px-3 py-4">
                        @if (is_array($jsonLap) && !empty($jsonLap['is_pit_lap']))
                            <span class="inline-flex items-center rounded px-2 py-1
                                        text-xs font-semibold
                                        bg-amber-500/15 text-amber-500">
                                PIT
                            </span>
                        @endif
                    </td>
                   {{-- LAP TIME --}}
                    <td class="px-6 py-4 cursor-pointer "
                        @click='
                            open = true;
                            lap = @json([
                                "lap" => $displayLap,
                                "lap_time" => is_numeric($displayLapTime)
                                    ? (float) $displayLapTime
                                    : null,
                                "sectors" => collect($jsonLap['sectors'] ?? [])
                                    ->map(fn ($time) => [
                                        "sector_time" => is_numeric($time)
                                            ? (float) $time
                                            : null
                                    ])
                                    ->values()
                            ])

                            '
                        >

                    <span class="
                            font-mono-timing
                            hover:text-[var(--text-h)]
                            transition
                            {{ $bestLap !== null
                                    && is_numeric($displayLapTime)
                                    && abs((float) $displayLapTime - (float) $bestLap) < 0.001
                                        ? 'text-[var(--lap-best)] hover:text-[var(--text-h)] font-semibold'
                                        : ''
                                }}
                        ">
                            {{ $displayLapTime !== null ? lapTime($displayLapTime) : '—' }}
                    </span>
                    </td>
                    {{-- SECTORS --}}
                    @for ($i = 1; $i <= $maxSectors; $i++)
                        @php
                            $sectorKey = 'S' . $i;
                            $sectorTime = $jsonLap['sectors'][$sectorKey] ?? null;
                            $bestSector = $bestSectors[$i - 1] ?? null;
                        @endphp

                        <td
                            class="px-6 py-4 font-mono-timing
                                {{ $sectorTime !== null && $bestSector !== null
                                    && abs((float) $sectorTime - (float) $bestSector) < 0.001
                                    ? 'text-purple-700 dark:text-purple-400 font-semibold'
                                    : 'text-gray-500 dark:text-gray-300' }}"
                            x-show="showSectors"
                        >
                            {{ is_numeric($sectorTime)
                                ? number_format((float) $sectorTime, 3)
                                : '—' }}
                        </td>
                    @endfor

                <td class="flex flex-row px-6 py-4">

                    @php


                        $fuelConsumed = isset($jsonLap['fuel_used'])
                            && is_numeric($jsonLap['fuel_used'])
                                ? (float) $jsonLap['fuel_used']
                                : null;

                        $fuelLapStart = isset($jsonLap['fuel_start'])
                            && is_numeric($jsonLap['fuel_start'])
                                ? (float) $jsonLap['fuel_start']
                                : null;
                    @endphp

                    @if($fuelConsumed !== null)

                        {{-- Consumo por vuelta desde el JSON --}}
                        <div class="
                            @if(
                                isset($metrics['avg_fuel'])
                                && is_numeric($metrics['avg_fuel'])
                                && is_numeric($fuelConsumed)
                            )
                                {{ (float) $fuelConsumed < (float) $metrics['avg_fuel']
                                    ? 'text-[var(--success)]'
                                    : ((float) $fuelConsumed > (float) $metrics['avg_fuel']
                                        ? 'text-[var(--danger)]'
                                        : 'text-[var(--value-info)]')
                                }}
                            @else
                                text-[var(--value-info)]
                            @endif
                        ">
                            {{ is_numeric($fuelConsumed)
                                ? number_format((float) $fuelConsumed, 3) . ' L'
                                : '—'
                            }}
                        </div>


                        {{-- Combustible al inicio de la vuelta --}}
                        <div class="flex align-middle items-center px-2">
                           <svg fill="CurrentColor" width="15" height="15" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" transform="matrix(-1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tra#d52020erCarrier" stroke-linecurrentcap="round" stroke-linejoin="round"></g><g id="SVGRepo_i#d52020onCarrier"><path d="M3,2H6C6.28,2 6.53,2.11 6.71,2.29L8.79,4.38L9.59,3.59C10,3.2 10.5,3 11,3H17C17.5,3 18,3.2 18.41,3.59L19.41,4.59C19.8,5 20,5.5 20,6V19A2,2 0 0,1 18,21H8A2,2 0 0,1 6,19V13L6,12V8C6,7.5 6.2,7 6.59,6.59L7.38,5.79L5.59,4H3V2M11,5V7H17V5H11M11.41,11L9.41,9H8V10.41L10,12.41V15.59L8,17.59V19H9.41L11.41,17H14.59L16.59,19H18V17.59L16,15.59V12.41L18,10.41V9H16.59L14.59,11H11.41M12,13H14V15H12V13Z"></path></g></svg>
                         <span class="px-1"> {{ $fuelLapStart !== null ? number_format($fuelLapStart, 1) . ' L' : '—' }} </span>
                        </div>

                    @else
                        —
                    @endif

                </td>

                {{-- LAP TRACK TEMP --}}
                <td class="px-6 py-4">
                    @php


                        $trackTemp = isset($jsonLap['weather']['TrackTemp'])
                            && is_numeric($jsonLap['weather']['TrackTemp'])
                                ? (float) $jsonLap['weather']['TrackTemp']
                                : null;
                    @endphp

                    {{ $trackTemp !== null ? number_format($trackTemp, 2) . ' °C' : '—' }}
                </td>


                {{-- LAP WEATHER  --}}

                @php

                    $weather = $jsonLap['weather'] ?? [];

                    // Precipitación: el IBT entrega un valor entre 0 y 1.
                    $precipitationRaw = $weather['Precipitation'] ?? null;

                    $precipitationPct = is_numeric($precipitationRaw)
                        ? min(100, max(0, (float) $precipitationRaw * 100))
                        : null;

                    // Intensidad de precipitación (iconos provisionales).
                    $rainIcons = match (true) {
                        $precipitationPct === null => [],
                        $precipitationPct == 0     => [],
                        $precipitationPct <= 10    => ['💧'],
                        $precipitationPct <= 30    => ['💧'],
                        $precipitationPct <= 65    => ['💧', '💧'],
                        $precipitationPct <= 75    => ['💧', '💧', '💧'],
                        default                    => ['💧', '💧', '💧', '💧', '·'],
                    };

                    // Estado del cielo. Mapeo provisional pendiente de validar
                    // contra los valores oficiales de Skies del SDK.
                    $skies = isset($weather['Skies']) && is_numeric($weather['Skies'])
                        ? (int) $weather['Skies']
                        : null;

                    $skyIcon = match ($skies) {
                        0       => '☀️',
                        1       => '🌤️',
                        2       => '⛅',
                        3       => '☁️',
                        default => '🌥️',
                    };

                    // WeatherDeclaredWet: bit 0 activado significa WET.
                    $weatherDeclaredWet = is_numeric($weather['WeatherDeclaredWet'] ?? null)
                        && (((int) $weather['WeatherDeclaredWet'] & 1) === 1);
                @endphp

                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">

                        {{-- Precipitación --}}
                        <span
                            class="inline-flex items-center gap-0.5 text-sm"
                            title="{{ $precipitationPct !== null
                                ? 'Precipitación: ' . number_format($precipitationPct, 1) . '%'
                                : 'Precipitación no disponible' }}"
                        >
                            @foreach($rainIcons as $rainIcon)
                                <span>{{ $rainIcon }}</span>
                            @endforeach
                        </span>

                        {{-- Estado del cielo --}}
                        <span
                            class="text-base"
                            title="Estado del cielo (Skies: {{ $skies ?? 'N/D' }})"
                        >
                            {{ $skyIcon }}
                        </span>

                        {{-- Condiciones declaradas húmedas --}}
                        @if($weatherDeclaredWet)
                            <span
                                class="text-xs font-semibold text-sky-400"
                                title="Condiciones declaradas húmedas"
                            >
                                [WET]
                            </span>
                        @endif

                    </div>
                </td>


                    <td class="flex px-6 py-4 w-48">

                        @php

                            $weather = $jsonLap['weather'] ?? [];

                            $windDirRaw = $weather['WindDir'] ?? null;
                            $windVelRaw = $weather['WindVel'] ?? null;

                            $windDir = is_numeric($windDirRaw)
                                ? (rad2deg((float) $windDirRaw) + 180) % 360
                                : null;

                            $windSpeedKmh = is_numeric($windVelRaw)
                                ? (float) $windVelRaw * 3.6
                                : null;

                            $windDirection = $windDir !== null
                                ? match (true) {
                                    $windDir >= 337 || $windDir < 22 => 'S',
                                    $windDir < 67  => 'SW',
                                    $windDir < 112 => 'W',
                                    $windDir < 157 => 'NW',
                                    $windDir < 202 => 'N',
                                    $windDir < 247 => 'NE',
                                    $windDir < 292 => 'E',
                                    default        => 'SE',
                                }
                                : null;
                        @endphp

                        <div class="flex items-center gap-1 text-[var(--value-info)]">
                            @if($windDir !== null)
                                <span class="text-xs">{{ $windDirection }}</span>

                                <svg
                                    width="25"
                                    height="25"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    aria-label="Dirección del viento: {{ $windDirection }}"
                                    title="Dirección del viento: {{ number_format($windDir, 1) }}°"
                                    style="transform: rotate({{ $windDir }}deg)"
                                >
                                    <path
                                        d="M10 16V4 M5.5 8.5L10 4L14.5 8.5"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            @endif

                            <span class="whitespace-nowrap">
                                {{ $windSpeedKmh !== null
                                    ? number_format($windSpeedKmh, 1) . ' km/h'
                                    : '—'
                                }}
                            </span>
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
