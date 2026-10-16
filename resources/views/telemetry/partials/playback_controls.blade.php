{{-- Controles del reproductor GPS de la vuelta --}}
<div
    class="mb-4 w-full bg-[var(--card)] p-4"
    aria-label="Reproductor de telemetría GPS"
>
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-[var(--text-title)]">Recorrido de vuelta</h3>
            <p class="text-xs text-[var(--text-muted)]">Reproduce la trazada GPS sobre el circuito</p>
        </div>
        <label class="flex items-center gap-2 text-sm text-[var(--text-muted)]">
            Velocidad
            <select
                id="telemetry-lap-speed"
                class="rounded-md border border-[var(--border)] bg-[var(--bg)] px-2 py-1 text-[var(--text-title)]"
                aria-label="Velocidad de reproducción"
            >
                <option value="0.25">×0,25</option>
                <option value="0.5">×0,5</option>
                <option value="1" selected>×1</option>
                <option value="2">×2</option>
                <option value="4">×4</option>
                <option value="8">×8</option>
            </select>
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button
            id="telemetry-lap-play"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500"
        >
            <span aria-hidden="true">▶</span> Reproducir
        </button>
        <button
            id="telemetry-lap-pause"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text-title)] hover:bg-[var(--bg)]"
        >
            <span aria-hidden="true">Ⅱ</span> Pausa
        </button>
        <button
            id="telemetry-lap-restart"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text-title)] hover:bg-[var(--bg)]"
        >
            ↺ Reiniciar
        </button>
        <span id="telemetry-lap-progress-label" class="ml-auto min-w-12 text-right text-sm font-semibold tabular-nums text-[var(--text-title)]">0%</span>
    </div>

    <input
        id="telemetry-lap-progress"
        type="range"
        min="0"
        max="100"
        step="0.1"
        value="0"
        class="mt-4 w-full cursor-pointer accent-cyan-500"
        aria-label="Posición de reproducción en la vuelta"
    >
    <div class="mt-1 flex justify-between text-xs text-[var(--text-muted)]">
        <span>Salida / meta</span>
        <span>Fin de vuelta</span>
    </div>
</div>
