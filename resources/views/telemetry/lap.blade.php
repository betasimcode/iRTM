@extends('layouts.telemetry')



@section('title', 'Telemetry')

@section('page-title', 'Telemetry from stint #'  .  $stint->id)



@section('content')

<div

    x-data="{

        tools: {

            gear: true,
            speed: true,
            sectors: true,
            controls: true,
            steering: true,
            minicontrols: false,

        },

        layers: {

            sectors: false,

            incidents: false,

        },

        graphics: {
            speed: false,
            gears: false,
            inputs: false,
            steering: false,
            sectors: false,
            setup: false,
        },

    }"

    class="space-y-3"

>


{{-- SELECTOR DE STINT Y VUELTA --}}


    {{-- HEADER --}}

    <header

        class="rounded-xl border border-[var(--border)]

               bg-[var(--card)] p-3 my-0"

    >



        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">

            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Vuelta</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                    {{ $selectedLap['candidate_lap'] ?? $selectedLap['lap'] ?? '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Lap time</p>

                <p class="flex mt-1 font-mono-timing text-lg font-semibold

                          text-[var(--text-title)]">

                    {{ isset($selectedLap['lap_time']) ? gmdate('i:s.', (int) $selectedLap['lap_time']) . str_pad((string) ((int) (($selectedLap['lap_time'] - floor($selectedLap['lap_time'])) * 1000)), 3, '0', STR_PAD_LEFT) : '--:--.---' }}

                    <button
                        type="button"
                        id="lap-color-trigger"
                        aria-label="Cambiar color de la trazada"
                        title="Color de la trazada"
                        class="flex items-center justify-center rounded-lg"
                    >
                        <span
                            id="lap-color-preview"
                            class="ml-3 h-4 w-4 rounded-full border border-white/20"
                            style="background-color: #00E5FF"
                        ></span>
                    </button>

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Fecha</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ $stint?->created_at?->format('d/m/Y H:i') ?? '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Aire / pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ isset($sessionData['conditions']['track_air_temp_c']) ? number_format($sessionData['conditions']['track_air_temp_c'], 1) . ' °C' : '—' }} / {{ isset($sessionData['conditions']['track_surface_temp_c']) ? number_format($sessionData['conditions']['track_surface_temp_c'], 1) . ' °C' : '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Estado pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                    {{ $sessionData['session']['track_rubber_state'] ?? '—' }}

                </p>

            </div>

        </div>

    </header>



{{-- COMPARATIVA DE VUELTAS --}}
<section class="rounded-xl border border-[var(--border)]
                bg-[var(--card)] p-4">
    <form
        method="GET"
        action="{{ route('telemetry.lap') }}"
        class="flex flex-wrap items-end gap-4"
    >
        <input type="hidden" name="stint" value="{{ $stint->id }}">
        <input
            type="hidden"
            name="lap"
            value="{{ $selectedLapNumber }}"
        >

        <div class="min-w-56 flex-1">
            <label
                for="reference_lap"
                class="mb-2 block text-xs uppercase tracking-wider
                       text-[var(--text-muted)]"
            >
                Vuelta de referencia
            </label>

            <select
                id="reference_lap"
                name="reference_lap"
                class="w-full rounded-lg border border-[var(--border)]
                       bg-[var(--bg)] p-2 text-sm text-[var(--text)]"
            >
                <option value="">Sin referencia</option>

                @foreach ($referenceLapOptions as $lapOption)
                    @php
                        $optionNumber = (int) (
                            $lapOption['candidate_lap']
                            ?? $lapOption['lap']
                            ?? 0
                        );
                    @endphp

                    <option
                        value="{{ $optionNumber }}"
                        @selected($referenceLapNumber === $optionNumber)
                    >
                        Vuelta {{ $optionNumber }}
                        — {{ number_format((float) $lapOption['lap_time'], 3) }} s
                    </option>
                @endforeach
            </select>
        </div>

        <button
            type="submit"
            class="rounded-lg border border-[var(--border)]
                   px-4 py-2 text-sm font-semibold
                   text-[var(--text-title)] hover:border-[var(--accent)]"
        >
            Comparar
        </button>

        <div class="min-w-40">
            <p class="text-xs uppercase tracking-wider
                      text-[var(--text-muted)]">
                Diferencia en este punto
            </p>

            <p
                id="telemetry-lap-delta"
                class="mt-1 font-mono-timing text-lg font-semibold
                       text-[var(--text-title)]"
            >
                —
            </p>
        </div>
    </form>

    @if ($referenceLapNumber !== null)
        <p class="mt-3 text-xs text-[var(--text-muted)]">
            Referencia: vuelta {{ $referenceLapNumber }}.
            Diferencia positiva: la vuelta principal pierde tiempo.
            Diferencia negativa: la vuelta principal gana tiempo.
        </p>
    @endif
</section>

{{-- TOOLS + TRACK MAP + GRAPHICS --}}
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">

        {{-- TOOLS --}}
        @include('telemetry.partials.aside')

        {{-- MAPA Y GRÁFICAS --}}
        <div
            class="grid min-w-0 grid-cols-1 gap-4"
            :class="Object.values(graphics).some(Boolean)
                ? 'xl:grid-cols-[minmax(0,1fr)_minmax(460px,0.65fr)]'
                : 'grid-cols-1'"
        >
            {{-- TRACK MAP --}}
            @include('telemetry.partials.track_map')

            {{-- GRAPHICS --}}
            @include('telemetry.partials.graphics_panel')
        </div>
    </div>

</div>





@include('telemetry.partials.lap_overlay_reproductor')













@endsection
