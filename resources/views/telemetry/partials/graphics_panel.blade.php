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
        <button
            type="button"
            @click="window.dispatchEvent(new CustomEvent('telemetry-graph-reset-zoom'))"
            class="rounded-lg border border-[var(--border)]
                px-2 py-1 text-xs hover:border-[var(--accent)]"
        >
            Restablecer zoom
        </button>
    </header>

    <div class="space-y-3">
        <template x-for="item in [
            { key: 'speed', label: 'Speed' },
            { key: 'gears', label: 'Gears' },
            { key: 'throttle', label: 'Throttle' },
            { key: 'brake', label: 'Brake' },
            { key: 'steering', label: 'Dirección' },
            { key: 'sectors', label: 'Comparativa de sectores' },
            { key: 'setup', label: 'Setup' },
            { key: 'suspension', label: 'Suspensión' },
        ]" :key="item.key">
            <section
                x-show="graphics[item.key]"
                class="rounded-lg border border-[var(--border)]
                       bg-[var(--bg)] p-3"
            >
                <div class="mb-2 flex min-w-0 items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-3">
                        <h3
                            class="min-w-0 text-xs font-semibold
                                   text-[var(--text-title)]"
                            x-text="item.label"
                        ></h3>

                        <div
                            x-show="item.key === 'speed'"
                            class="flex shrink-0 items-center gap-1.5 rounded-md border border-white/10 bg-black/20 px-2 py-1"
                            role="group"
                            aria-label="{{ __('ui.max_speed_both_laps') }}"
                        >
                            <span class="text-[9px] font-bold uppercase tracking-wide text-[var(--text-muted)]">{{ strtoupper(__('ui.max_speed')) }}</span>
                            <span
                                id="telemetry-graph-max-speed-main"
                                class="flex h-6 min-w-[36px] items-center justify-center rounded border border-white/15 bg-black/20 px-1 text-[11px] font-semibold tabular-nums"
                                style="color: #FFFFFF"
                            >—</span>
                            <span
                                id="telemetry-graph-max-speed-reference"
                                class="flex h-6 min-w-[36px] items-center justify-center rounded border border-white/15 bg-black/20 px-1 text-[11px] font-semibold tabular-nums"
                                style="color: #A855F7"
                            >—</span>
                        </div>
                    </div>

                    <div
                        x-show="['speed', 'gears', 'throttle', 'brake'].includes(item.key)"
                        class="grid shrink-0 grid-cols-[76px_66px_76px] gap-1"
                        role="group"
                        :aria-label="'Valores actuales de ' + item.label"
                    >
                        <span
                            :id="'telemetry-graph-value-' + item.key + '-main'"
                            class="flex h-7 w-full items-center justify-center overflow-hidden rounded-md border border-white/15 bg-black/20 px-1 text-[11px] font-semibold tabular-nums"
                            style="color: #FFFFFF"
                        >—</span>
                        <span
                            :id="'telemetry-graph-value-' + item.key + '-delta'"
                            class="flex h-7 w-full items-center justify-center overflow-hidden rounded-md border border-white/15 bg-black/20 px-1 text-[11px] font-semibold tabular-nums"
                        ></span>
                        <span
                            :id="'telemetry-graph-value-' + item.key + '-reference'"
                            class="flex h-7 w-full items-center justify-center overflow-hidden rounded-md border border-white/15 bg-black/20 px-1 text-[11px] font-semibold tabular-nums"
                            style="color: #A855F7"
                        ></span>
                    </div>
                </div>

                <template x-if="item.key === 'speed'">
                    <div class="w-full min-w-0">

                        <canvas
                            id="telemetry-graphics-speed"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="{{ __('ui.speed_graph_lap') }}"
                        ></canvas>
                    </div>
                </template>

                <template x-if="item.key === 'gears'">
                    <div class="w-full min-w-0">
                        <canvas
                            id="telemetry-graphics-gears"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="{{ __('ui.gear_graph_lap') }}"
                        ></canvas>
                    </div>
                </template>

                <template x-if="item.key === 'throttle'">
                    <div class="w-full min-w-0">
                        <canvas
                            id="telemetry-graphics-throttle"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="{{ __('ui.throttle_graph_lap') }}"
                        ></canvas>
                    </div>
                </template>

                <template x-if="item.key === 'brake'">
                    <div class="w-full min-w-0">
                        <canvas
                            id="telemetry-graphics-brake"
                            class="block w-full"
                            style="height: 180px;"
                            aria-label="{{ __('ui.brake_graph_lap') }}"
                        ></canvas>
                    </div>
                </template>



                            <template x-if="item.key === 'suspension'">
                    <div class="w-full min-w-0">
                        <p id="telemetry-suspension-data-status"
                           class="mb-2 text-[11px] text-[var(--text-muted)]">
                            {{ __('ui.body_ride_height_main_reference') }}
                        </p>

                        <div id="telemetry-suspension-wheel-layout"
                             class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold">LF · {{ __('ui.front_left') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-lf-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-lf-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-lf" class="block w-full" style="height:145px" aria-label="{{ __('ui.ride_height_front_left') }}"></canvas>
                            </div>
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold">RF · {{ __('ui.front_right') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-rf-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-rf-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-rf" class="block w-full" style="height:145px" aria-label="{{ __('ui.ride_height_front_right') }}"></canvas>
                            </div>
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold">LR · {{ __('ui.rear_left') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-lr-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-lr-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-lr" class="block w-full" style="height:145px" aria-label="{{ __('ui.ride_height_rear_left') }}"></canvas>
                            </div>
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold">RR · {{ __('ui.rear_right') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-rr-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-rr-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-rr" class="block w-full" style="height:145px" aria-label="{{ __('ui.ride_height_rear_right') }}"></canvas>
                            </div>
                        </div>

                        <div id="telemetry-suspension-axle-layout"
                             class="hidden grid grid-cols-1 gap-3">
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-xs font-semibold">FRH · {{ __('ui.front_ride_height') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-front-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-front-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-front" class="block w-full" style="height:180px" aria-label="{{ __('ui.front_ride_height') }}"></canvas>
                            </div>
                            <div class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--card)] p-2">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-xs font-semibold">RRH · {{ __('ui.rear_ride_height') }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)]">RH (mm)</span>
                                </div>
                                <div class="mb-1 flex justify-end gap-2 text-[10px] font-semibold tabular-nums">
                                    <span id="telemetry-suspension-rear-main" class="text-blue-400">—</span>
                                    <span id="telemetry-suspension-rear-reference" class="text-purple-400">—</span>
                                </div>
                                <canvas id="telemetry-graphics-suspension-rear" class="block w-full" style="height:180px" aria-label="{{ __('ui.rear_ride_height') }}"></canvas>
                            </div>
                        </div>

                        <p id="telemetry-suspension-no-data"
                           class="hidden rounded-md border border-dashed border-[var(--border)] p-4 text-sm text-[var(--text-muted)]">
                            {{ __('ui.no_ride_height_channels') }}
                        </p>
                    </div>
                </template>



            </section>

        </template>
    </div>
</section>
