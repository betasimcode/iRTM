<section
    x-cloak
    x-show="Object.values(graphics).some(Boolean)"
    x-transition
    class="box-border w-full min-w-0 rounded-xl
           border border-[var(--border)]
           bg-[var(--card)] p-3"
>
    <header class="mb-3 flex items-center justify-between gap-2">
        <h2 class="text-sm font-semibold uppercase tracking-wider
                   text-[var(--text-title)]">
            Graphics
        </h2>

        <button
            type="button"
            @click="Object.keys(graphics).forEach(key => graphics[key] = false)"
            class="rounded-lg border border-[var(--border)]
                   px-2 py-1 text-xs hover:border-[var(--accent)]"
        >
            Cerrar gráficas
        </button>
    </header>

    <div class="space-y-3">
        <template x-for="item in [
            { key: 'speed', label: 'Speed' },
            { key: 'gears', label: 'Gears' },
            { key: 'inputs', label: 'Throttle / Brake' },
            { key: 'steering', label: 'Dirección' },
            { key: 'sectors', label: 'Comparativa de sectores' },
            { key: 'setup', label: 'Setup' },
        ]" :key="item.key">
            <section
                x-show="graphics[item.key]"
                class="rounded-lg border border-[var(--border)]
                       bg-[var(--bg)] p-3"
            >
                <h3
                    class="mb-2 text-xs font-semibold
                           text-[var(--text-title)]"
                    x-text="item.label"
                ></h3>

                <template x-if="item.key === 'speed'">
                    <div class="w-full min-w-0">

                        <canvas
                            id="telemetry-graphics-speed"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="Gráfica de velocidad durante la vuelta"
                        ></canvas>
                    </div>
                </template>

                <template x-if="item.key === 'gears'">
                    <div class="w-full min-w-0">
                        <canvas
                            id="telemetry-graphics-gears"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="Gráfica de marchas durante la vuelta"
                        ></canvas>
                    </div>
                </template>




            </section>
        </template>
    </div>
</section>
