@extends('layouts.app')

@section('title', __('ui.strategy_planning'))
@section('page-title', $series->name.' '.$series->season_year.' - '.$currentRound->track->display_name.' - '.$entry->competitionCar->name.' - '. __('ui.strategic_plan'))

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
                <p class="text-[var(--text-muted)]">{{ __('ui.estimated_laps') }}</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['estimated_laps'] }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">{{ __('ui.tank_capacity') }}</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ $plan['tank_capacity'] }} L
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">{{ __('ui.total_fuel_required') }}</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['base']['fuel'] }} L
                </p>
            </div>

        </div>

    </div>

        {{-- PERSONALIZACIÓN DE ESTRATEGIA --}}
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            {{ __('ui.contingency_plan') }}
        </h2>

        @php
            $config = session('team_strategy_config');
        @endphp

        @if($config && ($config['margin_laps'] ?? 0 || $config['extra_fuel'] ?? 0))
            <div class="bg-[var(--binfo-a)] border border-[var(--binfo-b)] text-[var(--tinfo-a)] p-4 rounded-lg text-sm">
                {{ __('ui.active_contingency') }}
                +{{ $config['margin_laps'] ?? 0 }} vueltas,
                +{{ $config['extra_fuel'] ?? 0 }}L
            </div>
        @endif

        <form method="POST" action="{{ route('team.strategy.update', $series) }}">
            @csrf

            <div class="grid md:grid-cols-2 gap-6 text-sm">

                {{-- Margin laps --}}
                <div>
                    <label class="text-[var(--text-muted)]">{{ __('ui.add_laps') }}</label>
                    <input type="number" name="margin_laps" min="0"
                        class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg p-2 text-white"
                        placeholder="{{ __('ui.example_two') }}">
                </div>

                {{-- Extra fuel --}}
                <div>
                    <label class="text-[var(--text-muted)]">{{ __('ui.add_fuel_liters') }}</label>
                    <input type="number" step="0.1" name="extra_fuel"
                        class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg p-2 text-white"
                        placeholder="{{ __('ui.example_three_five') }}">
                </div>

            </div>

            <div class="flex gap-4 mt-6">

                <button class="bg-[var(--btn-app)] hover:bg-[var(--btn-app-hov)] px-6 py-2 rounded-lg text-white">
                    {{ __('ui.apply_contingency') }}
                </button>
            </form>
                <form method="POST" action="{{ route('team.strategy.reset', $series) }}">
                    @csrf
                    <button class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-lg text-white">
                        {{ __('ui.reset') }}
                    </button>


            </div>

        </form>

    </div>

    {{-- ESTRATEGIA PRINCIPAL --}}
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-8 space-y-6">

        <h2 class="text-xl font-semibold text-[var(--text-title)]">
            {{ __('ui.base_strategy') }}
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
                        'label' => __('ui.base_strategy'),
                        'stints' => [round($equal, 2), round($equal, 2)],
                        'color' => 'text-blue-400'
                    ],
                    'aggressive' => [
                        'label' => __('ui.aggressive'),
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
                <p class="text-[var(--text-muted)]">{{ __('ui.required_stints') }}</p>
                <p class="text-[var(--value-info)] text-lg font-semibold">
                    {{ $plan['base']['stints'] }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">{{ __('ui.estimated_stops') }}</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ max(0, $plan['base']['stints'] - 1) }}
                </p>
            </div>

            <div>
                <p class="text-[var(--text-muted)]">{{ __('ui.fuel_per_stint') }}</p>
                <p class="text-[var(--value-data)] text-lg font-semibold">
                    {{ round($plan['base']['fuel'] / $plan['base']['stints'], 2) }} L
                </p>
            </div>

        </div>

        <div class="mt-6">

            @if($plan['base']['stints'] <= 1)
                <div class="bg-[var(--success)] border border-[var(--border)]
                            text-green-100  px-6 py-4 rounded-xl">
                    ✔ {{ __('ui.single_stint_no_stops') }}
                </div>
            @else
                <div class="bg-[var(--warning)] border border-[var(--border)]
                            text-gray-900 px-6 py-4 rounded-xl">
                    ⚠ {{ __('ui.pit_stops_required', ['count' => max(0, $plan['base']['stints'] - 1)]) }}
                </div>
            @endif

        </div>

    </div>
    @php
    $favorite = session('team_strategy_favorite');
    @endphp
    @if(!empty($patterns))

<div class="mt-6 bg-gray-100 shadow-md border-gray-200 dark:bg-gray-900 border dark:border-gray-700 rounded-xl p-6">

    <h3 class="dark:text-white text-gray-800 font-semibold mb-4">
        {{ __('ui.strategy_comparison') }}
    </h3>

    <table class="w-full text-sm text-left border-collapse">

        {{-- HEADER --}}
        <thead>
            <tr class="text-gray-400 border-b border-gray-400 dark:border-gray-700">

                <th class="py-2">{{ __('ui.concept') }}</th>

                @foreach($patterns as $key => $p)
                    <th class="py-2 text-center">

                        <div class="flex flex-col items-center gap-1">

                            <span class="dark:text-white text-gray-800">
                                {{ $p['label'] }}
                            </span>

                            {{-- BOTÓN FAVORITO --}}
                            <form method="POST" action="{{ route('team.strategy.favorite', $series) }}">
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
                <td class="py-2 text-gray-400">{{ __('ui.initial_fuel') }}</td>

                @foreach($patterns as $key => $p)
                    <td <td class="text-center py-2
                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white font-bold' : 'text-green-700 dark:text-green-400' }}">
                        {{ $p['stints'][0] }} L
                    </td>
                @endforeach
            </tr>

            {{-- Parada 1 --}}
            <tr class="border-b border-gray-400 dark:border-gray-800">
                <td class="py-2 text-gray-400">{{ __('ui.pit_stop_1') }}</td>

                @foreach($patterns as $key => $p)
                    <td <td class="text-center py-2
                    {{ $favorite === $key ? 'dark:bg-indigo-600/20 bg-green-600 text-white font-bold' : 'text-green-700 dark:text-green-400' }}">
                        {{ $p['stints'][1] }} L
                    </td>
                @endforeach
            </tr>

            {{-- Total --}}
            <tr>
                <td class="py-2 text-green-800">{{ __('ui.total') }}</td>

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
            {{ __('ui.scenario_simulation') }}
        </h2>

        <div class="grid md:grid-cols-4 gap-6 text-sm">

            {{-- Base --}}
            <div class="bg-[var(--bbase)] p-4 rounded-xl border border-[var(--border)]s">
                <p class="text-gray-400 text-xs">{{ __('ui.base_strategy') }}</p>
                <p class="text-white font-semibold">
                    {{ $plan['base']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['base']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Conservador --}}
            <div class="bg-[var(--bcons)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-green-400 text-green-900 text-xs">{{ __('ui.conservative') }}</p>
                <p class="dark:text-white text-gray-900 font-semibold">
                    {{ $plan['conservative']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['conservative']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Agresivo --}}
            <div class="bg-[var(--bdang)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-red-400 text-red-900 text-xs">{{ __('ui.aggressive') }}</p>
                <p class="dark:text-white text-gray-900 font-semibold">
                    {{ $plan['aggressive']['fuel'] }} L
                </p>
                <p class="text-gray-500 text-xs">
                    {{ max(0, $plan['aggressive']['stints'] - 1) }} paradas
                </p>
            </div>

            {{-- Safety Car --}}
            <div class="bg-[var(--bsafe)] p-4 rounded-xl border border-[var(--border)]">
                <p class="dark:text-yellow-400 text-yellow-900 text-xs">{{ __('ui.safety_car') }}</p>
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
            <p class="text-gray-600 dark:text-gray-500">{{ __('ui.margin_applied') }}</p>
            <p class="dark:text-white text-gray-900 font-semibold">
                {{ $plan['margin_percent'] }} %
            </p>
        </div>

        <div>
            <p class="dark:text-gray-500 text-gray-600">{{ __('ui.fuel_buffer') }}</p>
            <p class="tdark:text-white text-gray-900 font-semibold">
                {{ $plan['buffer'] }} L
            </p>
        </div>

        <div>
            <p class="text-gray-600 dark:text-gray-500">{{ __('ui.risk_level') }}</p>

            @if($plan['risk'] === 'Bajo')
                <p class="text-green-400 font-semibold">{{ __('ui.low') }}</p>
            @elseif($plan['risk'] === 'Medio')
                <p class="text-yellow-400 font-semibold">{{ __('ui.medium') }}</p>
            @else
                <p class="text-red-400 font-semibold">{{ __('ui.high') }}</p>
            @endif

        </div>

    </div>

</div>


    {{-- RECOMENDACIÓN ESTRATÉGICA --}}
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8">

        <h2 class="text-lg font-semibold text-white mb-4">
            {{ __('ui.technical_recommendation') }}
        </h2>

        <p class="text-gray-400 text-sm leading-relaxed">

            @if($plan['base']['stints'] <= 1)
                {{ __('ui.conservative_recommendation') }}
            @elseif($plan['base']['stints'] == 2)
                {{ __('ui.flexible_recommendation') }}
            @else
                {{ __('ui.complex_recommendation') }}
            @endif

        </p>

    </div>

    @endif
<div class="mt-8">
    <a href="{{ route('team.competitions.show', $series) }}"
       class="inline-flex items-center px-5 py-2.5 bg-gray-700 hover:bg-gray-600
              text-gray-200 rounded text-sm font-medium transition">
        ← {{ __('ui.back_to_competition') }}
    </a>
</div>
</div>

@endsection

