@extends('layouts.telemetry')



@section('title', 'Telemetry')

@section('page-title', 'Telemetry from stint #'  .  $stint->id)



@section('content')

<div

    x-data="{

        tools: {

            gear: true,
            speed: true,
            sectors: true,
            controls: true,
            steering: true,
            minicontrols: false,

        },

        layers: {

            sectors: false,

            incidents: false,

        },

        graphics: {
            speed: false,
            gears: false,
            inputs: false,
            steering: false,
            sectors: false,
            setup: false,
        },

    }"

    class="space-y-3"

>


{{-- SELECTOR DE STINT Y VUELTA --}}


    {{-- HEADER --}}

    <header

        class="rounded-xl border border-[var(--border)]

               bg-[var(--card)] p-3 my-0"

    >



        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4
            xl:grid-cols-[96px_minmax(260px,1.1fr)_minmax(290px,1.1fr)_minmax(180px,0.8fr)_minmax(220px,1fr)_minmax(260px,1.1fr)_minmax(220px,1fr)]">

            <div class="max-w-28 rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Stint #{{ $stint->id }}</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   Lap  {{ $selectedLap['candidate_lap'] ?? $selectedLap['lap'] ?? '—' }}

                </p>

            </div>



            <div class="max-w-40 rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Lap time</p>

                <p class="flex mt-1 font-mono-timing text-lg font-semibold

                          text-[var(--text-title)]">

                    {{ isset($selectedLap['lap_time']) ? gmdate('i:s.', (int) $selectedLap['lap_time']) . str_pad((string) ((int) (($selectedLap['lap_time'] - floor($selectedLap['lap_time'])) * 1000)), 3, '0', STR_PAD_LEFT) : '--:--.---' }}

                    <button
                        type="button"
                        id="lap-color-trigger"
                        aria-label="Cambiar color de la trazada"
                        title="Color de la trazada"
                        class="flex items-center justify-center rounded-lg"
                    >
                        <span
                            id="lap-color-preview"
                            class="ml-3 h-3 w-3 rounded-full border border-white/20"
                            style="background-color: #00E5FF"
                        ></span>
                    </button>

                </p>

            </div>


            <div class="min-w-0 rounded-lg bg-[var(--bg)] p-3">
                <p class="text-xs text-[var(--text-muted)]">Reference lap</p>

                <div class="mt-1 flex min-w-0 items-center gap-2">
                    <input
                        type="hidden"
                        id="reference_lap"
                        value="{{ $referenceLapNumber ?? '' }}"
                    >

                    <input
                        type="hidden"
                        id="reference_stint"
                        value="{{ $referenceStintId ?? '' }}"
                    >

                    <span
                        id="reference-lap-label"
                        class="min-w-0 flex-1 truncate rounded-lg border
                            border-[var(--border)] bg-[var(--bg)] p-2
                            text-sm text-[var(--text)]"
                    >{{ $referenceLapNumber !== null ? '#' . ($referenceStintId ?? '—') . ' · Vuelta ' . $referenceLapNumber : '---' }}</span>

                    <div
                        id="reference-lap-color-host"
                        class="relative flex shrink-0 items-center"
                    ></div>

                    <button
                        type="button"
                        id="reference-lap-search"
                        class="flex shrink-0 items-center gap-2 rounded-lg
                            border border-[var(--border)] px-3 py-2
                            text-sm font-semibold text-[var(--text-title)]
                            hover:border-[var(--accent)]"
                        aria-label="Buscar vuelta de referencia"
                        title="Buscar vuelta de referencia"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        Buscar
                    </button>
                </div>
            </div>


            <div class="rounded-lg bg-[var(--bg)] p-3">
                <p class="text-xs text-[var(--text-muted)]">Reference mode</p>

                <div class="mt-2 grid grid-cols-2 gap-1"
                     id="telemetry-reference-mode"
                     role="group"
                     aria-label="Modo de análisis de referencia">
                    <button
                        type="button"
                        id="reference-mode-temporal"
                        data-reference-mode="temporal"
                        aria-pressed="true"
                        class="rounded-md border border-cyan-500 bg-cyan-600 px-2 py-2 text-xs font-semibold text-white"
                    >Temporal</button>
                    <button
                        type="button"
                        id="reference-mode-spatial"
                        data-reference-mode="spatial"
                        aria-pressed="false"
                        class="rounded-md border border-[var(--border)] px-2 py-2 text-xs font-semibold text-[var(--text)] hover:border-[var(--accent)]"
                    >Espacial</button>
                </div>

                <p id="reference-mode-description"
                   class="mt-2 text-[11px] leading-tight text-[var(--text-muted)]">
                    Sincronización por tiempo transcurrido.
                </p>
            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Fecha</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ $stint?->created_at?->format('d/m/Y H:i') ?? '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Aire / pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                   {{ isset($sessionData['conditions']['track_air_temp_c']) ? number_format($sessionData['conditions']['track_air_temp_c'], 1) . ' °C' : '—' }} / {{ isset($sessionData['conditions']['track_surface_temp_c']) ? number_format($sessionData['conditions']['track_surface_temp_c'], 1) . ' °C' : '—' }}

                </p>

            </div>



            <div class="rounded-lg bg-[var(--bg)] p-3">

                <p class="text-xs text-[var(--text-muted)]">Estado pista</p>

                <p class="mt-1 text-lg font-semibold text-[var(--text-title)]">

                    {{ $sessionData['session']['track_rubber_state'] ?? '—' }}

                </p>

            </div>

        </div>

    </header>



{{-- TOOLS + TRACK MAP + GRAPHICS --}}
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">

        {{-- TOOLS --}}
        @include('telemetry.partials.aside')

        {{-- MAPA Y GRÁFICAS --}}
        <div
            class="grid min-w-0 grid-cols-1 gap-4"
            :class="Object.values(graphics).some(Boolean)
                ? 'xl:grid-cols-[minmax(0,1fr)_minmax(460px,0.65fr)]'
                : 'grid-cols-1'"
        >
            {{-- TRACK MAP --}}
            @include('telemetry.partials.track_map')

            {{-- GRAPHICS --}}
            @include('telemetry.partials.graphics_panel')
        </div>
    </div>

</div>


{{-- MODAL: CATÁLOGO DE VUELTAS DE REFERENCIA --}}
<div
    id="reference-lap-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center
           bg-black/70 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="reference-lap-modal-title"
>
    <div
        id="reference-lap-modal-panel"
        class="flex max-h-[85vh] w-full max-w-3xl flex-col
               overflow-hidden rounded-2xl border
               border-[var(--border)] bg-[var(--card)] shadow-2xl"
    >
        {{-- Cabecera --}}
        <div
            class="flex items-center justify-between gap-4
                   border-b border-[var(--border)] p-4"
        >
            <div>
                <h2
                    id="reference-lap-modal-title"
                    class="text-lg font-semibold text-[var(--text-title)]"
                >
                    Buscar vuelta de referencia
                </h2>

                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    Selecciona un stint y después una vuelta.
                </p>
            </div>

            <button
                type="button"
                id="reference-lap-modal-close"
                class="flex h-9 w-9 shrink-0 items-center justify-center
                       rounded-lg border border-[var(--border)]
                       text-xl text-[var(--text)] hover:border-[var(--accent)]"
                aria-label="Cerrar catálogo"
                title="Cerrar"
            >
                &times;
            </button>
        </div>

        {{-- Búsqueda --}}
        <div class="border-b border-[var(--border)] p-4">
            <label
                for="reference-lap-search-input"
                class="mb-2 block text-xs font-semibold uppercase
                       tracking-wider text-[var(--text-muted)]"
            >
                Buscar por stint o piloto
            </label>

            <input
                type="search"
                id="reference-lap-search-input"
                autocomplete="off"
                placeholder="Ej.: 1089 o nombre del piloto"
                class="w-full rounded-lg border border-[var(--border)]
                       bg-[var(--bg)] px-3 py-2.5 text-sm
                       text-[var(--text)] outline-none
                       focus:border-[var(--accent)]"
            >

            <p class="mt-2 text-xs text-[var(--text-muted)]">
                Solo se mostrarán referencias compatibles con el coche
                y el circuito de la vuelta principal.
            </p>
        </div>

        {{-- Resultados del catálogo --}}
        <div
            id="reference-lap-results"
            class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4"
            aria-live="polite"
        >
            <p
                id="reference-lap-results-status"
                class="py-8 text-center text-sm text-[var(--text-muted)]"
            >
                Introduce un término de búsqueda para consultar el catálogo.
            </p>
        </div>

        {{-- Pie --}}
        <div
            class="flex items-center justify-between gap-3
                   border-t border-[var(--border)] p-4"
        >
            <span
                id="reference-lap-results-count"
                class="text-xs text-[var(--text-muted)]"
            >
                Catálogo de referencias
            </span>

            <button
                type="button"
                id="reference-lap-modal-cancel"
                class="rounded-lg border border-[var(--border)]
                       px-4 py-2 text-sm font-semibold
                       text-[var(--text-title)] hover:border-[var(--accent)]"
            >
                Cancelar
            </button>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('reference-lap-modal');
    const panel = document.getElementById('reference-lap-modal-panel');
    const openButton = document.getElementById('reference-lap-search');
    const closeButton = document.getElementById('reference-lap-modal-close');
    const cancelButton = document.getElementById('reference-lap-modal-cancel');
    const searchInput = document.getElementById('reference-lap-search-input');
    const results = document.getElementById('reference-lap-results');

    if (!modal || !panel || !openButton || !searchInput || !results) {
        return;
    }

    let previousFocus = null;

    function openModal() {
        previousFocus = document.activeElement;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        searchInput.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    }

    openButton.addEventListener('click', openModal);
    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            closeModal();
        }
    });

    panel.addEventListener('click', event => {
        event.stopPropagation();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });


    const resultsStatus = document.getElementById(
        'reference-lap-results-status'
    );
    const resultsCount = document.getElementById(
        'reference-lap-results-count'
    );

    const referenceStintInput = document.getElementById('reference_stint');
    const referenceLapInput = document.getElementById('reference_lap');
    const referenceLabel = document.getElementById('reference-lap-label');

    const endpoint = @json(route('telemetry.lap.references'));

    let searchTimer = null;
    let activeRequest = null;

    function setStatus(message) {
        results.replaceChildren();

        const paragraph = document.createElement('p');
        paragraph.className =
            'py-8 text-center text-sm text-[var(--text-muted)]';
        paragraph.textContent = message;

        results.appendChild(paragraph);

    }

    function renderResults(stints) {
        results.replaceChildren();

        if (!Array.isArray(stints) || stints.length === 0) {
            setStatus('No se encontraron stints con vueltas válidas.');
            resultsCount.textContent = '0 stints';
            return;
        }

        let totalLaps = 0;

        stints.forEach(stint => {
            const card = document.createElement('section');
            card.className =
                'rounded-xl border border-[var(--border)] p-3';

            const heading = document.createElement('div');
            heading.className =
                'mb-3 flex flex-wrap items-center justify-between gap-2';

            const title = document.createElement('h3');
            title.className =
                'font-semibold text-[var(--text-title)]';
            title.textContent =
                `Stint #${stint.stint_id} · ${stint.driver}`;

            const date = document.createElement('span');
            date.className = 'text-xs text-[var(--text-muted)]';
            date.textContent = stint.date ?? '';

            heading.append(title, date);
            card.appendChild(heading);

            const lapList = document.createElement('div');
            lapList.className = 'flex flex-wrap gap-2';

            (stint.laps ?? []).forEach(lap => {
                totalLaps++;

                const button = document.createElement('button');
                button.type = 'button';
                button.className =
                    'rounded-lg border border-[var(--border)] px-3 py-2 ' +
                    'text-sm text-[var(--text)] hover:border-[var(--accent)]';

                button.textContent =
                    `Vuelta ${lap.number} · ${lap.lap_time_label}`;

                button.addEventListener('click', () => {
                    selectReference(stint, lap);
                });

                lapList.appendChild(button);
            });

            card.appendChild(lapList);
            results.appendChild(card);
        });

        resultsCount.textContent =
            `${stints.length} stints · ${totalLaps} vueltas`;
    }

    async function loadReferences(search = '') {
        const params = new URLSearchParams(window.location.search);
        const mainStintId = params.get('stint');

        if (!mainStintId) {
            setStatus('No se ha podido identificar el stint principal.');
            resultsCount.textContent = 'Sin resultados';
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        setStatus('Buscando referencias compatibles...');
        resultsCount.textContent = 'Consultando catálogo';

        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('stint', mainStintId);
        const selectedLap = params.get('lap');
        if (selectedLap) {
            url.searchParams.set('lap', selectedLap);
        }

        if (search.trim() !== '') {
            url.searchParams.set('q', search.trim());
        }

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}`);
            }

            const payload = await response.json();
            renderResults(payload.data ?? []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            setStatus(
                'No se pudo cargar el catálogo. Comprueba la conexión e inténtalo de nuevo.'
            );
            resultsCount.textContent = 'Error de consulta';
            console.error('Error al cargar referencias:', error);
        }
    }

    function selectReference(stint, lap) {
        referenceStintInput.value = String(stint.stint_id);
        referenceLapInput.value = String(lap.number);

        referenceLabel.textContent =
            `#${stint.stint_id} · V${lap.number} · ${lap.lap_time_label}`;

        // La selección recarga la vista con ambos identificadores para que
        // Laravel resuelva las muestras y el overlay se inicialice con ellas.
        const params = new URLSearchParams(window.location.search);
        params.set('reference_stint', String(stint.stint_id));
        params.set('reference_lap', String(lap.number));

        closeModal();
        window.location.assign(`${window.location.pathname}?${params.toString()}`);

    }

    openButton.addEventListener('click', () => {
        loadReferences(searchInput.value);
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            loadReferences(searchInput.value);
        }, 300);
    });

});
</script>



@include('telemetry.partials.lap_overlay_reproductor')













@endsection
