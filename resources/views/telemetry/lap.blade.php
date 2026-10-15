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

        },

        layers: {

            sectors: false,

            incidents: false,

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

                <p class="mt-1 font-mono text-lg font-semibold

                          text-[var(--text-title)]">

                    {{ isset($selectedLap['lap_time']) ? gmdate('i:s.', (int) $selectedLap['lap_time']) . str_pad((string) ((int) (($selectedLap['lap_time'] - floor($selectedLap['lap_time'])) * 1000)), 3, '0', STR_PAD_LEFT) : '--:--.---' }}

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



    {{-- TOOLS + TRACK MAP --}}

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">



        {{-- TOOLS --}}

@include('telemetry.partials.aside')



        {{-- TRACK MAP --}}

@include('telemetry.partials.track_map')

    </div>

</div>





@include('telemetry.partials.lap_overlay_reproductor')













@endsection
