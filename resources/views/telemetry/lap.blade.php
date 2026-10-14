@extends('layouts.app')



@section('title', 'Telemetría de vuelta')

@section('page-title', 'Telemetría')



@section('content')

<div

    x-data="{

        tools: {

            gear: true,

            speed: true,

            sectors: true,

        },

        layers: {

            sectors: false,

            incidents: false,

        },

    }"

    class="space-y-4"

>





{{-- SELECTOR DE STINT Y VUELTA --}}

<section

    class="rounded-xl border border-[var(--border)]

           bg-[var(--card)] p-4"

>

    <form

        method="GET"

        action="{{ route('telemetry.lap') }}"

        class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]"

    >

        <div>

            <label

                for="stint"

                class="mb-1 block text-xs text-[var(--text-muted)]"

            >

                Stint

            </label>



            <select

                id="stint"

                name="stint"

                onchange="this.form.submit()"

                class="w-full rounded-lg border border-[var(--border)]

                       bg-[var(--bg)] p-2 text-sm text-[var(--text)]"

            >

                <option value="">Seleccionar stint…</option>



                @foreach ($stints as $item)

                    <option

                        value="{{ $item->id }}"

                        @selected($stint?->id === $item->id)

                    >

                        #{{ $item->id }}

                        — {{ $item->track?->display_name

                            ?? $item->track?->name

                            ?? 'Circuito no disponible' }}

                        — {{ $item->created_at?->format('d/m/Y H:i') }}

                    </option>

                @endforeach

            </select>

        </div>



        <div>

            <label

                for="lap"

                class="mb-1 block text-xs text-[var(--text-muted)]"

            >

                Vuelta

            </label>



            <select

                id="lap"

                name="lap"

                class="w-full rounded-lg border border-[var(--border)]

                       bg-[var(--bg)] p-2 text-sm text-[var(--text)]"

                @disabled(!$stint || count($laps) === 0)

            >

                @forelse ($laps as $lap)

                @php

                    $lapNumber = (int) (

                        $lap['candidate_lap'] ?? $lap['lap'] ?? 0

                    );

                @endphp



                <option

                    value="{{ $lapNumber }}"

                    @selected(

                        (int) ($selectedLapNumber ?? 0) === $lapNumber

                    )

                >

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

        </div>



        <div class="flex items-end">

            <button

                type="submit"

                class="w-full rounded-lg bg-[var(--accent)]

                       px-4 py-2 text-sm font-medium text-white

                       transition hover:opacity-90"

            >

                Cargar vuelta

            </button>

        </div>

    </form>

</section>





    {{-- HEADER --}}

    <header

        class="rounded-xl border border-[var(--border)]

               bg-[var(--card)] p-4 sm:p-5"

    >



        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">

            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Vuelta</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                    {{ $selectedLap['candidate_lap'] ?? $selectedLap['lap'] ?? '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Lap time</p>

                <p class="mt-1 font-mono text-lg font-semibold

                          text-[var(--text-title)]">

                    {{ isset($selectedLap['lap_time']) ? gmdate('i:s.', (int) $selectedLap['lap_time']) . str_pad((string) ((int) (($selectedLap['lap_time'] - floor($selectedLap['lap_time'])) * 1000)), 3, '0', STR_PAD_LEFT) : '--:--.---' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Fecha</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ $stint?->created_at?->format('d/m/Y H:i') ?? '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Aire / pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ isset($sessionData['conditions']['track_air_temp_c']) ? number_format($sessionData['conditions']['track_air_temp_c'], 1) . ' °C' : '—' }} / {{ isset($sessionData['conditions']['track_surface_temp_c']) ? number_format($sessionData['conditions']['track_surface_temp_c'], 1) . ' °C' : '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Estado pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                    {{ $sessionData['session']['track_rubber_state'] ?? '—' }}

                </p>

            </div>

        </div>

    </header>



    {{-- TOOLS + TRACK MAP --}}

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">



        {{-- TOOLS --}}

        <aside

            class="rounded-xl border border-[var(--border)]

                   bg-[var(--card)] p-4"

        >

            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider

                       text-[var(--text-title)]">

                Tools

            </h2>



            <div class="space-y-3">



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

<div

    x-show="tools.sectors"

    x-transition

    class="rounded-lg border border-[var(--border)]

           bg-[var(--bg)] p-3"

>

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



        {{-- TRACK MAP --}}

        <section

            class="flex min-h-[480px] min-w-0 flex-col rounded-xl

                   border border-[var(--border)] bg-[var(--card)]"

        >

            <header

                class="flex flex-wrap items-center justify-between gap-3

                       border-b border-[var(--border)] p-4"

            >

                <div>

                    <h2 class="font-semibold text-[var(--text-title)]">

                        {{ $sessionData['track']['display_name'] ?? $stint?->track?->display_name ?? '—' }} Map

                    </h2>

                    <p class="mt-1 text-xs text-[var(--text-muted)]">

                        El trazado aparecerá al cargar una vuelta válida.

                    </p>

                </div>



                <span

                    class="rounded-md border border-[var(--border)]

                           px-2 py-1 text-xs text-[var(--text-muted)]"

                >

                    Sin datos

                </span>

            </header>







{{-- MAPA ESTÁTICO + CAPA GPS INDEPENDIENTE --}}

<div

    class="flex flex-1 items-center justify-center

           bg-[var(--bg)] p-4 sm:p-6"

>

    @php

        $trackMapId = $stint?->track_id;



        $trackMapPath = $trackMapId

            ? public_path("telemetry/tracks/{$trackMapId}.svg")

            : null;

    @endphp



    @if ($trackMapPath && file_exists($trackMapPath))

        <div

            id="telemetry-track-canvas"

            class="relative w-full max-w-4xl"

            data-track-id="{{ $trackMapId }}"

        >

            {{-- ELEMENTO 1: SVG ESTÁTICO. NO MODIFICAR --}}
<object
    id="telemetry-track-map"
    data="{{ asset("telemetry/tracks/{$trackMapId}.svg") }}"
    type="image/svg+xml"
    class="relative z-0 block w-full"
    style="height: 480px; min-height: 320px;"
    aria-label="Mapa del circuito"
>
    <p class="p-4 text-sm text-[var(--text-muted)]">
        No se ha podido cargar el mapa del circuito.
    </p>
</object>

{{-- ELEMENTO 2: CAPA GPS DINÁMICA E INDEPENDIENTE --}}
<canvas
    id="telemetry-lap-overlay"
    class="pointer-events-none absolute inset-0 z-10 block"
    style="width: 100%; height: 100%;"
    aria-label="Trazada GPS de la vuelta"
></canvas>

        </div>

    @else

        <div class="py-12 text-center">

            <p class="font-medium text-[var(--text-title)]">

                Mapa del circuito no disponible

            </p>



            <p class="mt-2 text-sm text-[var(--text-muted)]">

                No se ha encontrado el SVG correspondiente a este circuito.

            </p>

        </div>

    @endif

</div>



            {{-- REFERENCIA ESTÁTICA DE SECTORES --}}

            <div

                class="border-t border-[var(--border)]

                       bg-[var(--card)] p-4"

            >

                <div

                    class="mb-3 flex flex-wrap

                           items-center justify-between gap-2"

                >

                    <div>

                        <h3

                            class="text-sm font-semibold

                                   text-[var(--text-title)]"

                        >

                            Referencia de sectores

                        </h3>



                        <p class="mt-1 text-xs text-[var(--text-muted)]">

                            Límites obtenidos del IBT

                        </p>

                    </div>



                    @if (!empty($sessionData['sectors']['count']))

                        <span

                            class="rounded-md border

                                   border-[var(--border)]

                                   px-2 py-1 text-xs

                                   text-[var(--text-muted)]"

                        >

                            {{ $sessionData['sectors']['count'] }} sectores

                        </span>

                    @endif

                </div>



                @if (!empty($sessionData['sectors']['segments']))

                    <div

                        class="grid grid-cols-1 gap-2

                               sm:grid-cols-2 xl:grid-cols-3"

                    >

                        @foreach (

                            $sessionData['sectors']['segments']

                            as $sector

                        )

                            <div

                                class="rounded-lg border

                                       border-[var(--border)]

                                       bg-[var(--bg)] p-3"

                            >

                                <div

                                    class="flex items-center

                                           justify-between gap-3"

                                >

                                    <span

                                        class="font-semibold

                                               text-[var(--text-title)]"

                                    >

                                        {{ $sector['label'] }}

                                    </span>



                                    <span

                                        class="text-xs

                                               text-[var(--text-muted)]"

                                    >

                                        {{ number_format(

                                            $sector['start_pct'] * 100,

                                            2,

                                            ',',

                                            '.'

                                        ) }} %

                                    </span>

                                </div>



                                <div

                                    class="mt-2 h-1.5 overflow-hidden

                                           rounded-full bg-[var(--border)]"

                                >

                                    <div

                                        class="h-full rounded-full

                                               bg-[var(--accent)]"

                                        style="width: {{ max(

                                            0,

                                            min(

                                                100,

                                                (

                                                    $sector['end_pct']

                                                    - $sector['start_pct']

                                                ) * 100

                                            )

                                        ) }}%"

                                    ></div>

                                </div>



                                <p

                                    class="mt-2 text-xs

                                           text-[var(--text-muted)]"

                                >

                                    Inicio:

                                    {{ number_format(

                                        $sector['start_pct'] * 100,

                                        2,

                                        ',',

                                        '.'

                                    ) }} %

                                    <span class="mx-1">·</span>

                                    Fin:

                                    {{ number_format(

                                        $sector['end_pct'] * 100,

                                        2,

                                        ',',

                                        '.'

                                    ) }} %

                                </p>

                            </div>

                        @endforeach

                    </div>

                @else

                    <p class="text-sm text-[var(--text-muted)]">

                        Este IBT no contiene límites de sector válidos.

                    </p>

                @endif

            </div>



            {{-- CONTROLES DEL MAPA --}}

            <footer

                class="flex flex-wrap gap-2 border-t border-[var(--border)]

                       p-4"

            >

                <button

                    type="button"

                    @click="layers.sectors = !layers.sectors"

                    :aria-pressed="layers.sectors"

                    class="rounded-lg border px-3 py-2 text-sm transition"

                    :class="layers.sectors

                        ? 'border-[var(--accent)] bg-[var(--accent)] text-white'

                        : 'border-[var(--border)] text-[var(--text)]'"

                >

                    <span x-text="layers.sectors ? '✓ ' : ''"></span>

                    Mostrar sectores

                </button>



                <button

                    type="button"

                    @click="layers.incidents = !layers.incidents"

                    :aria-pressed="layers.incidents"

                    class="rounded-lg border px-3 py-2 text-sm transition"

                    :class="layers.incidents

                        ? 'border-[var(--accent)] bg-[var(--accent)] text-white'

                        : 'border-[var(--border)] text-[var(--text)]'"

                >

                    <span x-text="layers.incidents ? '✓ ' : ''"></span>

                    Mostrar incidentes

                </button>

            </footer>

        </section>

    </div>

</div>









<script>

(() => {

    const initLapOverlay = () => {

        const container = document.getElementById('telemetry-track-canvas');

        const canvas = document.getElementById('telemetry-lap-overlay');

        const mapObject = document.getElementById('telemetry-track-map');



        if (!container || !canvas || !mapObject) return;



        // Evitar inicializaciones duplicadas.

        if (container.dataset.gpsOverlayReady === '1') return;

        container.dataset.gpsOverlayReady = '1';



        // Muestras obtenidas por TelemetryLapController.

        const samples = @js($lapTelemetry ?? []);

        console.log('[GPS diagnóstico] Primera muestra:', samples[0]);
        console.log('[GPS diagnóstico] Posición:', samples[0]?.position);

        const trackId = String(container.dataset.trackId);



        /*
         * CALIBRACIÓN POR track_id.
         *
         * scale: escala uniforme respecto al encaje GPS inicial.
         * rotation_deg: giro en grados.
         * offset_x_pct / offset_y_pct: desplazamiento porcentual
         * respecto al ancho y alto del canvas.
         *
         * Los valores son iniciales, no una calibración validada.
         */
        const defaultCalibration = {

            scale: 1,

            rotation_deg: 0,

            offset_x_pct: 0,

            offset_y_pct: 0,

        };



        const calibrations = {
            "266": {
                scale: 1.01,
                rotation_deg: 1,
                offset_x_pct: -0.2,
                offset_y_pct: 0.2,
            },
        };



        const calibration = {

            ...defaultCalibration,

            ...(calibrations[trackId] ?? {}),

        };



        // Conservar solamente posiciones GPS válidas.

        const points = samples

            .filter(sample =>

                Number.isFinite(Number(sample?.position?.lat)) &&

                Number.isFinite(Number(sample?.position?.lon)) &&

                sample.position.lat !== null &&

                sample.position.lon !== null

            )

            .map(sample => ({

                lat: Number(sample.position.lat),

                lon: Number(sample.position.lon),

                distance: Number(sample.lap_dist_pct),

            }));



        const context = canvas.getContext('2d');



        if (!context || points.length < 2) {

            console.warn(

                '[GPS overlay] No hay suficientes posiciones válidas.',

                { trackId, samples: samples.length, points: points.length }

            );

            return;

        }



        // Proyección local: longitud y latitud expresadas en metros.

        const originLat = points.reduce((sum, p) => sum + p.lat, 0) / points.length;

        const originLon = points.reduce((sum, p) => sum + p.lon, 0) / points.length;

        const radians = Math.PI / 180;

        const cosLat = Math.cos(originLat * radians);



        const projected = points.map(point => ({

            x: (point.lon - originLon) * radians * 6371000 * cosLat,

            y: (point.lat - originLat) * radians * 6371000,

        }));



        const minX = Math.min(...projected.map(p => p.x));

        const maxX = Math.max(...projected.map(p => p.x));

        const minY = Math.min(...projected.map(p => p.y));

        const maxY = Math.max(...projected.map(p => p.y));



        const rangeX = maxX - minX;

        const rangeY = maxY - minY;



        if (rangeX <= 0 || rangeY <= 0) {

            console.warn('[GPS overlay] Extensión GPS no válida.', trackId);

            return;

        }



        const resizeAndDraw = () => {

            const rect = container.getBoundingClientRect();



            if (rect.width <= 0 || rect.height <= 0) return;



            const dpr = window.devicePixelRatio || 1;



            canvas.width = Math.round(rect.width * dpr);

            canvas.height = Math.round(rect.height * dpr);



            context.setTransform(dpr, 0, 0, dpr, 0, 0);

            context.clearRect(0, 0, rect.width, rect.height);



            // Encajar la trazada manteniendo su proporción.

            const padding = Math.min(rect.width, rect.height) * 0.08;

            const availableWidth = Math.max(1, rect.width - padding * 2);

            const availableHeight = Math.max(1, rect.height - padding * 2);



            const fitScale = Math.min(

                availableWidth / rangeX,

                availableHeight / rangeY

            );



            const centerX = (minX + maxX) / 2;

            const centerY = (minY + maxY) / 2;



            const angle = calibration.rotation_deg * radians;

            const cosAngle = Math.cos(angle);

            const sinAngle = Math.sin(angle);



            const drawPoints = projected.map(point => {

                const x = (point.x - centerX) * fitScale;

                const y = -(point.y - centerY) * fitScale;



                const rotatedX = x * cosAngle - y * sinAngle;

                const rotatedY = x * sinAngle + y * cosAngle;



                return {

                    x: rect.width / 2

                        + rotatedX * calibration.scale

                        + rect.width * calibration.offset_x_pct / 100,

                    y: rect.height / 2

                        + rotatedY * calibration.scale

                        + rect.height * calibration.offset_y_pct / 100,

                };

            });



            context.lineWidth = 2.5;

            context.lineJoin = 'round';

            context.lineCap = 'round';

            context.strokeStyle = '#00e5ff';

            context.beginPath();



            drawPoints.forEach((point, index) => {

                if (index === 0) {

                    context.moveTo(point.x, point.y);

                } else {

                    context.lineTo(point.x, point.y);

                }

            });



            context.stroke();



            // Punto inicial y final para facilitar la orientación.

            const first = drawPoints[0];

            const last = drawPoints[drawPoints.length - 1];



            context.fillStyle = '#22c55e';

            context.beginPath();

            context.arc(first.x, first.y, 4, 0, Math.PI * 2);

            context.fill();



            context.fillStyle = '#f97316';

            context.beginPath();

            context.arc(last.x, last.y, 4, 0, Math.PI * 2);

            context.fill();

        };



        const observer = new ResizeObserver(resizeAndDraw);

        observer.observe(container);



        // El objeto SVG puede tardar en terminar de cargar.

        mapObject.addEventListener('load', resizeAndDraw);



        resizeAndDraw();



        console.info('[GPS overlay]', {

            trackId,

            validPoints: points.length,

            calibration,

        });

    };



    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', initLapOverlay, { once: true });

    } else {

        initLapOverlay();

    }

})();

</script>









@endsection
