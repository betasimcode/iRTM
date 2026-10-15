
@php
    $trackMapId = $stint?->track_id;
    $trackMapPath = $trackMapId
        ? public_path("telemetry/tracks/{$trackMapId}.svg")
        : null;
@endphp

<div class="flex flex-1 flex-col gap-2 bg-gray-500 p-4 sm:p-6">
    @if ($trackMapPath && file_exists($trackMapPath))
        <div id="telemetry-track-viewport"
            class="relative w-full overflow-hidden rounded-lg"
            style="height: 480px; min-height: 320px; cursor: grab; touch-action: none;"
            data-track-id="{{ $trackMapId }}"
            aria-label="Mapa del circuito. Usa la rueda para acercar y arrastra para desplazarte.">

            <div id="telemetry-track-canvas"
                class="absolute inset-0"
                style="transform-origin: 0 0;">

                <object id="telemetry-track-map"
                    data="{{ asset("telemetry/tracks/{$trackMapId}.svg") }}"
                    type="image/svg+xml"
                    class="pointer-events-none absolute inset-0 z-0 block h-full w-full"
                    aria-label="Mapa estático del circuito">
                    <p class="p-4 text-sm text-[var(--text-muted)]">
                        No se ha podido cargar el mapa del circuito.
                    </p>
                </object>

                <canvas id="telemetry-lap-overlay"
                    class="pointer-events-none absolute inset-0 z-10 block h-full w-full"
                    aria-label="Trazada GPS de la vuelta"></canvas>
            </div>
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
