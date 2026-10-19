<section

            class="flex min-h-[480px] min-w-0 flex-col rounded-xl

                   border border-[var(--border)] bg-[var(--card)]"

        >

            <header

                class="flex flex-wrap items-center justify-between gap-3

                       border-b border-[var(--border)] p-4"

            >

                <div>



                </div>


<div id="telemetry-main-panel">
    @include('telemetry.partials.main_telemetry')
</div>

@include('telemetry.partials.telemetry_panel_controls')

@include('telemetry.partials.zoom_control')


            </header>







{{-- MAPA ESTÁTICO + CAPA GPS INDEPENDIENTE --}}

@include('telemetry.partials.static_map')
@include('telemetry.partials.telemetry_panel_behavior')
@include('telemetry.partials.playback_controls')

            {{-- REFERENCIA ESTÁTICA DE SECTORES --}}







</section>
