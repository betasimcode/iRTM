<div
    x-data="{ standingsTab: 'division' }"
    class="bg-[var(--card)]"
>

    {{-- ============================================================
         STANDINGS HEADER
    ============================================================= --}}

    <div
        class="border-b border-[var(--border)]"
    >

        <div
            class="px-6 pb-2"
        >

            <div class="flex items-center justify-between">

                <img
                    class="w-28 h-auto"
                    src="{{ asset('storage/' . $series->iracingSeries->logo_path ) }}"
                    alt=""
                >

                <div>

                    <h2
                        class="text-xl
                               font-semibold
                               uppercase
                               tracking-wide
                               text-[var(--text-title)]"
                    >
                        {{ $series->iracingSeries->name }} {{ $series->season_year }} - Season {{ $series->season_number }}
                    </h2>

                </div>


                @if($canSyncStandings)

                    <button
                        type="button"
                        class="inline-flex
                               items-center
                               gap-2
                               rounded-md
                               border
                               border-[var(--border)]
                               bg-[var(--bg)]
                               px-3
                               py-2
                               text-[10px]
                               font-semibold
                               uppercase
                               tracking-wider
                               text-[var(--text)]
                               transition
                               hover:bg-[var(--hover)]
                               hover:text-[var(--value-data)]"
                    >

                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M20 11a8.1 8.1 0 0 0-14.7-4.7L3 9"/>
                            <path d="M3 4v5h5"/>
                            <path d="M4 13a8.1 8.1 0 0 0 14.7 4.7L21 15"/>
                            <path d="M21 20v-5h-5"/>
                        </svg>

                        Actualizar clasificación

                    </button>

                @endif


                {{-- TABS --}}

                <div
                    class="mt-4 flex items-center gap-1"
                >

                    <button
                        type="button"
                        @click="standingsTab = 'division'"
                        class="relative px-4 py-3
                               text-[11px]
                               font-semibold
                               uppercase
                               tracking-wider
                               transition"
                        :class="
                            standingsTab === 'division'
                                ? 'text-[var(--value-data)]'
                                : 'text-[var(--text-muted)] hover:text-[var(--text)]'
                        "
                    >
                        My Division

                        @if($division !== null)
                            <span
                                class="ml-1 text-[10px] opacity-70"
                            >

                            </span>
                        @endif

                        <span
                            x-show="standingsTab === 'division'"
                            class="absolute inset-x-0 bottom-0 h-px
                                   bg-[var(--value-data)]"
                        ></span>
                    </button>


                    <button
                        type="button"
                        @click="standingsTab = 'overall'"
                        class="relative px-4 py-3
                               text-[11px]
                               font-semibold
                               uppercase
                               tracking-wider
                               transition"
                        :class="
                            standingsTab === 'overall'
                                ? 'text-[var(--value-data)]'
                                : 'text-[var(--text-muted)] hover:text-[var(--text)]'
                        "
                    >
                        Overall

                        <span
                            x-show="standingsTab === 'overall'"
                            class="absolute inset-x-0 bottom-0 h-px
                                   bg-[var(--value-data)]"
                        ></span>
                    </button>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         CONTENT
    ============================================================= --}}

    <div class="p-6">

        {{-- ========================================================
             MY DIVISION
        ========================================================= --}}

        <div
            x-show="standingsTab === 'division'"
            x-transition.opacity
        >

            @if($divisionDrivers->isEmpty())

                <div class="py-10 text-center">

                    <p
                        class="text-sm
                               text-[var(--text-muted)]"
                    >
                        No hay clasificación disponible para tu división.
                    </p>

                </div>

            @else

                <div class="mb-4 w-32 m-auto text-center p-1 bg-[var(--bg)] rounded-md border border-[var(--border)]">

                    <p
                        class="text-sm
                               font-semibold
                               uppercase
                               tracking-wider
                               text-[var(--text)]"
                    >
                        Division {{ $division }}
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="border-b border-[var(--border)]
                                       text-[10px]
                                       uppercase
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


                        <tbody class="divide-y divide-[var(--border)] border border-[var(--border)] bg-[var(--bg)]">

                            @foreach($divisionDrivers as $driver)

                                @if((int) $driver->cust_id === (int) $currentCustId)

                                <tr
                                    class="transition bg-[var(--card)] border border-[var(--card)]"
                                >

                            @else

                                <tr
                                    class="transition hover:bg-[var(--hover)] hover:text-[var(--text-muted)]"
                                >

                            @endif

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
                                            class="font-medium uppercase hover:text-[var(--text-muted)]"
                                                {{ (int) $driver->cust_id === (int) $currentCustId
                                                    ? 'text-[var(--value-data)]'
                                                    : 'text-[var(--text)]'}}"
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


        {{-- ========================================================
             OVERALL
        ========================================================= --}}

        <div
            x-show="standingsTab === 'overall'"
            x-transition.opacity
        >

            @if($overallDrivers->isEmpty())

                <div class="py-10 text-center">

                    <p
                        class="text-sm
                               text-[var(--text-muted)]"
                    >
                        No hay clasificación general disponible.
                    </p>

                </div>

            @else

                <div class="mb-4 w-32 m-auto text-center p-1 bg-[var(--bg)] rounded-md border border-[var(--border)]">

                    <p
                        class="text-xs
                               font-semibold
                               uppercase
                               tracking-wider
                               text-[var(--text-muted)]"
                    >
                        Overall
                    </p>

                    <p
                        class="mt-1 text-[11px]
                               text-[var(--text-muted)]"
                    >
                        {{ $overallDrivers->count() }} drivers
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="border-b border-[var(--border)]
                                       text-[10px]
                                       uppercase
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


                        <tbody class="divide-y divide-[var(--border)] border border-[var(--border)] bg-[var(--bg)]">

                            @foreach($overallDrivers as $driver)

                                <tr
                                    class="transition
                                           hover:bg-white/[0.025]"
                                    @class([
                                        'bg-[var(--value-data)]/[0.06]'
                                            => (int) $driver->cust_id
                                                === (int) $currentCustId,
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
                                        {{ $driver->division +1 }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>

    </div>

</div>
