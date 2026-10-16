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

@include('telemetry.partials.main_telemetry')

@include('telemetry.partials.zoom_control')

            </header>







{{-- MAPA ESTÁTICO + CAPA GPS INDEPENDIENTE --}}

@include('telemetry.partials.static_map')

@include('telemetry.partials.playback_controls')

            {{-- REFERENCIA ESTÁTICA DE SECTORES --}}







</section>
