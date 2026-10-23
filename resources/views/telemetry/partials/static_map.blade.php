
@php
    $trackMapId = $stint?->track_id;

    $trackMapPath = $trackMapId
        ? resource_path("views/telemetry/tracks/{$trackMapId}.svg")
        : null;
@endphp

<div class="flex flex-1 flex-col gap-2 bg-[var(--back)] p-4 sm:p-6">
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
                    data="{{ route('telemetry.tracks.show', ['trackMapId' => $trackMapId]) }}"
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

            <div
                id="telemetry-floating-panel-host"
                class="pointer-events-none absolute inset-0 z-20 overflow-hidden"
                aria-label="Área de instrumentos desacoplados"
            >
            </div>

            {{-- Animación esquemática de la suspensión, sincronizada con el cursor de telemetría. --}}
            <section
                id="telemetry-suspension-motion"
                class="pointer-events-none absolute right-3 top-3 z-30 hidden w-[208px] rounded-xl border border-white/15 bg-slate-950/90 p-2.5 text-slate-100 shadow-xl backdrop-blur-sm"
                aria-label="Animación dinámica de la suspensión"
            >
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Suspensión · Front view</span>
                    <span id="telemetry-suspension-motion-status" class="text-[9px] text-slate-400">LIVE</span>
                </div>

                <svg viewBox="0 0 180 91" class="block w-full" role="img" aria-label="Vista frontal esquemática del vehículo y movimiento de las ruedas">
                    <line x1="8" y1="82" x2="172" y2="82" stroke="#64748b" stroke-width="1.5" stroke-dasharray="4 3" />
                    <g id="telemetry-suspension-motion-left-wheel">
                        <rect x="31" y="47" width="25" height="33" rx="6" fill="#111827" stroke="#94a3b8" stroke-width="1.5" />
                        <rect x="37" y="52" width="13" height="23" rx="3" fill="#475569" />
                        <path d="M38 54 L49 72 M49 54 L38 72" stroke="#94a3b8" stroke-width="1" opacity=".6" />
                    </g>
                    <g id="telemetry-suspension-motion-right-wheel">
                        <rect x="124" y="47" width="25" height="33" rx="6" fill="#111827" stroke="#94a3b8" stroke-width="1.5" />
                        <rect x="130" y="52" width="13" height="23" rx="3" fill="#475569" />
                        <path d="M131 54 L142 72 M142 54 L131 72" stroke="#94a3b8" stroke-width="1" opacity=".6" />
                    </g>
                    <line id="telemetry-suspension-motion-left-strut" x1="60" y1="43" x2="55" y2="58" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" />
                    <line id="telemetry-suspension-motion-right-strut" x1="120" y1="43" x2="124" y2="58" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" />
                    <g id="telemetry-suspension-motion-chassis">
                        <path d="M58 21 Q61 13 73 12 L107 12 Q119 13 122 21 L127 45 Q126 52 118 54 L62 54 Q54 52 53 45 Z" fill="#334155" stroke="#cbd5e1" stroke-width="1.6" />
                        <path d="M69 19 Q72 16 78 16 L102 16 Q108 16 111 19 L115 29 L65 29 Z" fill="#0f172a" stroke="#64748b" stroke-width="1" />
                        <path d="M64 34 L116 34 L119 45 Q118 48 113 48 L67 48 Q62 48 61 45 Z" fill="#475569" stroke="#94a3b8" stroke-width="1" />
                        <rect x="54" y="38" width="7" height="8" rx="2" fill="#fbbf24" />
                        <rect x="119" y="38" width="7" height="8" rx="2" fill="#fbbf24" />
                        <path d="M77 39 L103 39" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" />
                    </g>
                    <text x="9" y="10" fill="#cbd5e1" font-size="8">L</text>
                    <text x="165" y="10" fill="#cbd5e1" font-size="8">R</text>
                </svg>

                <div class="mt-1 grid grid-cols-2 gap-x-3 gap-y-1 font-mono text-[10px] tabular-nums">
                    <span><b class="text-sky-300">LF</b> <span id="telemetry-suspension-motion-lf">—</span></span>
                    <span><b class="text-sky-300">RF</b> <span id="telemetry-suspension-motion-rf">—</span></span>
                    <span><b class="text-slate-400">LR</b> <span id="telemetry-suspension-motion-lr">—</span></span>
                    <span><b class="text-slate-400">RR</b> <span id="telemetry-suspension-motion-rr">—</span></span>
                </div>
                <p class="mt-1 text-[9px] leading-tight text-slate-400">Esquema visual, no geometría física exacta.</p>
            </section>

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
