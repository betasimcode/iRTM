@extends('layouts.app')

@section('title', 'Planificación')
@section('page-title', $series->name.' '.$series->season_year.' - Plan Estratégico')

@section('content')

<div class="max-w-6xl mx-auto py-10 space-y-10">



    @if(!$plan)

        <div class="bg-[var(--bg)] border border-[var(--border)] rounded-xl p-8 text-[var(--text)]">
           {{ __('ui.nodata') }}
        </div>

    @else

    {{-- RESUMEN DE CARRERA --}}
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            {{ __('ui.summary') }}
        </h2>

        <div class="grid md:grid-cols-3 gap-6 text-sm">

            <div>
                <p class="text-[var(--text-muted)]">Vueltas estimadas</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['estimated_laps'] }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">Capacidad depósito</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ $plan['tank_capacity'] }} L
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">Combustible total necesario</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['base']['fuel'] }} L
                </p>
            </div>

        </div>

    </div>

        {{-- PERSONALIZACIÓN DE ESTRATEGIA --}}
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            Plan de contingencia
        </h2>

        @php
            $config = session('strategy_config');
        @endphp

        @if($config && ($config['margin_laps'] ?? 0 || $config['extra_fuel'] ?? 0))
            <div class="bg-[var(--binfo-a)] border border-[var(--binfo-b)] text-[var(--tinfo-a)] p-4 rounded-lg text-sm">
                Contingencia activa:
                +{{ $config['margin_laps'] ?? 0 }} vueltas,
                +{{ $config['extra_fuel'] ?? 0 }}L
            </div>
        @endif

        <form method="POST" action="{{ route('strategy.update', $series) }}">
            @csrf

            <div class="grid md:grid-cols-2 gap-6 text-sm">

                {{-- Margin laps --}}
                <div>
                    <label class="text-[var(--text-muted)]">Añadir vueltas</label>
                    <input type="number" name="margin_laps" min="0"
                        class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg p-2 text-white"
                        placeholder="Ej: 2">
                </div>

                {{-- Extra fuel --}}
                <div>
                    <label class="text-[var(--text-muted)]">Añadir combustible (L)</label>
                    <input type="number" step="0.1" name="extra_fuel"
                        class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg p-2 text-white"
                        placeholder="Ej: 3.5">
                </div>

            </div>

            <div class="flex gap-4 mt-6">

                <button class="bg-[var(--btn-app)] hover:bg-[var(--btn-app-hov)] px-6 py-2 rounded-lg text-white">
                    Aplicar contingencia
                </button>
            </form>
                <form method="POST" action="{{ route('strategy.reset', $series) }}">
                    @csrf
                    <button class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-lg text-white">
                        Reset
                    </button>


            </div>

        </form>

    </div>

    {{-- ESTRATEGIA PRINCIPAL --}}
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            Estrategia Base
        </h2>
        <div class="text-sm text-gray-400'">
        @php
            $tank = $plan['tank_capacity'] ?? null;
            $fuel = $plan['base']['fuel'] ?? null;
            $stints = $plan['base']['stints'] ?? 0;

            $patterns = [];

            if((int)$stints >= 2 && $tank && $fuel) {

                $equal = $fuel / 2;

                $aggr1 = min($tank, $fuel);
                $aggr2 = $fuel - $aggr1;

                $safe1 = $equal * 0.97;
                $safe2 = $fuel - $safe1;

                $patterns = [
                    'base' => [
                        'label' => 'Base',
                        'stints' => [round($equal, 2), round($equal, 2)],
                        'color' => 'text-blue-400'
                    ],
                    'aggressive' => [
                        'label' => 'Agresivo',
                        'stints' => [round($aggr1, 2), round($aggr2, 2)],
                        'color' => 'text-green-400'
                    ],
                    'safe' => [
                        'label' => 'Seguro',
                        'stints' => [round($safe1, 2), round($safe2, 2)],
                        'color' => 'text-yellow-400'
                    ]
                ];
            }
        @endphp

        <div class="grid md:grid-cols-3 gap-6 text-sm">

            <div>
                <p class="text-[var(--text-muted)]">Stints necesarios</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['base']['stints'] }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">Paradas estimadas</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ max(0, $plan['base']['stints'] - 1) }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">Combustible por stint</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ round($plan['base']['fuel'] / $plan['base']['stints'], 2) }} L
                </p>
            </div>

        </div>

        <div class="mt-6">

            @if($plan['base']['stints'] <= 1)
                <div class="bg-green-600/20 border border-green-500/30
                            text-green-400 px-6 py-4 rounded-xl">
                    ✔ La carrera cabe en un solo stint. Sin paradas necesarias.
                </div>
            @else
                <div class="dark:bg-yellow-600/20 border dark:border-yellow-500/30
                            dark:text-yellow-400 px-6 py-4 rounded-xl bg-yellow-300">
                    ⚠ Se requerirán {{ max(0, $plan['base']['stints'] - 1) }} parada(s).
                </div>
            @endif

        </div>

    </div>
    @php
    $favorite = session('strategy_favorite');
    @endphp
    @if(!empty($patterns))

<div class="mt-6 bg-gray-100 shadow-md border-gray-200 dark:bg-gray-900 border dark:border-gray-700 rounded-xl p-6">

    <h3 class="dark:text-white text-gray-800 font-semibold mb-4">
        Comparativa de Estrategias
    </h3>

    <table class="w-full text-sm text-left border-collapse">

        {{-- HEADER --}}
        <thead>
            <tr class="text-gray-400 border-b border-gray-400 dark:border-gray-700">

                <th class="py-2">Concepto</th>

                @foreach($patterns as $key => $p)
                    <th class="py-2 text-center">

                        <div class="flex flex-col items-center gap-1">

                            <span class="dark:text-white text-gray-800">
                                {{ $p['label'] }}
                            </span>

                            {{-- BOTÓN FAVORITO --}}
                            <form method="POST" action="{{ route('strategy.favorite', $series) }}">
                                @csrf
                                <input type="hidden" name="pattern" value="{{ $key }}">

                                <button type="submit"
                                    class="text-xs px-2 py-1 rounded
                                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                                    ★
                                </button>
                            </form>

                        </div>

                    </th>
                @endforeach

            </tr>
        </thead>

        {{-- BODY --}}
        <tbody class="dark:text-white text-gray-800">

            {{-- Fuel inicial --}}
            <tr class="border-b border-gray-400 dark:border-gray-800">
                <td class="py-2 text-gray-400">Fuel inicial</td>

                @foreach($patterns as $key => $p)
                    <td <td class="text-center py-2
                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white font-bold' : 'text-green-700 dark:text-green-400' }}">
                        {{ $p['stints'][0] }} L
                    </td>
                @endforeach
            </tr>

            {{-- Parada 1 --}}
            <tr class="border-b border-gray-400 dark:border-gray-800">
                <td class="py-2 text-gray-400">Parada 1</td>

                @foreach($patterns as $key => $p)
                    <td <td class="text-center py-2
                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white font-bold' : 'text-green-700 dark:text-green-400' }}">
                        {{ $p['stints'][1] }} L
                    </td>
                @endforeach
            </tr>

            {{-- Total --}}
            <tr>
                <td class="py-2 text-green-800">Total</td>

                @foreach($patterns as $key => $p)
                    <td <td class="text-center py-2
                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white font-bold' : 'text-green-700 dark:text-green-400' }}">
                        {{ array_sum($p['stints']) }} L
                    </td>
                @endforeach
            </tr>

        </tbody>

    </table>

</div>

@endif


    {{-- SIMULACIÓN DE ESCENARIOS --}}
    <div class="bg-[var(--base)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            Simulación de Escenarios
        </h2>

        <div class="grid md:grid-cols-4 gap-6 text-sm">

            {{-- Base --}}
            <div class="bg-[var(--bbase)] p-4 rounded-xl border border-[var(--border)]s">
                <p class="text-gray-400 text-xs">Base</p>
                <p class="text-white font-semibold">
                    {{ $plan['base']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['base']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Conservador --}}
            <div class="bg-[var(--bcons)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-green-400 text-green-900 text-xs">Conservador</p>
                <p class="dark:text-white text-gray-900 font-semibold">
                    {{ $plan['conservative']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['conservative']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Agresivo --}}
            <div class="bg-[var(--bdang)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-red-400 text-red-900 text-xs">Agresivo</p>
                <p class="dark:text-white text-gray-900 font-semibold">
                    {{ $plan['aggressive']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['aggressive']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Safety Car --}}
            <div class="bg-[var(--bsafe)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-yellow-400 text-yellow-900 text-xs">Safety Car</p>
                <p class="dark:text-white text-gray-900 font-semibold">
                    {{ $plan['safety_car']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['safety_car']['stints'] - 1) }} paradas
                </p>
            </div>

        </div>

    </div>

    <div class="bg-[var(--base)] border border-[var(--border)] rounded-xl p-6 text-sm">

    <div class="grid md:grid-cols-3 gap-6">

        <div>
            <p class="text-gray-600 dark:text-gray-500">Margen aplicado</p>
            <p class="dark:text-white text-gray-900 font-semibold">
                {{ $plan['margin_percent'] }} %
            </p>
        </div>

        <div>
            <p class="dark:text-gray-500 text-gray-600">Buffer combustible</p>
            <p class="tdark:text-white text-gray-900 font-semibold">
                {{ $plan['buffer'] }} L
            </p>
        </div>

        <div>
            <p class="text-gray-600 dark:text-gray-500">Nivel de riesgo</p>

            @if($plan['risk'] === 'Bajo')
                <p class="text-green-400 font-semibold">Bajo</p>
            @elseif($plan['risk'] === 'Medio')
                <p class="text-yellow-400 font-semibold">Medio</p>
            @else
                <p class="text-red-400 font-semibold">Alto</p>
            @endif

        </div>

    </div>

</div>


    {{-- RECOMENDACIÓN ESTRATÉGICA --}}
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8">

        <h2 class="text-lg font-semibold text-white mb-4">
            Recomendación Técnica
        </h2>

        <p class="text-gray-400 text-sm leading-relaxed">

            @if($plan['base']['stints'] <= 1)
                Estrategia conservadora recomendada. Controlar ritmo y evitar tráfico.
            @elseif($plan['base']['stints'] == 2)
                Estrategia flexible. Ventana óptima para undercut u overcut.
            @else
                Carrera estratégica compleja. Gestión crítica de neumáticos y ventanas de parada.
            @endif

        </p>

    </div>

    @endif
<div class="mt-8">
    <a href="{{ route('competitions.show', $series) }}"
       class="inline-flex items-center px-5 py-2.5 bg-gray-700 hover:bg-gray-600
              text-gray-200 rounded text-sm font-medium transition">
        ← Volver
    </a>
</div>
</div>

@endsection

