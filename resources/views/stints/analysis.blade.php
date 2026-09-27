@extends('layouts.app')

@section('title', 'Análisis Stint')
@section('page-title', ' - Análisis')

@section('content')

<br>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    {{-- Vueltas --}}
    <div class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.tlaps') }}</div>
        <div class="text-3xl font-semibold text-indigo-400 mt-2">
            {{ $laps->count() }}
        </div>
    </div>

    {{-- Mejor vuelta --}}
    <div class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.bescomplap') }}</div>
        <div class="text-3xl font-semibold text-green-500 dark:text-green-400 mt-2">
            {{ $laps->min('lap_time') ? lapTime($laps->min('lap_time')) : 'N/A' }}
        </div>
    </div>

    {{-- Ritmo objetivo --}}
    <div class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.reptiming') }}</div>
        <div class="text-3xl font-semibold mt-2">
            @if($pace)
                <span class="text-gray-500 dark:text-gray-400">
                    {{ lapTime($pace) }}
                </span>
            @else
                <span class="text-gray-500">N/A</span>
            @endif
        </div>
    </div>

    {{-- Consistencia --}}
    <div class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.consistency') }}</div>
        <div class="text-3xl font-semibold mt-2">
            @if($consistency)
                <span class="text-emerald-400">
                    ± {{ lapTime($consistency) }}
                </span>
            @else
                <span class="text-gray-500">N/A</span>
            @endif
        </div>
    </div>

    {{-- Stint competitivo --}}
    <div class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400"">{{ __('ui.compwindow') }}</div>
        <div class="text-3xl font-semibold text-gray-500 dark:text-gray-400 mt-2">
            {{ $competitiveEndLap ? __('ui.until') . ' lap ' .$competitiveEndLap : 'N/A' }}
        </div>
    </div>


    @php
function wearColor($v) {
    if ($v === null) return '#6b7280';
    if ($v < 10) return '#22c55e';     // verde
    if ($v < 20) return '#eab308';     // amarillo
    if ($v < 30) return '#f97316';     // naranja
    return '#ef4444';                  // rojo
}

function tempColor($v) {
    if ($v === null) return '#6b7280';
    if ($v < 70) return '#3b82f6';     // frío
    if ($v < 90) return '#22c55e';     // óptimo
    if ($v < 105) return '#eab308';    // caliente
    return '#ef4444';                  // muy caliente
}

$wear = $wear ?? ['fl'=>0,'fr'=>0,'rl'=>0,'rr'=>0];
$temps = $temps ?? ['fl'=>0,'fr'=>0,'rl'=>0,'rr'=>0];

@endphp



<div x-data="{ mode: 'wear' }"
     class="bg-gray-200 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-6 shadow">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.tyremap') }}</h3>

        <div class="flex gap-2 text-sm">
            <button @click="mode='wear'"
                :class="mode==='wear' ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300'"
                class="px-3 py-1 rounded">
                {{ __('ui.wear') }}
            </button>

            <button @click="mode='temp'"
                :class="mode==='temp' ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300'"
                class="px-3 py-1 rounded">
                {{ __('ui.temp') }}
            </button>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="flex items-center justify-between">

        {{-- SVG --}}
        <svg width="240" height="300" viewBox="0 0 240 300">

            {{-- coche --}}
            <rect x="70" y="20" width="100" height="260" rx="12"
                  fill="#374151" opacity="0.2"/>

            {{-- === RUEDAS === --}}

            {{-- FL --}}
            <g>
                <rect x="10" y="40" width="50" height="80" rx="8"
                    :fill="mode==='wear'
                        ? '{{ wearColor($wear['fl']) }}'
                        : '{{ tempColor($temps['fl']) }}'"/>

                <text x="35" y="85"
                    text-anchor="middle"
                    fill="black"
                    font-size="13"
                    font-weight="700"
                    x-text="mode==='wear'
                        ? '{{ $wear['fl'] ?? '-' }}%'
                        : '{{ $temps['fl'] ?? '-' }}°'">
                </text>

                <text x="35" y="135"
                    text-anchor="middle"
                    fill="#703500"
                    font-size="13"
                    x-text="mode==='temp'
                        ? '{{ ($tempDelta['fl'] ?? 0) > 0 ? '+' : '' }}{{ $tempDelta['fl'] ?? 0 }}°'
                        : ''">
                </text>
            </g>

            {{-- FR --}}
           <g>
                <rect x="180" y="40" width="50" height="80" rx="8"
                    :fill="mode==='wear'
                        ? '{{ wearColor($wear['fr']) }}'
                        : '{{ tempColor($temps['fr']) }}'"/>

                <text x="205" y="85"
                    text-anchor="middle"
                    fill="black"
                    font-size="13"
                    font-weight="700"
                    x-text="mode==='wear'
                        ? '{{ $wear['fr'] ?? '-' }}%'
                        : '{{ $temps['fr'] ?? '-' }}°'">
                </text>

                <text x="205" y="135"
                    text-anchor="middle"
                    fill="#703500"
                    font-size="13"
                    x-text="mode==='temp'
                        ? '{{ ($tempDelta['fr'] ?? 0) > 0 ? '+' : '' }}{{ $tempDelta['fr'] ?? 0 }}°'
                        : ''">
                </text>
            </g>

            {{-- RL --}}
            <g>
                <rect x="10" y="180" width="50" height="80" rx="8"
                    :fill="mode==='wear'
                        ? '{{ wearColor($wear['rl']) }}'
                        : '{{ tempColor($temps['rl']) }}'"/>

                <text x="35" y="225"
                    text-anchor="middle"
                    fill="black"
                    font-size="13"
                    font-weight="700"
                    x-text="mode==='wear'
                        ? '{{ $wear['rl'] ?? '-' }}%'
                        : '{{ $temps['rl'] ?? '-' }}°'">
                </text>

                <text x="35" y="275"
                    text-anchor="middle"
                    fill="#703500"
                    font-size="13"
                    x-text="mode==='temp'
                        ? '{{ ($tempDelta['rl'] ?? 0) > 0 ? '+' : '' }}{{ $tempDelta['rl'] ?? 0 }}°'
                        : ''">
                </text>
            </g>

            {{-- RR --}}
            <g>
                <rect x="180" y="180" width="50" height="80" rx="8"
                    :fill="mode==='wear'
                        ? '{{ wearColor($wear['rr']) }}'
                        : '{{ tempColor($temps['rr']) }}'"/>

                <text x="205" y="225"
                    text-anchor="middle"
                    fill="black"
                    font-size="13"
                    font-weight="700"
                    x-text="mode==='wear'
                        ? '{{ $wear['rr'] ?? '-' }}%'
                        : '{{ $temps['rr'] ?? '-' }}°'">
                </text>

                <text x="205" y="275"
                    text-anchor="middle"
                    fill="#703500"
                    font-size="13"
                    x-text="mode==='temp'
                        ? '{{ ($tempDelta['rr'] ?? 0) > 0 ? '+' : '' }}{{ $tempDelta['rr'] ?? 0 }}°'
                        : ''">
                </text>
            </g>

        </svg>

        {{-- LEYENDA --}}
        <div class="text-xs text-gray-400 space-y-2 ml-6">

            <div class="text-gray-300 font-semibold mb-2">
                <span x-text="mode === 'wear' ? '{{ __('ui.wearscale') }}' : '{{ __('ui.tempscale') }}'"></span>
            </div>

            <div><span class="text-green-400">●</span> {{ __('ui.optimal') }}</div>
            <div><span class="text-yellow-400">●</span> {{ __('ui.med') }}</div>
            <div><span class="text-orange-400">●</span> {{ __('ui.high') }}</div>
            <div><span class="text-red-400">●</span> {{ __('ui.warning') }}</div>

        </div>

    </div>

</div>


</div>

{{-- Acción volver --}}

<div class="mt-8">
        <x-ui.button variant="exit" href="{{ route('stints.show', $stint) }}">
        <x-heroicon-o-arrow-turn-left-up class="w-4 h-4 mr-2"/> {{ __('ui.back') }}
        </x-ui.button>
</div>

@endsection
