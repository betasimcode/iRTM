<div class="flex flex-wrap items-center justify-end gap-2">
    <span
        class="rounded-md border border-[var(--border)]
               px-2 py-1 text-xs text-[var(--text-muted)]"
    >
        Sin datos
    </span>

    <div class="flex items-center gap-2">
        <button
            id="telemetry-map-zoom-out"
            type="button"
            class="rounded-lg border border-[var(--border)]
                   px-3 py-2 text-sm text-[var(--text)]"
            aria-label="Alejar mapa"
            title="Alejar"
        >−</button>

        <span
            id="telemetry-map-zoom-level"
            class="min-w-12 text-center text-xs text-[var(--text-muted)]"
        >100%</span>

        <button
            id="telemetry-map-zoom-in"
            type="button"
            class="rounded-lg border border-[var(--border)]
                   px-3 py-2 text-sm text-[var(--text)]"
            aria-label="Ampliar mapa"
            title="Ampliar"
        >+</button>

        <button
            id="telemetry-map-reset"
            type="button"
            class="rounded-lg border border-[var(--border)]
                   px-3 py-2 text-sm text-[var(--text)]"
        >Restablecer vista</button>
    </div>
</div>
