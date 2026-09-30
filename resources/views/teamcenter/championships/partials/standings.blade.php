@if(!$hasStandings)

    <div class="py-12 text-center">
        <div class="text-sm text-[var(--text-muted)]">
            No hay clasificación disponible para esta temporada.
        </div>
    </div>

@else

    <div
        x-data="{ standingsTab: 'division' }"
        class="space-y-5"
    >

        {{-- ============================================================
             TABS
        ============================================================= --}}

        <div
            class="flex items-center gap-1
                   border-b border-white/10"
        >

            <button
                type="button"
                @click="standingsTab = 'division'"
                class="relative px-4 py-3
                       text-xs font-semibold uppercase tracking-wider
                       transition"
                :class="
                    standingsTab === 'division'
                        ? 'text-[var(--value-data)]'
                        : 'text-[var(--text-muted)] hover:text-white'
                "
            >
                My Division

                @if($division !== null)
                    <span
                        class="ml-1 text-[10px] opacity-70"
                    >
                        {{ $division }}
                    </span>
                @endif

                <span
                    x-show="standingsTab === 'division'"
                    class="absolute inset-x-0 bottom-[-1px] h-px
                           bg-[var(--value-data)]"
                ></span>
            </button>


            <button
                type="button"
                @click="standingsTab = 'overall'"
                class="relative px-4 py-3
                       text-xs font-semibold uppercase tracking-wider
                       transition"
                :class="
                    standingsTab === 'overall'
                        ? 'text-[var(--value-data)]'
                        : 'text-[var(--text-muted)] hover:text-white'
                "
            >
                Overall

                <span
                    x-show="standingsTab === 'overall'"
                    class="absolute inset-x-0 bottom-[-1px] h-px
                           bg-[var(--value-data)]"
                ></span>
            </button>

        </div>


        {{-- ============================================================
             MY DIVISION
        ============================================================= --}}

        <div
            x-show="standingsTab === 'division'"
            x-transition.opacity
            class="overflow-hidden"
        >

            @if($divisionDrivers->isEmpty())

                <div class="py-10 text-center">
                    <div class="text-sm text-[var(--text-muted)]">
                        No hay clasificación disponible para tu división.
                    </div>
                </div>

            @else

                <div class="mb-3 flex items-center justify-between">

                    <div>
                        <div
                            class="text-xs font-semibold uppercase
                                   tracking-wider
                                   text-[var(--text-muted)]"
                        >
                            Division {{ $division }}
                        </div>

                        <div
                            class="mt-1 text-[11px]
                                   text-[var(--text-muted)]"
                        >
                            {{ $divisionDrivers->count() }} drivers
                        </div>
                    </div>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>
                            <tr
                                class="border-b border-white/10
                                       text-[10px] uppercase
                                       tracking-wider
                                       text-[var(--text-muted)]"
                            >

                                <th class="px-3 py-2 text-left">
                                    Pos
                                </th>

                                <th class="px-3 py-2 text-left">
                                    Nation
                                </th>

                                <th class="px-3 py-2 text-left">
                                    Driver
                                </th>

                                <th class="px-3 py-2 text-right">
                                    Points
                                </th>

                                <th class="px-3 py-2 text-right">
                                    Starts
                                </th>

                                <th class="px-3 py-2 text-right">
                                    Wins
                                </th>

                            </tr>
                        </thead>


                        <tbody class="divide-y divide-white/5">

                            @foreach($divisionDrivers as $driver)

                                <tr
                                    class="transition
                                           hover:bg-white/[0.025]"
                                    @class([
                                        'bg-[var(--value-data)]/[0.06]'
                                            => (string) $driver->cust_id
                                                === (string) $currentCustId,
                                    ])
                                >

                                    <td
                                        class="px-3 py-2.5
                                               font-mono text-xs"
                                    >
                                        {{ $driver->rank }}
                                    </td>


                                    <td class="px-3 py-2.5">

                                        <x-country-flag
                                            :code="$driver->country_code"
                                        />

                                    </td>


                                    <td class="px-3 py-2.5">

                                        <div
                                            @class([
                                                'font-medium',
                                                'text-[var(--value-data)]'
                                                    => (string) $driver->cust_id
                                                        === (string) $currentCustId,
                                            ])
                                        >
                                            {{ $driver->display_name }}
                                        </div>

                                    </td>


                                    <td
                                        class="px-3 py-2.5
                                               text-right font-mono"
                                    >
                                        {{ number_format($driver->points, 1) }}
                                    </td>


                                    <td
                                        class="px-3 py-2.5
                                               text-right font-mono
                                               text-[var(--text-muted)]"
                                    >
                                        {{ $driver->starts }}
                                    </td>


                                    <td
                                        class="px-3 py-2.5
                                               text-right font-mono
                                               text-[var(--text-muted)]"
                                    >
                                        {{ $driver->wins }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>


        {{-- ============================================================
             OVERALL
        ============================================================= --}}

        <div
            x-show="standingsTab === 'overall'"
            x-transition.opacity
            class="overflow-hidden"
        >

            @if($overallDrivers->isEmpty())

                <div class="py-10 text-center">
                    <div class="text-sm text-[var(--text-muted)]">
                        No hay clasificación general disponible.
                    </div>
                </div>

            @else

                <div class="mb-3 flex items-center justify-between">

                    <div>
                        <div
                            class="text-xs font-semibold uppercase
                                   tracking-wider
                                   text-[var(--text-muted)]"
                        >
                            Overall
                        </div>

                        <div
                            class="mt-1 text-[11px]
                                   text-[var(--text-muted)]"
                        >
                            {{ $overallDrivers->count() }} drivers
                        </div>
                    </div>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>
                            <tr
                                class="border-b border-white/10
                                       text-[10px] uppercase
                                       tracking-wider
                                       text-[var(--text-muted)]"
                            >

                                <th class="px-3 py-2 text-left">
                                    Pos
                                </th>

                                <th class="px-3 py-2 text-left">
                                    Nation
                                </th>

                                <th class="px-3 py-2 text-left">
                                    Driver
                                </th>

                                <th class="px-3 py-2 text-right">
                                    Points
                                </th>

                                <th class="px-3 py-2 text-right">
                                    Division
                                </th>

                            </tr>
                        </thead>


                        <tbody class="divide-y divide-white/5">

                            @foreach($overallDrivers as $driver)

                                <tr
                                    class="transition
                                           hover:bg-white/[0.025]"
                                    @class([
                                        'bg-[var(--value-data)]/[0.06]'
                                            => (string) $driver->cust_id
                                                === (string) $currentCustId,
                                    ])
                                >

                                    <td
                                        class="px-3 py-2.5
                                               font-mono text-xs"
                                    >
                                        {{ $driver->rank }}
                                    </td>


                                    <td class="px-3 py-2.5">

                                        <x-country-flag
                                            :code="$driver->country_code"
                                        />

                                    </td>


                                    <td class="px-3 py-2.5">

                                        <div
                                            @class([
                                                'font-medium',
                                                'text-[var(--value-data)]'
                                                    => (string) $driver->cust_id
                                                        === (string) $currentCustId,
                                            ])
                                        >
                                            {{ $driver->display_name }}
                                        </div>

                                    </td>


                                    <td
                                        class="px-3 py-2.5
                                               text-right font-mono"
                                    >
                                        {{ number_format($driver->points, 1) }}
                                    </td>


                                    <td
                                        class="px-3 py-2.5
                                               text-right font-mono
                                               text-[var(--text-muted)]"
                                    >
                                        {{ $driver->division }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>

    </div>

@endif
