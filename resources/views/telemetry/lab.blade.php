@extends('layouts.telemetry')

@section('title', 'Telemetry Lab')

@section('content')

    <div class="max-w-8xl mx-auto px-6 py-8">

        {{-- HEADER --}}

        <div class="mb-8">

            <h1
                class="text-2xl font-semibold
                       text-[var(--text)]"
            >
                Telemetry Lab
            </h1>

            <p
                class="mt-1 text-sm
                       text-[var(--text-muted)]"
            >
                IBT binary reader and telemetry inspection.
            </p>

        </div>


        {{-- STINT SELECTOR --}}

        <section
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--card)]
                   p-5"
        >

            <form
                method="GET"
                action="{{ route('telemetry.lab') }}"
                class="flex flex-col md:flex-row
                       md:items-end gap-4"
            >

                <div class="flex-1">

                    <label
                        for="stint"
                        class="block text-xs uppercase
                               tracking-wider
                               text-[var(--text-muted)]
                               mb-2"
                    >
                        IBT / Stint
                    </label>

                    <select
                        id="stint"
                        name="stint"
                        class="w-full rounded-lg
                               border border-[var(--border)]
                               bg-[var(--bg)]
                               px-3 py-2
                               text-sm
                               text-[var(--text)]"
                    >

                        <option value="">
                            Seleccionar stint
                        </option>

                        @foreach($stints as $stint)

                            @php
                                $file = $stint->files
                                    ->firstWhere('type', 'ibt');
                            @endphp

                            <option
                                value="{{ $stint->id }}"
                                @selected(
                                    $selectedStint?->id === $stint->id
                                )
                            >
                                Stint {{ $stint->id }}
                                —
                                {{ $stint->car_name ?? 'Car' }}
                                —
                                {{ $stint->track?->name ?? 'Track' }}
                                —
                                {{ $file?->filename ?? 'IBT' }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <button
                    type="submit"
                    class="rounded-lg
                           bg-[var(--accent)]
                           px-5 py-2
                           text-sm font-semibold
                           text-white"
                >
                    Cargar IBT
                </button>

            </form>


            {{-- RAW RANGE / VARIABLES --}}

            <form
                method="GET"
                action="{{ route('telemetry.lab') }}"
                class="mt-6"
            >

                @if($selectedStint)

                    <input
                        type="hidden"
                        name="stint"
                        value="{{ $selectedStint->id }}"
                    >

                @endif


                <div
                    class="grid
                           grid-cols-1
                           gap-4
                           md:grid-cols-3"
                >

                    <div>

                        <label
                            for="from"
                            class="block
                                   text-xs
                                   uppercase
                                   tracking-wide
                                   text-[var(--text-muted)]"
                        >
                            Desde
                        </label>

                        <input
                            id="from"
                            type="number"
                            name="from"
                            min="0"
                            value="{{ $startRecord }}"
                            class="mt-1
                                   block
                                   w-full
                                   rounded-lg
                                   border
                                   border-[var(--border)]
                                   bg-[var(--bg)]
                                   px-3
                                   py-2
                                   text-sm
                                   text-[var(--text)]"
                        >

                    </div>


                    <div>

                        <label
                            for="to"
                            class="block
                                   text-xs
                                   uppercase
                                   tracking-wide
                                   text-[var(--text-muted)]"
                        >
                            Hasta
                        </label>

                        <input
                            id="to"
                            type="number"
                            name="to"
                            min="0"
                            value="{{ $endRecord }}"
                            class="mt-1
                                   block
                                   w-full
                                   rounded-lg
                                   border
                                   border-[var(--border)]
                                   bg-[var(--bg)]
                                   px-3
                                   py-2
                                   text-sm
                                   text-[var(--text)]"
                        >

                    </div>


                    <div class="flex items-end">

                        <button
                            type="submit"
                            class="w-full
                                   rounded-lg
                                   bg-[var(--text)]
                                   px-4
                                   py-2
                                   text-sm
                                   font-medium
                                   text-[var(--bg)]
                                   transition
                                   hover:opacity-90"
                        >
                            Leer registros
                        </button>

                    </div>

                </div>


                <div class="mt-6">

                    <div
                        class="mb-3
                               text-xs
                               uppercase
                               tracking-[0.15em]
                               text-[var(--text-muted)]"
                    >
                        Variables RAW
                    </div>


                    <div
                        class="grid
                               grid-cols-2
                               gap-2
                               md:grid-cols-3
                               lg:grid-cols-4"
                    >

                        @foreach($variables as $variable)

                            <label
                                class="flex
                                       items-center
                                       gap-2
                                       rounded-lg
                                       border
                                       border-[var(--border)]
                                       bg-[var(--card)]
                                       px-3
                                       py-2"
                            >

                                <input
                                    type="checkbox"
                                    name="variables[]"
                                    value="{{ $variable['name'] }}"
                                    @checked(
                                        in_array(
                                            $variable['name'],
                                            $selectedVariables,
                                            true
                                        )
                                    )
                                >

                                <span
                                    class="truncate
                                           text-xs
                                           text-[var(--text)]"
                                >
                                    {{ $variable['name'] }}
                                </span>

                            </label>

                        @endforeach

                    </div>

                </div>

            </form>

        </section>


        @if($error)

            <section
                class="mt-6
                       rounded-xl
                       border border-red-400/30
                       bg-red-400/5
                       p-5"
            >

                <p
                    class="text-sm
                           text-red-400"
                >
                    {{ $error }}
                </p>

            </section>

        @endif


        @if($selectedStint && $header)

            {{-- STINT INFO --}}

            <section class="mt-6">

                <div
                    class="rounded-xl
                           border border-[var(--border)]
                           bg-[var(--card)]
                           p-5"
                >

                    <div
                        class="flex flex-col
                               lg:flex-row
                               lg:items-start
                               lg:justify-between
                               gap-6"
                    >

                        <div>

                            <p
                                class="text-xs uppercase
                                       tracking-wider
                                       text-[var(--text-muted)]"
                            >
                                Selected IBT
                            </p>

                            <h2
                                class="mt-1 text-xl
                                       font-semibold
                                       text-[var(--text)]"
                            >
                                Stint {{ $selectedStint->id }}
                            </h2>

                            <p
                                class="mt-1 text-sm
                                       text-[var(--text-muted)]"
                            >
                                {{ $ibtFile?->filename }}
                            </p>

                        </div>


                        <div
                            class="grid grid-cols-2
                                   md:grid-cols-4
                                   gap-6"
                        >

                            <div>

                                <p
                                    class="text-xs uppercase
                                           text-[var(--text-muted)]"
                                >
                                    Records
                                </p>

                                <p
                                    class="mt-1 text-lg
                                           font-semibold
                                           text-[var(--value-data)]"
                                >
                                    {{ number_format(
                                        $header['session_record_count']
                                    ) }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs uppercase
                                           text-[var(--text-muted)]"
                                >
                                    Tick rate
                                </p>

                                <p
                                    class="mt-1 text-lg
                                           font-semibold
                                           text-[var(--value-data)]"
                                >
                                    {{ $header['tick_rate'] }} Hz
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs uppercase
                                           text-[var(--text-muted)]"
                                >
                                    Start
                                </p>

                                <p
                                    class="mt-1 text-lg
                                           font-semibold
                                           text-[var(--value-data)]"
                                >
                                    {{ number_format(
                                        $header['session_start_time'],
                                        3
                                    ) }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="text-xs uppercase
                                           text-[var(--text-muted)]"
                                >
                                    End
                                </p>

                                <p
                                    class="mt-1 text-lg
                                           font-semibold
                                           text-[var(--value-data)]"
                                >
                                    {{ number_format(
                                        $header['session_end_time'],
                                        3
                                    ) }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            {{-- HEADER DETAILS --}}

            <section class="mt-6">

                <div
                    class="rounded-xl
                           border border-[var(--border)]
                           bg-[var(--card)]
                           p-5"
                >

                    <h2
                        class="text-lg font-semibold
                               text-[var(--text)]"
                    >
                        IBT Header
                    </h2>

                    <div
                        class="mt-4 grid
                               grid-cols-2
                               md:grid-cols-4
                               lg:grid-cols-6
                               gap-4"
                    >

                        @foreach($header as $key => $value)

                            <div
                                class="rounded-lg
                                       border
                                       border-[var(--border)]
                                       bg-[var(--bg)]
                                       p-3"
                            >

                                <p
                                    class="text-[10px]
                                           uppercase
                                           tracking-wider
                                           text-[var(--text-muted)]"
                                >
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $key
                                    ) }}
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-mono
                                           break-all
                                           text-[var(--text)]"
                                >
                                    {{ is_scalar($value)
                                        ? $value
                                        : json_encode($value) }}
                                </p>

                            </div>

                        @endforeach

                    </div>

                </div>

            </section>


{{-- LAPS --}}

<section class="mt-6">

    <div
        class="rounded-xl
               border border-[var(--border)]
               bg-[var(--card)]
               overflow-hidden"
    >

        <div
            class="px-5 py-4
                   border-b
                   border-[var(--border)]
                   flex
                   items-center
                   justify-between"
        >

            <div>

                <h2
                    class="text-lg
                           font-semibold
                           text-[var(--text)]"
                >
                    LAPS
                </h2>

                <p
                    class="mt-1
                           text-xs
                           text-[var(--text-muted)]"
                >
                    Vueltas cronometradas candidatas extraídas
                    directamente del IBT.
                </p>

            </div>


            <span
                class="rounded-lg
                       border
                       border-[var(--border)]
                       px-3
                       py-1
                       text-xs
                       font-mono
                       text-[var(--text-muted)]"
            >
                {{ count($laps) }} laps
            </span>

        </div>


        @if(empty($laps))

            <div
                class="px-5
                       py-6
                       text-sm
                       text-[var(--text-muted)]"
            >
                No se han detectado vueltas cronometradas
                candidatas.
            </div>

        @else

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead
                        class="bg-[var(--bg)]"
                    >

                        <tr>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Lap
                            </th>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Lap Time
                            </th>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Completed
                            </th>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Complete #
                            </th>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Time #
                            </th>

                            <th
                                class="px-4 py-3
                                       text-left
                                       text-xs uppercase
                                       text-[var(--text-muted)]"
                            >
                                Best Lap
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($laps as $lap)

                            <tr
                                class="border-t
                                       border-[var(--border)]"
                            >

                                <td
                                    class="px-4 py-3
                                           font-mono
                                           font-semibold
                                           text-[var(--text)]"
                                >
                                    {{ $lap['lap'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           font-semibold
                                           text-[var(--lap-best)]"
                                >
                                    @php
                                        $milliseconds =
                                            (int) round(
                                                $lap['lap_time'] * 1000
                                            );

                                        $minutes =
                                            intdiv(
                                                $milliseconds,
                                                60000
                                            );

                                        $seconds =
                                            intdiv(
                                                $milliseconds % 60000,
                                                1000
                                            );

                                        $ms =
                                            $milliseconds % 1000;
                                    @endphp

                                    {{ sprintf(
                                        '%d:%02d.%03d',
                                        $minutes,
                                        $seconds,
                                        $ms
                                    ) }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text)]"
                                >
                                    {{ $lap['completed'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text-muted)]"
                                >
                                    {{ $lap['completion_index'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text-muted)]"
                                >
                                    {{ $lap['record_index'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text-muted)]"
                                >
                                    {{ $lap['best_lap'] }}
                                    /
                                    {{ number_format(
                                        $lap['best_lap_time'],
                                        3,
                                        '.',
                                        ''
                                    ) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</section>


{{-- LAP TIMING DIAGNOSTIC --}}

<section class="mt-6">

    <div
        class="rounded-xl
               border border-[var(--border)]
               bg-[var(--card)]
               overflow-hidden"
    >

        <div
            class="px-5 py-4
                   border-b
                   border-[var(--border)]
                   flex
                   items-center
                   justify-between"
        >

            <div>

                <h2
                    class="text-lg
                           font-semibold
                           text-[var(--text)]"
                >
                    LAP TIMING DIAGNOSTIC
                </h2>

                <p
                    class="mt-1
                           text-xs
                           text-[var(--text-muted)]"
                >
                    Evolución de LapCompleted y LapLastLapTime
                    alrededor de cada cambio de vuelta.
                </p>

            </div>

            <span
                class="rounded-lg
                       border
                       border-[var(--border)]
                       px-3
                       py-1
                       text-xs
                       font-mono
                       text-[var(--text-muted)]"
            >
                {{ count($lapTimingDiagnostics) }} samples
            </span>

        </div>


        @if(empty($lapTimingDiagnostics))

            <div
                class="px-5
                       py-6
                       text-sm
                       text-[var(--text-muted)]"
            >
                No se han detectado cambios de
                LapCompleted.
            </div>

        @else

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead
                        class="bg-[var(--bg)]"
                    >

                        <tr>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Index
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Time
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Lap
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Completed
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Last Lap Time
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Best Lap Time
                            </th>

                            <th class="px-4 py-3 text-left text-xs uppercase text-[var(--text-muted)]">
                                Trigger
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($lapTimingDiagnostics as $diagnostic)

                            <tr
                                class="border-t
                                       border-[var(--border)]"
                            >

                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text-muted)]"
                                >
                                    {{ $diagnostic['index'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono"
                                >

                                    @php
                                        $milliseconds =
                                            (int) round(
                                                $diagnostic['session_time'] * 1000
                                            );

                                        $hours =
                                            intdiv(
                                                $milliseconds,
                                                3600000
                                            );

                                        $minutes =
                                            intdiv(
                                                $milliseconds % 3600000,
                                                60000
                                            );

                                        $seconds =
                                            intdiv(
                                                $milliseconds % 60000,
                                                1000
                                            );

                                        $ms =
                                            $milliseconds % 1000;
                                    @endphp

                                    {{ sprintf(
                                        '%02d:%02d:%02d.%03d',
                                        $hours,
                                        $minutes,
                                        $seconds,
                                        $ms
                                    ) }}

                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           font-semibold"
                                >
                                    {{ $diagnostic['lap'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           font-semibold"
                                >
                                    {{ $diagnostic['lap_completed'] }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono"
                                >
                                    {{ number_format(
                                        $diagnostic['lap_last_lap_time'],
                                        6,
                                        '.',
                                        ''
                                    ) }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono"
                                >
                                    {{ number_format(
                                        $diagnostic['lap_best_lap_time'],
                                        6,
                                        '.',
                                        ''
                                    ) }}
                                </td>


                                <td
                                    class="px-4 py-3
                                           font-mono
                                           text-[var(--text-muted)]"
                                >
                                    LapCompleted →
                                    {{ $diagnostic['trigger_lap'] }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</section>



{{-- LAP TRANSITIONS --}}

@if($selectedStint && !empty($transitions))

    <section
        class="mt-6"
    >

        <div
            class="rounded-xl
                   border
                   border-[var(--border)]
                   bg-[var(--card)]"
        >

            <div
                class="border-b
                       border-[var(--border)]
                       px-5
                       py-4"
            >

                <div
                    class="flex
                           items-center
                           justify-between"
                >

                    <div>

                        <h2
                            class="text-sm
                                   font-semibold
                                   uppercase
                                   tracking-wider
                                   text-[var(--text)]"
                        >
                            Lap transitions
                        </h2>

                        <p
                            class="mt-1
                                   text-xs
                                   text-[var(--text-muted)]"
                        >
                            Cambios relevantes detectados
                            directamente en el IBT.
                        </p>

                    </div>

                    <span
                        class="rounded-md
                               border
                               border-[var(--border)]
                               bg-[var(--bg)]
                               px-2
                               py-1
                               text-xs
                               text-[var(--text-muted)]"
                    >
                        {{ count($transitions) }} transitions
                    </span>

                </div>

            </div>


            <div class="overflow-x-auto">

                <table
                    class="min-w-full
                           text-xs"
                >

                    <thead>

                        <tr
                            class="border-b
                                   border-[var(--border)]
                                   text-left"
                        >

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Index
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Time
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Lap
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Completed
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Lap %
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Pit
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Stall
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Inc
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Surface
                            </th>

                            <th class="px-4 py-3 text-[var(--text-muted)]">
                                Events
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($transitions as $transition)

                            <tr
                                class="border-b
                                       border-[var(--border)]
                                       hover:bg-[var(--bg)]"
                            >

                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--text)]"
                                >
                                    {{ $transition['index'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >

                                    @php

                                        $totalMilliseconds =
                                            (int) round(
                                                ((float)
                                                    $transition['session_time'])
                                                * 1000
                                            );

                                        $hours =
                                            intdiv(
                                                $totalMilliseconds,
                                                3600000
                                            );

                                        $minutes =
                                            intdiv(
                                                $totalMilliseconds
                                                % 3600000,
                                                60000
                                            );

                                        $seconds =
                                            intdiv(
                                                $totalMilliseconds
                                                % 60000,
                                                1000
                                            );

                                        $milliseconds =
                                            $totalMilliseconds
                                            % 1000;

                                    @endphp

                                    {{ sprintf(
                                        '%02d:%02d:%02d.%03d',
                                        $hours,
                                        $minutes,
                                        $seconds,
                                        $milliseconds
                                    ) }}

                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['lap'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['lap_completed'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ number_format(
                                        $transition['lap_dist_pct'],
                                        6,
                                        '.',
                                        ''
                                    ) }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['on_pit_road'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['pit_stall'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['incident_count'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3
                                           font-mono
                                           text-[var(--value-data)]"
                                >
                                    {{ $transition['track_surface'] }}
                                </td>


                                <td
                                    class="px-4
                                           py-3"
                                >

                                    <div
                                        class="flex
                                               flex-wrap
                                               gap-1"
                                    >

                                        @foreach(
                                            $transition['events']
                                            as $event
                                        )

                                            <span
                                                class="rounded
                                                       border
                                                       border-[var(--border)]
                                                       bg-[var(--bg)]
                                                       px-2
                                                       py-1
                                                       font-mono
                                                       text-[var(--text-muted)]"
                                            >
                                                {{ $event }}
                                            </span>

                                        @endforeach

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </section>

@endif


            {{-- RAW RECORDS --}}

            <section class="mt-6">

                <div
                    class="rounded-xl
                           border border-[var(--border)]
                           bg-[var(--card)]
                           overflow-hidden"
                >

                    <div
                        class="px-5 py-4
                               border-b
                               border-[var(--border)]"
                    >

                        <h2
                            class="text-lg font-semibold
                                   text-[var(--text)]"
                        >
                            RAW Records
                        </h2>

                        <p
                            class="mt-1 text-xs
                                   text-[var(--text-muted)]"
                        >
                            Registros
                            {{ $startRecord }}
                            →
                            {{ $endRecord }}
                            ·
                            {{ count($records) }}
                            muestras.
                        </p>

                    </div>


                    @if(count($records))

                        <div class="overflow-x-auto">

                            <table
                                class="w-full text-sm"
                            >

                                <thead
                                    class="bg-[var(--bg)]"
                                >

                                    <tr>

                                        <th
                                            class="px-4 py-3
                                                   text-left
                                                   text-xs uppercase
                                                   text-[var(--text-muted)]"
                                        >
                                            #
                                        </th>

                                        @foreach($selectedVariables as $column)

                                            <th
                                                class="px-4 py-3
                                                       text-left
                                                       text-xs uppercase
                                                       whitespace-nowrap
                                                       text-[var(--text-muted)]"
                                            >
                                                {{ $column }}
                                            </th>

                                        @endforeach

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($records as $record)

                                        <tr
                                            class="border-t
                                                   border-[var(--border)]"
                                        >

                                            <td
                                                class="px-4 py-3
                                                       font-mono
                                                       text-[var(--text-muted)]"
                                            >
                                                {{ $record['index'] }}
                                            </td>

                                            @foreach($selectedVariables as $column)

                                                <td
                                                    class="px-4 py-3
                                                           font-mono
                                                           whitespace-nowrap
                                                           text-[var(--text)]"
                                                >

                                                    @php
                                                        $value =
                                                            $record['values'][$column]
                                                            ?? null;
                                                    @endphp

                                                    @if($value === null)

    —

@elseif($column === 'SessionTime')

    @php
        $totalMilliseconds = (int) round(
            ((float) $value) * 1000
        );

        $hours = intdiv(
            $totalMilliseconds,
            3600000
        );

        $minutes = intdiv(
            $totalMilliseconds % 3600000,
            60000
        );

        $seconds = intdiv(
            $totalMilliseconds % 60000,
            1000
        );

        $milliseconds =
            $totalMilliseconds % 1000;
    @endphp

    <span
        title="{{ number_format(
            (float) $value,
            6,
            '.',
            ''
        ) }} s"
    >
        {{ sprintf(
            '%02d:%02d:%02d.%03d',
            $hours,
            $minutes,
            $seconds,
            $milliseconds
        ) }}
    </span>

@elseif(is_bool($value))

    {{ $value ? 'true' : 'false' }}

@elseif(is_float($value))

    {{ number_format(
        $value,
        6,
        '.',
        ''
    ) }}

@else

    {{ $value }}

@endif

                                                </td>

                                            @endforeach

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div
                            class="p-6
                                   text-sm
                                   text-[var(--text-muted)]"
                        >
                            No hay registros en el rango solicitado.
                        </div>

                    @endif

                </div>

            </section>


            {{-- VARIABLES --}}

            <section class="mt-6">

                <div
                    class="rounded-xl
                           border border-[var(--border)]
                           bg-[var(--card)]
                           overflow-hidden"
                >

                    <div
                        class="px-5 py-4
                               border-b
                               border-[var(--border)]"
                    >

                        <h2
                            class="text-lg font-semibold
                                   text-[var(--text)]"
                        >
                            Variables
                        </h2>

                        <p
                            class="mt-1 text-xs
                                   text-[var(--text-muted)]"
                        >
                            {{ count($variables) }}
                            variables disponibles en el IBT.
                        </p>

                    </div>


                    <div
                        class="max-h-[600px]
                               overflow-auto"
                    >

                        <table class="w-full text-sm">

                            <thead
                                class="sticky top-0
                                       bg-[var(--bg)]"
                            >

                                <tr>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        #
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        Variable
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        Type
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        Offset
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        Unit
                                    </th>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs uppercase
                                               text-[var(--text-muted)]"
                                    >
                                        Description
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($variables as $variable)

                                    <tr
                                        class="border-t
                                               border-[var(--border)]"
                                    >

                                        <td
                                            class="px-4 py-2
                                                   font-mono
                                                   text-[var(--text-muted)]"
                                        >
                                            {{ $variable['index'] }}
                                        </td>

                                        <td
                                            class="px-4 py-2
                                                   font-mono
                                                   font-semibold
                                                   text-[var(--text)]"
                                        >
                                            {{ $variable['name'] }}
                                        </td>

                                        <td
                                            class="px-4 py-2
                                                   text-[var(--text)]"
                                        >
                                            {{ $variable['type_name'] }}
                                        </td>

                                        <td
                                            class="px-4 py-2
                                                   font-mono
                                                   text-[var(--text)]"
                                        >
                                            {{ $variable['offset'] }}
                                        </td>

                                        <td
                                            class="px-4 py-2
                                                   text-[var(--text-muted)]"
                                        >
                                            {{ $variable['unit'] ?: '—' }}
                                        </td>

                                        <td
                                            class="px-4 py-2
                                                   text-[var(--text-muted)]"
                                        >
                                            {{ $variable['description'] ?: '—' }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        @endif

    </div>

@endsection
