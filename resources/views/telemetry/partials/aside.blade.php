<aside

            class="rounded-xl border border-[var(--border)]

                   bg-[var(--card)] p-4"

        >

            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider

                       text-[var(--text-title)]">

                Settings

            </h2>

            <div class="space-y-3 mb-3">

@include('telemetry.partials.lap_selector')
{{-- <form

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

    </form> --}}


            </div>

            <div class="space-y-3 mt-2">




{{-- CONFIGURACIÓN VISUAL DE TELEMETRÍA --}}

<div class="space-y-5 mt-3">

    {{-- MAP --}}
    <section class="space-y-2">
        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">
            <span class="text-base">⌖</span> MAP
        </h3>

        <div class="grid grid-cols-3 gap-2">
            <button type="button"
                @click="document.getElementById('telemetry-panel-toggle')?.click()"
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]">
                <span class="block text-lg">◉</span>Instrumentos
            </button>

            <button
                type="button"
                id="telemetry-panel-detach"
                @click="
                    const panel = document.getElementById('telemetry-main-panel');
                    if (panel) {
                        const detached = panel.parentElement?.id === 'telemetry-floating-panel-host';
                        $el.textContent = detached ? '⤢ Acoplar' : '⤢ Desacoplar';
                        $el.title = detached ? 'Acoplar instrumentos' : 'Desacoplar instrumentos';
                        $el.setAttribute('aria-pressed', String(!detached));
                        window.dispatchEvent(new CustomEvent('telemetry-panel-toggle-detach'));
                    }
                "
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]"
            >
                <span class="block text-lg">⤢</span>
                <span>Acoplar</span>
            </button>

            <button type="button"
                @click="document.getElementById('telemetry-map-reset')?.click()"
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]">
                <span class="block text-lg">⌂</span>Encuadrar
            </button>

            <button type="button"
                @click="document.getElementById('telemetry-map-zoom-in')?.click()"
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]">
                <span class="block text-lg">＋</span>Zoom +
            </button>

            <button type="button"
                @click="document.getElementById('telemetry-map-zoom-out')?.click()"
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]">
                <span class="block text-lg">−</span>Zoom −
            </button>

            <button type="button"
                @click="document.getElementById('telemetry-map-follow')?.click()"
                class="rounded-lg border border-[var(--border)] p-2 text-center text-xs hover:border-[var(--accent)]">
                <span class="block text-lg">◎</span>Seguir
            </button>
        </div>
    </section>

    <div class="border-t border-[var(--border)]"></div>

    {{-- ASIDE --}}
    <section class="space-y-2">
        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">
            <span class="text-base">◫</span> ASIDE
        </h3>

        <div class="grid grid-cols-3 gap-2">
            <button type="button"
                @click="tools.gear = !tools.gear"
                :aria-pressed="tools.gear"
                :class="tools.gear ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">⚙</span>Gear
            </button>

            <button type="button"
                @click="tools.speed = !tools.speed"
                :aria-pressed="tools.speed"
                :class="tools.speed ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">◉</span>Speed
            </button>

            <button type="button"
                @click="tools.sectors = !tools.sectors"
                :aria-pressed="tools.sectors"
                :class="tools.sectors ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">⌁</span>Sectors
            </button>

            <button
                type="button"
                @click="
                    tools.controls = !tools.controls;
                    if (tools.controls) tools.minicontrols = false;
                "
                :aria-pressed="tools.controls"
                :class="tools.controls
                    ? 'border-[var(--accent)] bg-[var(--bg)]'
                    : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs"
            >
                <span class="block text-lg">▥</span>
                Controls
                <span
                    class="mt-1 block text-[10px]"
                    {{-- x-text="tools.controls ? 'ON' : 'OFF'" --}}
                ></span>
            </button>

            {{-- <button type="button" disabled title="Pendiente de integrar"
                class="cursor-not-allowed rounded-lg border border-[var(--border)] p-2 text-center text-xs opacity-40">
                <span class="block text-lg">▤</span>Mini controls
            </button> --}}


            <button
                type="button"
                @click="
                    tools.minicontrols = !tools.minicontrols;
                    if (tools.minicontrols) tools.controls = false;
                "
                :aria-pressed="tools.minicontrols"
                class="w-full items-center text-xs justify-between gap-1
                    rounded-lg border p-2 text-center transition"
                :class="tools.minicontrols
                    ? 'border-[var(--accent)] bg-[var(--bg)]'
                    : 'border-[var(--border)] opacity-60'"
            >
                <span class="block text-lg">▤</span>Inputs

            </button>


            <button
                type="button"
                @click="tools.steering = !tools.steering"
                :aria-pressed="tools.steering"
                :class="tools.steering
                    ? 'border-[var(--accent)] bg-[var(--bg)]'
                    : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs"
            >
                <span class="block text-lg">↻</span>
                Steering
                <span
                    class="mt-1 block text-[10px]"
                    {{-- x-text="tools.steering ? 'ON' : 'OFF'" --}}
                ></span>
            </button>
        </div>
    </section>

    <div class="border-t border-[var(--border)]"></div>

    {{-- GRAPHICS --}}
    <section class="space-y-2">
        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">
            <span class="text-base">⌁</span> CHANNELS
        </h3>

        <div class="grid grid-cols-3 gap-2">
            <button type="button"
                @click="graphics.speed = !graphics.speed"
                :aria-pressed="graphics.speed"
                :class="graphics.speed ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">↗</span>Speed
            </button>

            <button type="button"
                @click="graphics.gears = !graphics.gears"
                :aria-pressed="graphics.gears"
                :class="graphics.gears ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">⚙</span>Gears
            </button>

            <button
                type="button"
                @click="graphics.throttle = !graphics.throttle"
                :aria-pressed="graphics.throttle"
                :class="graphics.throttle
                    ? 'border-[var(--accent)] bg-[var(--bg)]'
                    : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs"
            >
                <span class="block text-lg">▥</span>
                Throttle
            </button>

            <button
                type="button"
                @click="graphics.brake = !graphics.brake"
                :aria-pressed="graphics.brake"
                :class="graphics.brake
                    ? 'border-[var(--accent)] bg-[var(--bg)]'
                    : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs"
            >
                <span class="block text-lg">▤</span>
                Brake
            </button>

            <button type="button"
                @click="graphics.steering = !graphics.steering"
                :aria-pressed="graphics.steering"
                :class="graphics.steering ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">↻</span>Steering
            </button>

            <button type="button"
                @click="graphics.sectors = !graphics.sectors"
                :aria-pressed="graphics.sectors"
                :class="graphics.sectors ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">Ⅱ</span>Sectors
            </button>

            <button type="button"
                @click="graphics.setup = !graphics.setup"
                :aria-pressed="graphics.setup"
                :class="graphics.setup ? 'border-[var(--accent)] bg-[var(--bg)]' : 'border-[var(--border)] opacity-60'"
                class="rounded-lg border p-2 text-center text-xs">
                <span class="block text-lg">☷</span>Setup
            </button>
        </div>
    </section>
</div>








    {{-- DATOS REALES DE LA VUELTA --}}

    {{-- <div

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

    </div> --}}











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

        </aside>
