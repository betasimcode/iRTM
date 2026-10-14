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
