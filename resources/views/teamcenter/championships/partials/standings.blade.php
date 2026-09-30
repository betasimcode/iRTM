@if(!$hasStandings)

    <div
        class="rounded-xl
               border border-[var(--border)]
               bg-[var(--bg)]
               p-8 text-center"
    >
        <p class="text-sm text-[var(--text-muted)]">
            No hay clasificación disponible para esta temporada.
        </p>
    </div>

@else

@php
    $countryFlags = [
        'US'  => '🇺🇸',
        'ENG' => '🏴',
        'SCO' => '🏴',
        'WAL' => '🏴',
        'NIR' => '🇬🇧',

        'ES' => '🇪🇸',
        'FR' => '🇫🇷',
        'DE' => '🇩🇪',
        'IT' => '🇮🇹',
        'PT' => '🇵🇹',
        'NL' => '🇳🇱',
        'BE' => '🇧🇪',
        'AT' => '🇦🇹',
        'CH' => '🇨🇭',
        'SE' => '🇸🇪',
        'NO' => '🇳🇴',
        'DK' => '🇩🇰',
        'FI' => '🇫🇮',
        'PL' => '🇵🇱',
        'CZ' => '🇨🇿',
        'SK' => '🇸🇰',
        'HU' => '🇭🇺',
        'RO' => '🇷🇴',
        'GR' => '🇬🇷',
        'IE' => '🇮🇪',

        'BR' => '🇧🇷',
        'AR' => '🇦🇷',
        'MX' => '🇲🇽',
        'CL' => '🇨🇱',
        'CO' => '🇨🇴',

        'CA' => '🇨🇦',
        'AU' => '🇦🇺',
        'NZ' => '🇳🇿',
        'JP' => '🇯🇵',
        'KR' => '🇰🇷',
        'CN' => '🇨🇳',
        'SG' => '🇸🇬',
        'IN' => '🇮🇳',
        'ZA' => '🇿🇦',
    ];
@endphp

    {{-- HEADER --}}

    <div
        class="flex flex-col
               md:flex-row
               md:items-center
               md:justify-between
               gap-4
               mb-6"
    >

        {{-- <div>

            <p
                class="text-xs uppercase
                       tracking-wider
                       text-[var(--text-muted)]"
            >
                Season standings
            </p>

            <p
                class="mt-1 text-lg
                       font-semibold
                       text-[var(--text-card-title)]"
            >
                {{ $series->iracingSeries->name }}
            </p>

        </div> --}}


        @if($division !== null)

            {{-- <div
                class="inline-flex
                       items-center
                       rounded-lg
                       border border-[var(--border)]
                       bg-[var(--bg)]
                       px-4 py-2"
            >

                <span
                    class="text-xs uppercase
                           text-[var(--text-muted)]"
                >
                    Your division
                </span>

                <span
                    class="ml-2 font-semibold
                           text-[var(--value-data)]"
                >
                    {{ $division }}
                </span>

            </div> --}}

        @endif

    </div>


    {{-- DIVISION --}}

    @if($division !== null && $divisionDrivers->isNotEmpty())

        <section class="mb-8">

            <div
                class="flex items-center
                       justify-between
                       mb-3"
            >

                <h4
                    class="text-sm font-bold
                           uppercase
                           tracking-wide
                           text-[var(--text-title)]"
                >
                    Division {{ $division }}
                </h4>

                <span
                    class="text-xs
                           text-[var(--text-muted)]"
                >
                    {{ $divisionDrivers->count() }} drivers
                </span>

            </div>


            <div
                class="overflow-hidden
                       rounded-xl
                       border border-[var(--border)]"
            >

                <table class="w-full text-sm">

                    <thead class="bg-[var(--bg)]">

                        <tr>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Pos
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Driver
                            </th>

                            <th class="w-10 px-4 py-3 text-center text-xs uppercase text-[var(--text-muted)]">
                                Nation
                            </th>

                            <th class="px-4 py-3 text-right text-xs uppercase text-[var(--text-muted)]">
                                Points
                            </th>

                            <th class="px-4 py-3 text-right text-xs uppercase text-[var(--text-muted)]">
                                Starts
                            </th>

                            <th class="px-4 py-3 text-right text-xs uppercase text-[var(--text-muted)]">
                                Wins
                            </th>

                        </tr>

                    </thead>


                    <tbody
                        class="divide-y
                               divide-[var(--border)]"
                    >

                        @foreach($divisionDrivers as $driver)

                            <tr
                                class="{{ (int) $driver->cust_id === (int) $currentCustId
                                    ? 'bg-[var(--btn-app)]'
                                    : '' }}"
                            >

                                <td
                                    class="px-4 py-3
                                           font-semibold
                                           text-[var(--text)]"
                                >
                                    {{ $driver->rank }}
                                </td>

                                <td
                                    class="px-4 py-3
                                        text-[var(--text)]"
                                >
                                    <div class="font-bold uppercase">
                                        {{ $driver->display_name }}
                                    </div>
                                </td>

                                <td
                                    class="px-4 py-3
                                        text-center"
                                >
                                    @if($driver->country_code)

                                        <span
                                            class="text-xl leading-none"
                                            title="{{ $driver->country_code }}"
                                        >
                                            {{ $countryFlags[$driver->country_code] ?? '🌐' }}
                                        </span>

                                    @else

                                        <span
                                            class="text-[var(--text-muted)]"
                                        >
                                            —
                                        </span>

                                    @endif
                                </td>

                                <td
                                    class="px-4 py-3
                                           text-right
                                           font-semibold
                                           text-[var(--value-data)]"
                                >
                                    {{ number_format($driver->points, 2) }}
                                </td>

                                <td
                                    class="px-4 py-3
                                           text-right
                                           text-[var(--text-muted)]"
                                >
                                    {{ $driver->starts }}
                                </td>

                                <td
                                    class="px-4 py-3
                                           text-right
                                           text-[var(--text-muted)]"
                                >
                                    {{ $driver->wins }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </section>

    @endif


    {{-- OVERALL --}}

    <section>

        <div
            class="flex items-center
                   justify-between
                   mb-3"
        >

            <h4
                class="text-sm font-semibold
                       uppercase
                       tracking-wide
                       text-[var(--text-title)]"
            >
                Overall
            </h4>

            <span
                class="text-xs
                       text-[var(--text-muted)]"
            >
                {{ $overallDrivers->count() }} drivers
            </span>

        </div>


        <div
            class="overflow-hidden
                   rounded-xl
                   border border-[var(--border)]"
        >

            <table class="w-full text-sm">

                <thead class="bg-[var(--bg)]">

                    <tr>

                        <th
                            class="px-4 py-3
                                   text-left
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Pos
                        </th>

                        <th
                            class="px-4 py-3
                                   text-left
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Driver
                        </th>

                        <th
                            class="w-24 px-4 py-3
                                text-center
                                text-xs uppercase
                                text-[var(--text-muted)]"
                        >
                            Nation
                        </th>

                        <th
                            class="px-4 py-3
                                   text-right
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Points
                        </th>

                        <th
                            class="px-4 py-3
                                   text-right
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Div
                        </th>

                    </tr>

                </thead>


                <tbody
                    class="divide-y
                           divide-[var(--border)]"
                >

                    @foreach($overallDrivers as $driver)

                        <tr
                            class="{{ (int) $driver->cust_id === (int) $currentCustId
                                ? 'bg-[var(--btn-app)]'
                                : '' }}"
                        >

                            <td
                                class="px-4 py-3
                                       font-semibold
                                       text-[var(--text)]"
                            >
                                {{ $driver->rank }}
                            </td>

                            <td
                                class="px-4 py-3
                                    text-[var(--text)]"
                            >
                                <div class="font-bold uppercase">
                                    {{ $driver->display_name }}
                                </div>
                            </td>

                            <td
                                class="px-4 py-3
                                    text-center"
                            >
                                @if($driver->country_code)

                                    <span
                                        class="text-xl leading-none"
                                        title="{{ $driver->country_code }}"
                                    >
                                        {{ $countryFlags[$driver->country_code] ?? '🌐' }}
                                    </span>

                                @else

                                    <span class="text-[var(--text-muted)]">
                                        —
                                    </span>

                                @endif
                            </td>

                            <td
                                class="px-4 py-3
                                       text-right
                                       font-semibold
                                       text-[var(--value-data)]"
                            >
                                {{ number_format($driver->points, 2) }}
                            </td>

                            <td
                                class="px-4 py-3
                                       text-right
                                       text-[var(--text-muted)]"
                            >
                                {{ $driver->division ?? '—' }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </section>

@endif
