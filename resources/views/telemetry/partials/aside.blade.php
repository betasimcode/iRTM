<aside

            class="rounded-xl border border-[var(--border)]

                   bg-[var(--card)] p-4"

        >

            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider

                       text-[var(--text-title)]">

                lap config

            </h2>

            <div class="space-y-3 mb-3">


<form

        method="GET"

        action="{{ route('telemetry.lap') }}"

        class="gap-3"

    >

        <div class="mb-2">


            <select id="stint" name="stint" onchange="this.form.submit()" class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)] p-2 text-sm text-[var(--text)]">

                <option value="">Seleccionar stint…</option>



                @foreach ($stints as $item)

                    <option value="{{ $item->id }}" @selected($stint?->id === $item->id)>

                        #{{ $item->id }}

                        — {{ $item->track?->display_name

                            ?? $item->track?->name

                            ?? 'Circuito no disponible' }}

                        — {{ $item->created_at?->format('d/m/Y H:i') }}

                    </option>

                @endforeach

            </select>

        </div>

@include('telemetry.partials.lap_selector')

        {{-- <div class="mb-2">


            <select id="lap" name="lap" class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)] p-2 text-sm text-[var(--text)]" @disabled(!$stint || count($laps) === 0)>

                @forelse ($laps as $lap)

                @php

                    $lapNumber = (int) (

                        $lap['candidate_lap'] ?? $lap['lap'] ?? 0

                    );

                @endphp



                <option value="{{ $lapNumber }}" @selected((int) ($selectedLapNumber ?? 0) === $lapNumber)>

                    Vuelta {{ $lapNumber }}

                    — {{ isset($lap['lap_time'])

                        ? gmdate('i:s.', (int) $lap['lap_time'])

                            . str_pad(

                                (string) (int) (

                                    ($lap['lap_time'] - floor($lap['lap_time']))

                                    * 1000

                                ),

                                3,

                                '0',

                                STR_PAD_LEFT

                            )

                        : '--:--.---' }}

                </option>

            @empty

                <option value="">Sin vueltas disponibles</option>

            @endforelse

            </select>

        </div> --}}

        <div class="flex items-end mb-4">

            <button

                type="submit"

                class="w-full rounded-lg bg-[var(--accent)]

                       px-4 py-2 text-sm font-medium text-white

                       transition hover:opacity-90"

            >

                Cargar vuelta

            </button>

        </div>
        <div class="my-6 divide-y-2 divide-solid divide-[var(--border)]"></div>

    </form>


            </div>

            <div class="space-y-3 mt-2">



                {{-- GEAR --}}

                <button

                    type="button"

                    @click="tools.gear = !tools.gear"

                    :aria-pressed="tools.gear"

                    class="flex w-full items-center justify-between gap-3

                           rounded-lg border p-3 text-left transition"

                    :class="tools.gear

                        ? 'border-[var(--accent)] bg-[var(--bg)]'

                        : 'border-[var(--border)] opacity-60'"

                >

                    <span class="flex items-center gap-3">

                        <span class="text-xl">⚙</span>

                        <span class="text-sm font-medium">Gear</span>

                    </span>

                    <span

                        class="text-xs"

                        x-text="tools.gear ? 'ON' : 'OFF'"

                    ></span>

                </button>



                {{-- SPEED --}}

                <button

                    type="button"

                    @click="tools.speed = !tools.speed"

                    :aria-pressed="tools.speed"

                    class="flex w-full items-center justify-between gap-3

                           rounded-lg border p-3 text-left transition"

                    :class="tools.speed

                        ? 'border-[var(--accent)] bg-[var(--bg)]'

                        : 'border-[var(--border)] opacity-60'"

                >

                    <span class="flex items-center gap-3">

                        <span class="text-xl">◉</span>

                        <span class="text-sm font-medium">Speed</span>

                    </span>

                    <span

                        class="text-xs"

                        x-text="tools.speed ? 'ON' : 'OFF'"

                    ></span>

                </button>



                {{-- SECTORS --}}

                <button

                    type="button"

                    @click="tools.sectors = !tools.sectors"

                    :aria-pressed="tools.sectors"

                    class="flex w-full items-center justify-between gap-3

                           rounded-lg border p-3 text-left transition"

                    :class="tools.sectors

                        ? 'border-[var(--accent)] bg-[var(--bg)]'

                        : 'border-[var(--border)] opacity-60'"

                >

                    <span class="flex items-center gap-3">

                        <span class="text-xl">⌁</span>

                        <span class="text-sm font-medium">Sectors</span>

                    </span>

                    <span

                        class="text-xs"

                        x-text="tools.sectors ? 'ON' : 'OFF'"

                    ></span>

                </button>







    {{-- DATOS REALES DE LA VUELTA --}}

    <div

        x-show="tools.speed || tools.gear"

        x-transition

        class="space-y-3 rounded-lg border border-[var(--border)] bg-[var(--bg)] p-3"

    >

        @if (count($lapTelemetry) > 0)



            @if (true)

                <div x-show="tools.speed">

                    <div class="text-xs text-[var(--text-muted)]">

                        Velocidad máxima

                    </div>



                    <div class="font-mono text-2xl font-semibold text-[var(--text-title)]">

                        {{ number_format(max(array_column($lapTelemetry, 'speed_kph')), 1) }}

                        <span class="text-sm">km/h</span>

                    </div>

                </div>

            @endif



            <div x-show="tools.gear">

                <div class="mb-2 text-xs text-[var(--text-muted)]">

                    Marchas utilizadas

                </div>



                <div class="flex flex-wrap gap-2">

                    @foreach (collect($lapTelemetry)->pluck('gear')->unique()->sort() as $gear)

                        <span class="rounded-md border border-[var(--border)] px-2 py-1 font-mono text-sm text-[var(--text-title)]">

                            {{ $gear === 0 ? 'N' : ($gear === -1 ? 'R' : $gear) }}

                        </span>

                    @endforeach

                </div>

            </div>



        @else

            <p class="text-xs text-[var(--text-muted)]">

                No hay muestras de velocidad y marcha disponibles para esta vuelta.

            </p>

        @endif

    </div>











{{-- CONTENIDO SECTORS --}}
<div x-show="tools.sectors" x-transition class="rounded-lg border border-[var(--border)] bg-[var(--bg)] p-3">

    <h3 class="mb-3 text-sm font-semibold text-[var(--text-title)]">

        Sectores

    </h3>



    @forelse (($selectedLap['sectors'] ?? []) as $sector => $time)

        <div class="flex items-center justify-between gap-3 py-1">

            <span class="text-sm text-[var(--text-muted)]">

                {{ $sector }}

            </span>



            <span class="font-mono text-sm text-[var(--text-title)]">

                {{ number_format((float) $time, 3) }} s

            </span>

        </div>

    @empty

        <p class="text-xs text-[var(--text-muted)]">

            No hay sectores disponibles para esta vuelta.

        </p>

    @endforelse

</div>

            </div>



            <p class="mt-5 text-xs leading-relaxed text-[var(--text-muted)]">

                Selecciona las herramientas que quieras mostrar durante

                el análisis de la vuelta.

            </p>

        </aside>
