@extends('layouts.telemetry')

@section('title', 'Telemetry Lab')

@section('content')

    <div class="max-w-7xl mx-auto px-6 py-8">

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
                            Primeros {{ count($records) }}
                            registros del IBT.
                        </p>

                    </div>


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

                                    @php
                                        $recordColumns = [
                                            'SessionTime',
                                            'Lap',
                                            'LapCompleted',
                                            'LapDistPct',
                                            'Speed',
                                            'RPM',
                                            'Gear',
                                            'Throttle',
                                            'Brake',
                                            'OnPitRoad',
                                            'PlayerCarInPitStall',
                                            'PlayerCarDriverIncidentCount',
                                            'PlayerTrackSurface',
                                            'LFrideHeight',
                                            'RFrideHeight',
                                            'LRrideHeight',
                                            'RRrideHeight',
                                        ];
                                    @endphp

                                    @foreach($recordColumns as $column)

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

                                        @foreach($recordColumns as $column)

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

                                                @if(is_bool($value))

                                                    {{ $value ? 'true' : 'false' }}

                                                @elseif(is_float($value))

                                                    {{ number_format(
                                                        $value,
                                                        6,
                                                        '.',
                                                        ''
                                                    ) }}

                                                @elseif($value === null)

                                                    —

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
