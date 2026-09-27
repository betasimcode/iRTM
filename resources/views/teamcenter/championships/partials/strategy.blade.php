

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    {{-- HEADER --}}
    <section class="rounded-2xl border border-[var(--border)]
                    bg-[var(--card)] p-6">

        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <h1 class="text-2xl font-bold font-microsport
                           text-[var(--text-title)]">
                    STRATEGY
                </h1>

                <p class="mt-2 text-sm text-[var(--text-muted)]">
                    {{ $series->iracingSeries->name }}
                    · {{ $series->season_year }}
                    S{{ $series->season_number }}
                </p>

                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    {{ $displayRound?->track?->display_name ?? 'Track unavailable' }}
                    @if($displayRound?->track?->variant)
                        - {{ $displayRound->track->variant }}
                    @endif
                </p>
            </div>

            <a href="{{ route('teamcenter.championships.show', $series) }}"
               class="px-4 py-2 rounded-lg border border-[var(--border)]
                      text-[var(--text)] hover:bg-[var(--card-hover)]">
                Back to Championship
            </a>

        </div>

    </section>


    {{-- REPORT SCOPE --}}
    <section class="rounded-xl border border-[var(--border)]
                    bg-[var(--card)] p-5">

        <form method="GET"
              action="{{ route('teamcenter.championships.strategy', $series) }}"
              class="flex flex-wrap items-center justify-between gap-4">

            <label class="text-sm text-[var(--text-muted)]">
                Statistical reference
            </label>

            <select name="report_scope"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-[var(--border)]
                           bg-[var(--bg)] text-[var(--text)] px-4 py-2">

                <option value="week"
                    @selected($reportScope === 'week')>
                    Current week
                </option>

                <option value="season"
                    @selected($reportScope === 'season')>
                    Season
                </option>

                <option value="all"
                    @selected($reportScope === 'all')>
                    Historic
                </option>

            </select>

        </form>

    </section>


    @if(session('success'))
        <div class="rounded-lg border border-green-500/30
                    bg-green-500/10 text-green-500 p-4 text-sm">
            {{ session('success') }}
        </div>
    @endif


    {{-- NO DATA --}}
    @if(!$plan)

        <section class="rounded-xl border border-[var(--border)]
                        bg-[var(--card)] p-8 text-center">

            <h2 class="text-lg font-semibold text-[var(--text-title)]">
                Strategy unavailable
            </h2>

            <p class="mt-3 text-sm text-[var(--text-muted)]">
                No hay datos suficientes de ritmo o consumo para
                calcular la estrategia de esta combinación de
                coche y circuito.
            </p>

        </section>

    @else

        {{-- RACE SUMMARY --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)] mb-5">
                Race Summary
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                <div class="rounded-xl border border-[var(--border)]
                            bg-[var(--bg)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Estimated laps
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-info)]">
                        {{ $plan['estimated_laps'] }}
                    </p>
                </div>

                <div class="rounded-xl border border-[var(--border)]
                            bg-[var(--bg)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Tank capacity
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-data)]">
                        {{ number_format($plan['tank_capacity'], 2) }} L
                    </p>
                </div>

                <div class="rounded-xl border border-[var(--border)]
                            bg-[var(--bg)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Base fuel
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-info)]">
                        {{ number_format($plan['base']['fuel'], 2) }} L
                    </p>
                </div>

            </div>

        </section>


        {{-- CONTINGENCY --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)]">
                Contingency Plan
            </h2>

            @if(($config['margin_laps'] ?? 0) > 0 ||
                ($config['extra_fuel'] ?? 0) > 0)

                <div class="mt-4 rounded-lg border border-[var(--border)]
                            bg-[var(--bg)] p-4 text-sm
                            text-[var(--text-muted)]">

                    Active contingency:
                    +{{ $config['margin_laps'] ?? 0 }} laps,
                    +{{ number_format($config['extra_fuel'] ?? 0, 2) }} L

                </div>

            @endif

            <form method="POST"
                  action="{{ route(
                      'teamcenter.championships.strategy.update',
                      $series
                  ) }}"
                  class="mt-5">

                @csrf

                <div class="grid md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm
                                      text-[var(--text-muted)]">
                            Additional laps
                        </label>

                        <input type="number"
                               name="margin_laps"
                               min="0"
                               step="1"
                               value="{{ old(
                                   'margin_laps',
                                   $config['margin_laps'] ?? 0
                               ) }}"
                               required
                               class="mt-2 w-full rounded-lg
                                      border border-[var(--border)]
                                      bg-[var(--bg)] text-[var(--text)]
                                      p-3">

                        @error('margin_laps')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm
                                      text-[var(--text-muted)]">
                            Additional fuel (L)
                        </label>

                        <input type="number"
                               name="extra_fuel"
                               min="0"
                               step="0.1"
                               value="{{ old(
                                   'extra_fuel',
                                   $config['extra_fuel'] ?? 0
                               ) }}"
                               required
                               class="mt-2 w-full rounded-lg
                                      border border-[var(--border)]
                                      bg-[var(--bg)] text-[var(--text)]
                                      p-3">

                        @error('extra_fuel')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div class="flex flex-wrap gap-3 mt-6">

                    <button type="submit"
                            class="px-5 py-2 rounded-lg
                                   bg-[var(--btn-app)]
                                   text-[var(--text-title)]
                                   border border-[var(--border)]">
                        Apply contingency
                    </button>

                </div>

            </form>

            <form method="POST"
                  action="{{ route(
                      'teamcenter.championships.strategy.reset',
                      $series
                  ) }}"
                  class="mt-3">

                @csrf

                <button type="submit"
                        class="px-5 py-2 rounded-lg
                               border border-[var(--border)]
                               text-[var(--text-muted)]">
                    Reset contingency
                </button>

            </form>

        </section>


        {{-- BASE STRATEGY --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)] mb-5">
                Base Strategy
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                <div class="rounded-xl bg-[var(--bg)]
                            border border-[var(--border)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Required stints
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-info)]">
                        {{ $plan['base']['stints'] }}
                    </p>
                </div>

                <div class="rounded-xl bg-[var(--bg)]
                            border border-[var(--border)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Estimated stops
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-data)]">
                        {{ max(0, $plan['base']['stints'] - 1) }}
                    </p>
                </div>

                <div class="rounded-xl bg-[var(--bg)]
                            border border-[var(--border)] p-4">
                    <p class="text-xs uppercase text-[var(--text-muted)]">
                        Fuel per stint (average)
                    </p>
                    <p class="mt-2 text-2xl font-bold text-[var(--value-data)]">
                        {{ number_format(
                            $plan['base']['fuel'] /
                            max(1, $plan['base']['stints']),
                            2
                        ) }} L
                    </p>
                </div>

            </div>

            <div class="mt-5 rounded-xl border p-4
                        {{ $plan['base']['stints'] <= 1
                            ? 'border-green-500/30 bg-green-500/10'
                            : 'border-yellow-500/30 bg-yellow-500/10' }}">

                <p class="text-sm font-semibold">
                    @if($plan['base']['stints'] <= 1)
                        Race fits within one stint. No fuel stop required.
                    @else
                        Estimated fuel stops:
                        {{ max(0, $plan['base']['stints'] - 1) }}
                    @endif
                </p>

            </div>

        </section>


        {{-- SCENARIO COMPARISON --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)] mb-5">
                Scenario Comparison
            </h2>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>
                        <tr class="border-b border-[var(--border)]
                                   text-[var(--text-muted)] text-left">

                            <th class="py-3">Scenario</th>
                            <th class="py-3 text-center">Fuel</th>
                            <th class="py-3 text-center">Stints</th>
                            <th class="py-3 text-center">Stops</th>
                            <th class="py-3 text-center">Favorite</th>

                        </tr>
                    </thead>

                    <tbody>

                        @foreach([
                            'base' => 'Base',
                            'conservative' => 'Conservative',
                            'aggressive' => 'Aggressive',
                            'safety_car' => 'Safety Car',
                        ] as $key => $label)

                            <tr class="border-b border-[var(--border)]
                                       last:border-0">

                                <td class="py-4 font-semibold
                                           text-[var(--text-title)]">
                                    {{ $label }}
                                </td>

                                <td class="py-4 text-center
                                           text-[var(--value-data)]">
                                    {{ number_format(
                                        $plan[$key]['fuel'],
                                        2
                                    ) }} L
                                </td>

                                <td class="py-4 text-center
                                           text-[var(--value-data)]">
                                    {{ $plan[$key]['stints'] }}
                                </td>

                                <td class="py-4 text-center
                                           text-[var(--value-data)]">
                                    {{ max(
                                        0,
                                        $plan[$key]['stints'] - 1
                                    ) }}
                                </td>

                                <td class="py-4 text-center">

                                    <form method="POST"
                                          action="{{ route(
                                              'teamcenter.championships.strategy.favorite',
                                              $series
                                          ) }}">

                                        @csrf

                                        <input type="hidden"
                                               name="pattern"
                                               value="{{ $key }}">

                                        <button type="submit"
                                                aria-label="Select {{ $label }}"
                                                class="px-3 py-1 rounded-lg
                                                       border border-[var(--border)]
                                                       {{ $favorite === $key
                                                           ? 'bg-[var(--btn-app)] text-[var(--text-title)]'
                                                           : 'text-[var(--text-muted)]' }}">
                                            {{ $favorite === $key
                                                ? '★ Selected'
                                                : '☆ Select' }}
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </section>


        {{-- RISK / BUFFER --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)] mb-5">
                Fuel Risk
            </h2>

            <div class="grid md:grid-cols-3 gap-4">

                <div>
                    <p class="text-sm text-[var(--text-muted)]">
                        Applied margin
                    </p>
                    <p class="mt-2 text-xl font-bold text-[var(--value-data)]">
                        {{ $plan['margin_percent'] }} %
                    </p>
                </div>

                <div>
                    <p class="text-sm text-[var(--text-muted)]">
                        Fuel buffer
                    </p>
                    <p class="mt-2 text-xl font-bold text-[var(--value-data)]">
                        {{ $plan['buffer'] }} L
                    </p>
                </div>

                <div>
                    <p class="text-sm text-[var(--text-muted)]">
                        Risk level
                    </p>

                    <p class="mt-2 text-xl font-bold
                        {{ $plan['risk'] === 'Bajo'
                            ? 'text-green-500'
                            : ($plan['risk'] === 'Medio'
                                ? 'text-yellow-500'
                                : 'text-red-500') }}">
                        {{ $plan['risk'] }}
                    </p>
                </div>

            </div>

        </section>


        {{-- RECOMMENDATION --}}
        <section class="rounded-2xl border border-[var(--border)]
                        bg-[var(--card)] p-6">

            <h2 class="text-lg font-semibold text-[var(--text-title)] mb-3">
                Strategic Recommendation
            </h2>

            <p class="text-sm text-[var(--text-muted)] leading-relaxed">

                @if($plan['base']['stints'] <= 1)
                    Estrategia de un solo stint. Controlar el ritmo,
                    el consumo y evitar tráfico innecesario.
                @elseif($plan['base']['stints'] == 2)
                    Estrategia de dos stints. Evaluar la ventana de parada
                    y la posibilidad de undercut u overcut.
                @else
                    Carrera con múltiples stints. Será necesario
                    coordinar las paradas y la gestión del combustible
                    entre los pilotos.
                @endif

            </p>

        </section>

    @endif

</div>
