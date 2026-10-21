<script src="{{ asset('js/telemetry/sector-marker-style.js') }}?v={{ filemtime(public_path('js/telemetry/sector-marker-style.js')) }}"></script>
<script>
(() => {
    console.info('[LapOverlay] Script cargado');

    const initLapOverlay = () => {
        console.info('[LapOverlay] Inicializando');

        const viewport = document.getElementById('telemetry-track-viewport');
        const container = document.getElementById('telemetry-track-canvas');
        const canvas = document.getElementById('telemetry-lap-overlay');
        const mapObject = document.getElementById('telemetry-track-map');

        console.info('[LapOverlay] Elementos', {
            viewport: !!viewport,
            container: !!container,
            canvas: !!canvas,
            mapObject: !!mapObject,
        });

        if (!viewport || !container || !canvas || !mapObject) {
            console.warn('[LapOverlay] Faltan elementos del mapa');
            return;
        }

        if (viewport.dataset.mapNavigationReady === '1') {
            console.info('[LapOverlay] Ya estaba inicializado');
            return;
        }

        viewport.dataset.mapNavigationReady = '1';

        const samples = @js($lapTelemetry ?? []);

        const referenceSamples = @js($referenceLapTelemetry ?? []);
        const referenceLapNumber = @js($referenceLapNumber ?? null);

        const DEFAULT_REFERENCE_LAP_COLOR = '#E879F9';

        const sectorSegments = @js(
            $sessionData['sectors']['segments'] ?? []
        );

        console.info('[LapOverlay] Datos recibidos', {
            samples: samples.length,
            sectorSegments,
        });

        const trackId = String(viewport.dataset.trackId);

        const zoomInButton = document.getElementById('telemetry-map-zoom-in');
        const zoomOutButton = document.getElementById('telemetry-map-zoom-out');
        const resetButton = document.getElementById('telemetry-map-reset');
        const zoomLabel = document.getElementById('telemetry-map-zoom-level');

        // El control se crea aquí para no depender de una modificación separada de la vista.
        let followButton = document.getElementById('telemetry-map-follow');
        if (!followButton && resetButton) {
            followButton = document.createElement('button');
            followButton.id = 'telemetry-map-follow';
            followButton.type = 'button';
            followButton.className =
                'rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text)]';
            followButton.textContent = 'Seguir coche';
            followButton.title = 'Mantener el coche centrado durante la reproducción';
            followButton.setAttribute('aria-pressed', 'false');
            resetButton.insertAdjacentElement('afterend', followButton);
        }

        // Controles del reproductor (se insertan en track_map.blade.php).
        const playButton = document.getElementById('telemetry-lap-play');
        const pauseButton = document.getElementById('telemetry-lap-pause');
        const playbackResetButton = document.getElementById('telemetry-lap-restart');
        const progressSlider = document.getElementById('telemetry-lap-progress');
        const progressLabel = document.getElementById('telemetry-lap-progress-label');
        const speedSelect = document.getElementById('telemetry-lap-speed');


        // Paleta fija compartida por trazadas y gráficas.
        const lapColorPresets = [
            { name: 'Red', value: '#EF4444' },
            { name: 'Blue', value: '#3B82F6' },
            { name: 'Violet', value: '#A855F7' },
            { name: 'Green', value: '#22C55E' },
            { name: 'Yellow', value: '#FACC15' },
            { name: 'Cian', value: '#06B6D4' },
            { name: 'Orange', value: '#F59E0B' },
        ];

        const lapSelector = document.getElementById('lap');
        const lapColorTrigger = document.getElementById('lap-color-trigger');
        const lapColorPreview = document.getElementById('lap-color-preview');

        // Conserva los cambios manuales mientras permanezca activa la página.
        const manualLapColors = new Map();

        const getSelectedLapKey = () => {
            if (lapSelector?.value) {
                return String(lapSelector.value);
            }

            return new URLSearchParams(window.location.search).get('lap') ?? '';
        };

        const getAutomaticLapColor = () => {
            const lapNumber = Number(getSelectedLapKey());
            const index = lapSelector
                ? lapSelector.selectedIndex
                : Math.max(0, lapNumber - 1);

            return lapColorPresets[
                Math.max(0, index) % lapColorPresets.length
            ].value;
        };

        const getActiveReferenceLapColor = () => {
            if (referenceLapNumber == null) {
                return DEFAULT_REFERENCE_LAP_COLOR;
            }

            const key = `reference:${referenceLapNumber}`;

            return manualLapColors.get(key) ??
                DEFAULT_REFERENCE_LAP_COLOR;
        };

        const getActiveLapColor = () => {
            const key = getSelectedLapKey();
            return manualLapColors.get(key) ?? getAutomaticLapColor();
        };

        const updateLapColorPreview = () => {
            if (lapColorPreview) {
                lapColorPreview.style.backgroundColor = getActiveLapColor();
            }

            if (lapColorTrigger) {
                lapColorTrigger.title =
                    `Color de trazada: ${lapColorPresets.find(
                        color => color.value === getActiveLapColor()
                    )?.name ?? 'Personalizado'}`;
            }
        };



        if (lapColorTrigger && lapColorPreview) {
            const colorContainer = lapColorTrigger.parentElement;

            if (colorContainer) {
                colorContainer.style.position = 'relative';

                const colorMenu = document.createElement('div');

                colorMenu.id = 'lap-color-menu';
                colorMenu.className =
                    'absolute left-5 top-full z-50 mt-2 hidden ' +
                    'min-w-40 rounded-lg border border-[var(--border)] ' +
                    'bg-[var(--bg)] p-2 shadow-xl';

                colorMenu.setAttribute('role', 'group');
                colorMenu.setAttribute('aria-label', 'Color de trazada');

                lapColorPresets.forEach(preset => {
                    const option = document.createElement('button');

                    option.type = 'button';
                    option.className =
                        'flex w-full items-center gap-2 rounded-md ' +
                        'px-3 py-2 text-left text-sm hover:bg-white/10';

                    option.innerHTML = `
                        <span style="
                            display:inline-block;
                            width:16px;
                            height:16px;
                            border-radius:50%;
                            background:${preset.value};
                            flex-shrink:0;
                        "></span>
                        <span>${preset.name}</span>
                    `;

                    option.addEventListener('click', () => {
                        const key = getSelectedLapKey();

                        if (!key) return;

                        manualLapColors.set(key, preset.value);
                        updateLapColorPreview();
                        updateVehicleColor();
                        colorMenu.classList.add('hidden');
                        drawOverlay();
                        drawSpeedGraph();
                        drawGearsGraph();
                        drawThrottleGraph();
                        drawBrakeGraph();
                    });

                    colorMenu.appendChild(option);
                });

                colorContainer.appendChild(colorMenu);

                lapColorTrigger.addEventListener('click', event => {
                    event.stopPropagation();
                    colorMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', event => {
                    if (!colorContainer.contains(event.target)) {
                        colorMenu.classList.add('hidden');
                    }
                });

                lapSelector?.addEventListener('change', () => {
                    updateLapColorPreview();
                    updateVehicleColor();
                    drawOverlay();
                    drawSpeedGraph();
                    drawGearsGraph();
                    drawThrottleGraph();
                    drawBrakeGraph();
                });
            }
        }

        updateLapColorPreview();

        // Selector independiente para el color de la vuelta de referencia.
        if (lapColorTrigger?.parentElement) {
            const colorContainer = lapColorTrigger.parentElement;

            const referenceColorTrigger = document.createElement('button');
            referenceColorTrigger.type = 'button';
            referenceColorTrigger.id = 'reference-lap-color-trigger';
            referenceColorTrigger.className = lapColorTrigger.className;
            referenceColorTrigger.title = 'Color de la vuelta de referencia';
            referenceColorTrigger.disabled = referenceLapNumber == null;
            referenceColorTrigger.style.marginLeft = '0.5rem';

            const referenceColorPreview = document.createElement('span');
            referenceColorPreview.style.cssText = [
                'display:inline-block',
                'width:14px',
                'height:14px',
                'border-radius:50%',
                'background:' + getActiveReferenceLapColor(),
                'border:1px solid rgba(148,163,184,.6)'
            ].join(';');

            referenceColorTrigger.appendChild(referenceColorPreview);
            referenceColorTrigger.appendChild(
                document.createTextNode(' Referencia')
            );

            const referenceColorMenu = document.createElement('div');
            referenceColorMenu.id = 'reference-lap-color-menu';
            referenceColorMenu.className =
                'absolute left-5 top-full z-50 mt-2 hidden ' +
                'min-w-40 rounded-lg border border-[var(--border)] ' +
                'bg-[var(--bg)] p-2 shadow-xl';

            referenceColorMenu.setAttribute('role', 'group');
            referenceColorMenu.setAttribute(
                'aria-label',
                'Color de la vuelta de referencia'
            );

            lapColorPresets.forEach(preset => {
                const option = document.createElement('button');

                option.type = 'button';
                option.className =
                    'flex w-full items-center gap-2 rounded-md ' +
                    'px-3 py-2 text-left text-sm hover:bg-white/10';

                option.innerHTML = `
                    <span style="
                        display:inline-block;
                        width:16px;
                        height:16px;
                        border-radius:50%;
                        background:${preset.value};
                        flex-shrink:0;
                    "></span>
                    <span>${preset.name}</span>
                `;

                option.addEventListener('click', () => {
                    if (referenceLapNumber == null) return;

                    manualLapColors.set(
                        `reference:${referenceLapNumber}`,
                        preset.value
                    );

                    referenceColorPreview.style.backgroundColor = preset.value;
                    referenceColorTrigger.title =
                        `Color de referencia: ${preset.name}`;

                    referenceColorMenu.classList.add('hidden');

                    drawOverlay();
                    redrawAllGraphs();
                });

                referenceColorMenu.appendChild(option);
            });

            colorContainer.appendChild(referenceColorTrigger);
            colorContainer.appendChild(referenceColorMenu);

            referenceColorTrigger.addEventListener('click', event => {
                event.stopPropagation();
                referenceColorMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', event => {
                if (!colorContainer.contains(event.target)) {
                    referenceColorMenu.classList.add('hidden');
                }
            });
        }

        const defaultCalibration = {
            scale: 1,
            rotation_deg: 0,
            offset_x_pct: 0,
            offset_y_pct: 0,
        };

        @include('telemetry.partials.track_calibrations')

        const calibration = {
            ...defaultCalibration,
            ...(calibrations[trackId] ?? {}),
        };

        // Conservamos cada muestra completa para sincronizar posición y tiempo.
        const validSamples = samples.filter(sample =>
            sample?.position?.lat != null &&
            sample?.position?.lon != null &&
            Number.isFinite(Number(sample.position.lat)) &&
            Number.isFinite(Number(sample.position.lon))
        );

        const points = validSamples.map(sample => ({
            lat: Number(sample.position.lat),
            lon: Number(sample.position.lon),
            sessionTime: Number.isFinite(Number(sample.session_time))
                ? Number(sample.session_time)
                : null,
            lapDistPct: Number.isFinite(Number(sample.lap_dist_pct))
                ? Number(sample.lap_dist_pct)
                : null,
        }));

        // Intervalo horizontal compartido por todas las gráficas.
        // Los valores representan la distancia normalizada de la vuelta.
        const graphViewport = {
            start: 0,
            end: 1,
        };

        // Durante la reproducción, desplaza la ventana visible sin alterar el zoom.
        // El cursor se mantiene aproximadamente al 65 % del ancho de la gráfica.
        const updateGraphFollow = () => {
            if (!isPlaying || validSamples.length === 0) return;

            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const sample = validSamples[sampleIndex];
            const rawDistance = Number(sample?.lap_dist_pct);

            const distance = Number.isFinite(rawDistance)
                ? (
                    rawDistance > 1 && rawDistance <= 100
                        ? rawDistance / 100
                        : rawDistance
                )
                : sampleIndex / Math.max(1, validSamples.length - 1);

            if (!Number.isFinite(distance)) return;

            const range = graphViewport.end - graphViewport.start;

            // Con la vuelta completa visible no hace falta desplazar la gráfica.
            if (range >= 0.999) return;

            const cursorRatio = (distance - graphViewport.start) / range;
            const targetRatio = 0.65;

            // Mantener una zona de anticipación a la derecha, y recuperar el cursor
            // si se reanuda la reproducción desde fuera del intervalo visible.
            if (cursorRatio > 0.50 || cursorRatio < 0.08) {
                graphViewport.start = distance - range * targetRatio;
                graphViewport.end = graphViewport.start + range;
                clampGraphViewport();
            }
        };

        const GRAPH_MIN_RANGE = 0.005; // Zoom máximo: intervalo del 0,5 %.
        const GRAPH_ZOOM_FACTOR = 1.25;

        const redrawAllGraphs = () => {
            drawSpeedGraph();
            drawGearsGraph();
            drawThrottleGraph();
            drawBrakeGraph();
        };

        const clampGraphViewport = () => {
            const range = graphViewport.end - graphViewport.start;

            if (range >= 1) {
                graphViewport.start = 0;
                graphViewport.end = 1;
                return;
            }

            if (graphViewport.start < 0) {
                graphViewport.end -= graphViewport.start;
                graphViewport.start = 0;
            }

            if (graphViewport.end > 1) {
                graphViewport.start -= graphViewport.end - 1;
                graphViewport.end = 1;
            }

            graphViewport.start = Math.max(0, graphViewport.start);
            graphViewport.end = Math.min(1, graphViewport.end);
        };

        const resetGraphZoom = () => {
            graphViewport.start = 0;
            graphViewport.end = 1;
            redrawAllGraphs();
        };

        window.addEventListener(
            'telemetry-graph-reset-zoom',
            resetGraphZoom
        );

        const zoomGraphAt = (distance, factor) => {
            const oldRange = graphViewport.end - graphViewport.start;
            const newRange = Math.max(
                GRAPH_MIN_RANGE,
                Math.min(1, oldRange * factor)
            );

            const anchor = Math.max(
                graphViewport.start,
                Math.min(graphViewport.end, distance)
            );

            const ratio = oldRange > 0
                ? (anchor - graphViewport.start) / oldRange
                : 0.5;

            graphViewport.start = anchor - ratio * newRange;
            graphViewport.end = graphViewport.start + newRange;

            clampGraphViewport();
            redrawAllGraphs();
        };

        const graphDistanceAtPointer = (canvas, clientX) => {
            const rect = canvas.getBoundingClientRect();

            // Las gráficas usan un margen izquierdo de 38 px y uno derecho de 10 px.
            const left = 38;
            const right = 10;
            const plotWidth = rect.width - left - right;

            if (plotWidth <= 0) return graphViewport.start;

            const pixel = clientX - rect.left - left;
            const ratio = Math.max(0, Math.min(1, pixel / plotWidth));

            return graphViewport.start
                + ratio * (graphViewport.end - graphViewport.start);
        };


        // Gráfica de velocidad sincronizada con la vuelta.
        const speedGraphCanvas = document.getElementById('telemetry-graphics-speed');
        let speedGraphContext = null;

        const drawSpeedGraph = () => {
            const graph = document.getElementById('telemetry-graphics-speed');

            if (!graph || !graph.isConnected) return;

            const width = graph.clientWidth;
            const height = graph.clientHeight;

            if (width <= 0 || height <= 0) return;

            const dpr = window.devicePixelRatio || 1;

            graph.width = Math.round(width * dpr);
            graph.height = Math.round(height * dpr);

            const ctx = graph.getContext('2d');
            if (!ctx) return;

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const data = validSamples
                .map((sample, index) => {
                    const rawDistance = Number(sample.lap_dist_pct);
                    const rawSpeed = Number(sample.speed_kph);

                    if (
                        !Number.isFinite(rawDistance) ||
                        !Number.isFinite(rawSpeed) ||
                        rawSpeed < 0
                    ) {
                        return null;
                    }

                    // lap_dist_pct normalmente se expresa entre 0 y 1.
                    // Si llega en porcentaje, convertirlo al mismo rango.
                    const distance = rawDistance > 1 && rawDistance <= 100
                        ? rawDistance / 100
                        : rawDistance;

                    if (distance < 0 || distance > 1) {
                        return null;
                    }

                    return {
                        x: distance,
                        speed: rawSpeed,
                        index,
                    };
                })
                .filter(Boolean)
                .sort((a, b) => a.x - b.x);

            if (data.length < 2) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '12px sans-serif';
                ctx.fillText('Sin datos de velocidad suficientes', 8, 20);
                return;
            }

            const maxSpeed = Math.max(50, ...data.map(point => point.speed));
            const minSpeed = 0;

            const padding = { top: 10, right: 10, bottom: 24, left: 38 };
            const plotWidth = width - padding.left - padding.right;
            const plotHeight = height - padding.top - padding.bottom;

            if (plotWidth <= 0 || plotHeight <= 0) return;

            const xAt = distance =>
                padding.left
                + ((distance - graphViewport.start)
                    / (graphViewport.end - graphViewport.start))
                * plotWidth;

            const yAt = speed =>
                padding.top + (1 - (speed - minSpeed) / (maxSpeed - minSpeed))
                    * plotHeight;

            // Rejilla y escala vertical.
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.lineWidth = 0.5;
            ctx.strokeStyle = 'rgba(148, 163, 184, 0.2)';
            ctx.fillStyle = '#94a3b8';

            for (let step = 0; step <= 4; step++) {
                const speed = maxSpeed * step / 4;
                const y = yAt(speed);

                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(width - padding.right, y);
                ctx.stroke();

                ctx.fillText(String(Math.round(speed)), padding.left - 6, y);
            }

            // Distancia normalizada de la vuelta: 0–100 %.
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';

            [0, 25, 50, 75, 100].forEach(percent => {
                const distance = graphViewport.start
                    + (graphViewport.end - graphViewport.start) * percent / 100;

                const x = xAt(distance);

                ctx.fillText(
                    `${(distance * 100).toFixed(
                        graphViewport.end - graphViewport.start < 0.1 ? 1 : 0
                    )}%`,
                    x,
                    height - padding.bottom + 7
                );
            });

            // Curva de velocidad.
            ctx.save();
            ctx.beginPath();
            ctx.rect(padding.left, padding.top, plotWidth, plotHeight);
            ctx.clip();
            ctx.beginPath();

            data.forEach((point, index) => {
                const x = xAt(point.x);
                const y = yAt(point.speed);

                if (index === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });

            ctx.strokeStyle = getActiveLapColor();
            ctx.lineWidth = 0.5;
            ctx.lineJoin = 'round';
            ctx.stroke();
            ctx.restore();

            // Cursor asociado al índice de reproducción actual.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentSample = validSamples[sampleIndex];
            const rawCurrentDistance = Number(currentSample?.lap_dist_pct);

            const currentDistance = Number.isFinite(rawCurrentDistance)
                ? (
                    rawCurrentDistance > 1 && rawCurrentDistance <= 100
                        ? rawCurrentDistance / 100
                        : rawCurrentDistance
                )
                : sampleIndex / Math.max(1, validSamples.length - 1);

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(
                    Math.max(0, Math.min(1, currentDistance))
                );

                ctx.beginPath();
                ctx.moveTo(cursorX, padding.top);
                ctx.lineTo(cursorX, height - padding.bottom);
                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 0.5;
                ctx.stroke();

                const nearest = data.reduce((best, point) =>
                    Math.abs(point.index - playbackIndex)
                        < Math.abs(best.index - playbackIndex)
                        ? point
                        : best
                );

                ctx.beginPath();
                ctx.arc(cursorX, yAt(nearest.speed), 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                ctx.textAlign = 'right';
                ctx.textBaseline = 'top';
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = '#38bdf8';
                ctx.fillText(
                    `${nearest.speed.toFixed(0)} km/h`,
                    width - padding.right,
                    padding.top
                );
            }

        };

        // Gráfica de marchas sincronizada con la reproducción.
        const drawGearsGraph = () => {
            const graph = document.getElementById('telemetry-graphics-gears');

            if (!graph || !graph.isConnected) return;

            const width = graph.clientWidth;
            const height = graph.clientHeight;

            if (width <= 0 || height <= 0) return;

            const dpr = window.devicePixelRatio || 1;

            graph.width = Math.round(width * dpr);
            graph.height = Math.round(height * dpr);

            const ctx = graph.getContext('2d');
            if (!ctx) return;

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const data = validSamples
                .map((sample, index) => {
                    const rawDistance = Number(sample.lap_dist_pct);
                    const rawGear = Number(sample.gear);
                    const time = Number(sample.session_time);

                    if (
                        !Number.isFinite(rawDistance) ||
                        sample.gear == null ||
                        !Number.isFinite(rawGear) ||
                        rawGear < -1 ||
                        rawGear > 10
                    ) {
                        return null;
                    }

                    const distance =
                        rawDistance > 1 && rawDistance <= 100
                            ? rawDistance / 100
                            : rawDistance;

                    if (distance < 0 || distance > 1) return null;

                    return {
                        x: distance,
                        gear: rawGear,
                        time,
                        index,
                    };
                })
                .filter(Boolean)
                .sort((a, b) => a.x - b.x);

            // Filtro visual de neutral transitorio.
            // Solo afecta a la serie representada en la gráfica.
            const MAX_TRANSIENT_NEUTRAL_SECONDS = 0.06;

            for (let index = 1; index < data.length - 1;) {
                if (data[index].gear !== 0) {
                    index++;
                    continue;
                }

                const neutralStart = index;
                let neutralEnd = index;

                while (
                    neutralEnd + 1 < data.length &&
                    data[neutralEnd + 1].gear === 0
                ) {
                    neutralEnd++;
                }

                const previous = data[neutralStart - 1];
                const next = data[neutralEnd + 1];

                const startTime = data[neutralStart].time;
                const endTime = data[neutralEnd].time;

                const neutralDuration =
                    Number.isFinite(startTime) && Number.isFinite(endTime)
                        ? endTime - startTime
                        : Infinity;

                const isTransientShift =
                    next &&
                    previous.gear > 0 &&
                    next.gear > 0 &&
                    previous.gear !== next.gear &&
                    neutralDuration >= 0 &&
                    neutralDuration <= MAX_TRANSIENT_NEUTRAL_SECONDS;

                if (isTransientShift) {
                    for (
                        let neutralIndex = neutralStart;
                        neutralIndex <= neutralEnd;
                        neutralIndex++
                    ) {
                        data[neutralIndex].gear = previous.gear;
                    }
                }

                index = neutralEnd + 1;
            }

            if (data.length < 2) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '12px sans-serif';
                ctx.fillText('Sin datos de marchas suficientes', 8, 20);
                return;
            }

            const gears = data.map(point => point.gear);
            const minGear = Math.min(0, ...gears);
            const maxGear = Math.max(1, ...gears);

            const padding = { top: 10, right: 10, bottom: 24, left: 38 };
            const plotWidth = width - padding.left - padding.right;
            const plotHeight = height - padding.top - padding.bottom;

            if (plotWidth <= 0 || plotHeight <= 0) return;

            const xAt = distance =>
                padding.left
                + ((distance - graphViewport.start)
                    / (graphViewport.end - graphViewport.start))
                * plotWidth;

            const yAt = gear =>
                padding.top +
                (maxGear - gear) / Math.max(1, maxGear - minGear) * plotHeight;

            // Rejilla y etiquetas de marchas.
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(148, 163, 184, 0.2)';
            ctx.fillStyle = '#94a3b8';

            for (let gear = minGear; gear <= maxGear; gear++) {
                const y = yAt(gear);

                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(width - padding.right, y);
                ctx.stroke();

                ctx.fillText(gear === 0 ? 'N' : gear === -1 ? 'R' : String(gear),
                    padding.left - 6, y);
            }

            // Eje X: distancia normalizada.
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';

            [0, 25, 50, 75, 100].forEach(percent => {
                const distance = graphViewport.start
                    + (graphViewport.end - graphViewport.start) * percent / 100;

                const x = xAt(distance);

                ctx.fillText(
                    `${(distance * 100).toFixed(
                        graphViewport.end - graphViewport.start < 0.1 ? 1 : 0
                    )}%`,
                    x,
                    height - padding.bottom + 7
                );
            });

            // Curva escalonada: horizontal mientras se mantiene la marcha,
            // vertical únicamente cuando cambia.
            ctx.save();
            ctx.beginPath();
            ctx.rect(padding.left, padding.top, plotWidth, plotHeight);
            ctx.clip();
            ctx.beginPath();

            if (data.length > 0) {
                ctx.moveTo(xAt(data[0].x), yAt(data[0].gear));

                for (let index = 1; index < data.length; index++) {
                    const previous = data[index - 1];
                    const current = data[index];

                    const x = xAt(current.x);

                    // Mantener la marcha anterior hasta el punto de cambio.
                    ctx.lineTo(x, yAt(previous.gear));

                    // Dibujar el cambio vertical solo si cambia la marcha.
                    if (current.gear !== previous.gear) {
                        ctx.lineTo(x, yAt(current.gear));
                    }
                }
            }

            ctx.strokeStyle = getActiveLapColor();
            ctx.lineWidth = 0.5;
            ctx.lineJoin = 'round';
            ctx.stroke();
            ctx.restore();

            // Cursor compartido con Speed y el mapa.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentSample = validSamples[sampleIndex];
            const rawDistance = Number(currentSample?.lap_dist_pct);

            const currentDistance = Number.isFinite(rawDistance)
                ? (
                    rawDistance > 1 && rawDistance <= 100
                        ? rawDistance / 100
                        : rawDistance
                )
                : sampleIndex / Math.max(1, validSamples.length - 1);

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(Math.max(0, Math.min(1, currentDistance)));

                ctx.beginPath();
                ctx.moveTo(cursorX, padding.top);
                ctx.lineTo(cursorX, height - padding.bottom);
                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                const nearest = data.reduce((best, point) =>
                    Math.abs(point.index - playbackIndex) <
                    Math.abs(best.index - playbackIndex)
                        ? point
                        : best
                );

                ctx.beginPath();
                ctx.arc(cursorX, yAt(nearest.gear), 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                ctx.textAlign = 'right';
                ctx.textBaseline = 'top';
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = '#a78bfa';

                ctx.fillText(
                    `Marcha ${nearest.gear === 0 ? 'N' : nearest.gear === -1 ? 'R' : nearest.gear}`,
                    width - padding.right,
                    padding.top
                );
            }

        };


        // Gráficas independientes de acelerador y freno.
        const drawPedalGraph = (pedal, color) => {
            const graph = document.getElementById(
                `telemetry-graphics-${pedal}`
            );

            if (!graph || !graph.isConnected) return;

            const width = graph.clientWidth;
            const height = graph.clientHeight;

            if (width <= 0 || height <= 0) return;

            const dpr = window.devicePixelRatio || 1;

            graph.width = Math.round(width * dpr);
            graph.height = Math.round(height * dpr);

            const ctx = graph.getContext('2d');
            if (!ctx) return;

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const data = validSamples
                .map((sample, index) => {
                    const rawDistance = Number(sample.lap_dist_pct);
                    const rawValue = sample[pedal] == null
                        ? NaN
                        : Number(sample[pedal]);

                    if (
                        !Number.isFinite(rawDistance) ||
                        !Number.isFinite(rawValue)
                    ) {
                        return null;
                    }

                    const distance =
                        rawDistance > 1 && rawDistance <= 100
                            ? rawDistance / 100
                            : rawDistance;

                    // Admite señales expresadas como fracción o porcentaje.
                    const value = rawValue > 1 && rawValue <= 100
                        ? rawValue / 100
                        : rawValue;

                    if (
                        distance < 0 || distance > 1 ||
                        value < 0 || value > 1
                    ) {
                        return null;
                    }

                    return {
                        x: distance,
                        value,
                        index,
                    };
                })
                .filter(Boolean)
                .sort((a, b) => a.x - b.x);

            const padding = {
                top: 12,
                right: 12,
                bottom: 26,
                left: 38,
            };

            const plotWidth = width - padding.left - padding.right;
            const plotHeight = height - padding.top - padding.bottom;

            if (plotWidth <= 0 || plotHeight <= 0) return;

            const xAt = distance =>
                padding.left
                + ((distance - graphViewport.start)
                    / (graphViewport.end - graphViewport.start))
                * plotWidth;

            const yAt = value =>
                padding.top + (1 - value) * plotHeight;

            ctx.font = '10px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.lineWidth = 0.5;
            ctx.strokeStyle = 'rgba(148, 163, 184, 0.2)';
            ctx.fillStyle = '#94a3b8';

            // Escala vertical fija: 0–100 %.
            [0, 25, 50, 75, 100].forEach(percent => {
                const y = yAt(percent / 100);

                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(width - padding.right, y);
                ctx.stroke();

                ctx.fillText(
                    `${percent}%`,
                    padding.left - 6,
                    y
                );
            });

            // Eje horizontal: distancia de vuelta.
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';

            [0, 25, 50, 75, 100].forEach(percent => {
                const distance = graphViewport.start
                    + (graphViewport.end - graphViewport.start) * percent / 100;

                const x = xAt(distance);

                ctx.fillText(
                    `${(distance * 100).toFixed(
                        graphViewport.end - graphViewport.start < 0.1 ? 1 : 0
                    )}%`,
                    x,
                    height - padding.bottom + 7
                );
            });

            if (data.length < 2) {
                ctx.fillStyle = '#94a3b8';
                ctx.textAlign = 'left';
                ctx.fillText(
                    `Sin datos de ${pedal === 'throttle' ? 'acelerador' : 'freno'} suficientes`,
                    padding.left,
                    padding.top
                );
                return;
            }

            // Curva del pedal.
            ctx.save();
            ctx.beginPath();
            ctx.rect(padding.left, padding.top, plotWidth, plotHeight);
            ctx.clip();
            ctx.beginPath();

            data.forEach((point, index) => {
                const x = xAt(point.x);
                const y = yAt(point.value);

                if (index === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });

            ctx.strokeStyle = color;
            ctx.lineWidth = 1.5;
            ctx.lineJoin = 'round';
            ctx.stroke();
            ctx.restore();

            // Posición actual sincronizada con el reproductor.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentSample = validSamples[sampleIndex];
            const rawDistance = Number(currentSample?.lap_dist_pct);

            const currentDistance = Number.isFinite(rawDistance)
                ? (
                    rawDistance > 1 && rawDistance <= 100
                        ? rawDistance / 100
                        : rawDistance
                )
                : sampleIndex / Math.max(1, validSamples.length - 1);

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(
                    Math.max(0, Math.min(1, currentDistance))
                );

                const nearest = data.reduce((best, point) =>
                    Math.abs(point.index - playbackIndex) <
                    Math.abs(best.index - playbackIndex)
                        ? point
                        : best
                );

                ctx.beginPath();
                ctx.moveTo(cursorX, padding.top);
                ctx.lineTo(cursorX, height - padding.bottom);
                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 1;
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(cursorX, yAt(nearest.value), 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                ctx.textAlign = 'right';
                ctx.textBaseline = 'top';
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = color;
                ctx.fillText(
                    `${Math.round(nearest.value * 100)}%`,
                    width - padding.right,
                    padding.top
                );
            }

        };

        const drawThrottleGraph = () =>
            drawPedalGraph('throttle', '#22c55e');

        const drawBrakeGraph = () =>
            drawPedalGraph('brake', '#ef4444');


        const context = canvas.getContext('2d');

                // Silueta SVG del vehículo, vista desde arriba y con el frontal hacia arriba.
        const vehicleSvg = new Image();

                // Silueta del coche de la vuelta de referencia.
        const referenceVehicleSvg = new Image();
        let referenceVehicleColor = null;

        referenceVehicleSvg.onload = () => {
            drawOverlay();
        };

        function updateReferenceVehicleSvg() {
            const referenceColor = getActiveReferenceLapColor();

            if (
                referenceVehicleColor === referenceColor &&
                referenceVehicleSvg.complete &&
                referenceVehicleSvg.naturalWidth > 0
            ) {
                return;
            }

            const referenceColor = getActiveReferenceLapColor();
            const source = vehicleSvg.src;
                    let referenceVehicleSource = null;

            if (!source) {
                return;
            }

            if (
                referenceVehicleColor === referenceColor &&
                referenceVehicleSource === source
            ) {
                return;
            }

            const svgData = decodeURIComponent(
                vehicleSvg.src.substring(
                    vehicleSvg.src.indexOf(',') + 1
                )
            );

            const mainColor = getActiveLapColor();
            const mainShadow = shadeHexColor(mainColor, 0.78);
            const mainDark = shadeHexColor(mainColor, 0.55);

            const referenceShadow = shadeHexColor(referenceColor, 0.78);
            const referenceDark = shadeHexColor(referenceColor, 0.55);

            const referenceSvgData = svgData
                .replaceAll(mainShadow, referenceShadow)
                .replaceAll(mainDark, referenceDark)
                .replaceAll(mainColor, referenceColor);

            referenceVehicleColor = referenceColor;
            referenceVehicleSource = source;

            referenceVehicleSvg.src =
                'data:image/svg+xml;charset=utf-8,' +
                encodeURIComponent(referenceSvgData);
        }

        vehicleSvg.onload = () => {
            drawOverlay();
        };


@if($stint->car_id == 25)
@include('telemetry.partials.car.fclass')
@elseif($stint->car_id == 128)
@include('telemetry.partials.car.proto')
@endif

        // vehicleSvg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(`
        //     <svg xmlns="http://www.w3.org/2000/svg"
        //          width="64" height="112" viewBox="0 0 64 112">
        //         <path d="
        //             M20 5
        //             Q32 -1 44 5
        //             L51 21
        //             L57 42
        //             L55 83
        //             L48 104
        //             Q32 113 16 104
        //             L9 83
        //             L7 42
        //             L13 21 Z"
        //             fill="#f8fafc"
        //             stroke="#111827"
        //             stroke-width="3"/>
        //         <path d="
        //             M17 26
        //             Q32 19 47 26
        //             L44 43
        //             L20 43 Z"
        //             fill="#334155"
        //             stroke="#111827"
        //             stroke-width="2"/>
        //         <path d="
        //             M20 49 L44 49
        //             L46 75 L18 75 Z"
        //             fill="#64748b"
        //             stroke="#111827"
        //             stroke-width="2"/>
        //         <path d="M23 10 L41 10 L45 17 L19 17 Z"
        //             fill="#00e5ff"/>
        //         <path d="M20 88 L44 88 L40 101 L24 101 Z"
        //             fill="#334155"/>
        //         <path d="M10 27 L15 25 L18 39 L12 41 Z"
        //             fill="#111827"/>
        //         <path d="M54 27 L49 25 L46 39 L52 41 Z"
        //             fill="#111827"/>
        //         <path d="M10 76 L16 77 L14 91 L8 89 Z"
        //             fill="#111827"/>
        //         <path d="M54 76 L48 77 L50 91 L56 89 Z"
        //             fill="#111827"/>
        //     </svg>
        // `);

        if (!context || points.length < 2) {
            console.warn('[GPS overlay] No hay suficientes posiciones válidas.', {
                trackId,
                samples: samples.length,
                points: points.length,
            });
            return;
        }

        // Estado del reproductor. El progreso se expresa como índice fraccional de muestra.
        let playbackIndex = 0;
        let isPlaying = false;
        let animationFrameId = null;
        let previousFrameTime = null;
        let playbackSpeed = Number(speedSelect?.value ?? 1);

        const hasSessionTimes = points.every(point => point.sessionTime !== null);
        const sessionDuration = hasSessionTimes
            ? points[points.length - 1].sessionTime - points[0].sessionTime
            : 0;
        // Si el IBT no trae tiempos utilizables, reproducimos la vuelta en 60 segundos.
        const playbackDuration = sessionDuration > 0 ? sessionDuration : 60;

        // Estado de navegación independiente de la calibración del circuito.
        const view = { zoom: 1, panX: 0, panY: 0 };
        const MIN_ZOOM = 1;
        const MAX_ZOOM = 10;
        const ZOOM_STEP = 1.2;

        let isDragging = false;
        let dragStart = null;

        let isFollowEnabled = false;

        const applyContainerTransform = () => {
            container.style.transformOrigin = '0 0';
            container.style.transform =
                `translate(${view.panX}px, ${view.panY}px) scale(${view.zoom})`;
        };

        const updateFollowButton = () => {
            if (!followButton) return;

            followButton.setAttribute('aria-pressed', String(isFollowEnabled));
            followButton.textContent = isFollowEnabled
                ? 'Siguiendo coche'
                : 'Seguir coche';

            followButton.classList.toggle('bg-cyan-600', isFollowEnabled);
            followButton.classList.toggle('text-white', isFollowEnabled);
            followButton.classList.toggle('border-cyan-500', isFollowEnabled);
        };

        const zoomAt = (clientX, clientY, factor) => {
            const rect = viewport.getBoundingClientRect();
            const pointerX = clientX - rect.left;
            const pointerY = clientY - rect.top;

            const nextZoom = Math.max(
                MIN_ZOOM,
                Math.min(MAX_ZOOM, view.zoom * factor)
            );

            const ratio = nextZoom / view.zoom;

            // Mantiene bajo el cursor el punto del mapa que se está ampliando.
            view.panX = pointerX - (pointerX - view.panX) * ratio;
            view.panY = pointerY - (pointerY - view.panY) * ratio;
            view.zoom = nextZoom;

            if (view.zoom === MIN_ZOOM) {
                view.panX = 0;
                view.panY = 0;
            }

            updateView();
        };

        viewport.addEventListener('wheel', event => {
            event.preventDefault();

            zoomAt(
                event.clientX,
                event.clientY,
                event.deltaY < 0 ? ZOOM_STEP : 1 / ZOOM_STEP
            );
        }, { passive: false });

        viewport.addEventListener('pointerdown', event => {
            if (event.button !== 0 || isFollowEnabled) return;

            isDragging = true;
            dragStart = {
                x: event.clientX,
                y: event.clientY,
                panX: view.panX,
                panY: view.panY,
            };

            viewport.setPointerCapture(event.pointerId);
            viewport.style.cursor = 'grabbing';
        });

        viewport.addEventListener('pointermove', event => {
            if (!isDragging || !dragStart) return;

            view.panX = dragStart.panX + event.clientX - dragStart.x;
            view.panY = dragStart.panY + event.clientY - dragStart.y;
            updateView();
        });

        const endDrag = () => {
            isDragging = false;
            dragStart = null;
            viewport.style.cursor = 'grab';
        };

        viewport.addEventListener('pointerup', endDrag);
        viewport.addEventListener('pointercancel', endDrag);
        viewport.addEventListener('lostpointercapture', endDrag);

        zoomInButton?.addEventListener('click', () => {
            const rect = viewport.getBoundingClientRect();
            zoomAt(
                rect.left + rect.width / 2,
                rect.top + rect.height / 2,
                ZOOM_STEP
            );
        });

        zoomOutButton?.addEventListener('click', () => {
            const rect = viewport.getBoundingClientRect();
            zoomAt(
                rect.left + rect.width / 2,
                rect.top + rect.height / 2,
                1 / ZOOM_STEP
            );
        });

        resetButton?.addEventListener('click', () => {
            isFollowEnabled = false;
            updateFollowButton();
            view.zoom = 1;
            view.panX = 0;
            view.panY = 0;
            updateView();
        });

        // Proyección local del GPS a metros.
        const radians = Math.PI / 180;
        const originLat = points.reduce((sum, p) => sum + p.lat, 0) / points.length;
        const originLon = points.reduce((sum, p) => sum + p.lon, 0) / points.length;
        const cosLat = Math.cos(originLat * radians);

        const referenceProjected = referenceSamples
            .filter(sample =>
                sample?.position?.lat != null &&
                sample?.position?.lon != null &&
                Number.isFinite(Number(sample.position.lat)) &&
                Number.isFinite(Number(sample.position.lon))
            )
            .map(sample => ({
                x: (Number(sample.position.lon) - originLon)
                    * radians * 6371000 * cosLat,
                y: (Number(sample.position.lat) - originLat)
                    * radians * 6371000,
                lapDistPct: Number.isFinite(Number(sample.lap_dist_pct))
                    ? Number(sample.lap_dist_pct)
                    : null,
                sessionTime: Number.isFinite(Number(sample.session_time))
                    ? Number(sample.session_time)
                    : null,
            }));

        const projected = points.map(point => ({
            x: (point.lon - originLon) * radians * 6371000 * cosLat,
            y: (point.lat - originLat) * radians * 6371000,
        }));

        const minX = Math.min(...projected.map(p => p.x));
        const maxX = Math.max(...projected.map(p => p.x));
        const minY = Math.min(...projected.map(p => p.y));
        const maxY = Math.max(...projected.map(p => p.y));

        const rangeX = maxX - minX;
        const rangeY = maxY - minY;

        if (rangeX <= 0 || rangeY <= 0) {
            console.warn('[GPS overlay] Extensión GPS no válida.', trackId);
            return;
        }

        const drawOverlay = () => {
            const width = viewport.clientWidth;
            const height = viewport.clientHeight;

            if (width <= 0 || height <= 0) return;


            const dpr = window.devicePixelRatio || 1;

            // Renderizar el overlay con resolución extra para que conserve nitidez al aplicar zoom CSS.
            const renderScale = dpr * view.zoom;

            canvas.width = Math.round(width * renderScale);
            canvas.height = Math.round(height * renderScale);

            // Las coordenadas del trazado siguen expresadas en píxeles CSS.
            context.setTransform(renderScale, 0, 0, renderScale, 0, 0);
            context.clearRect(0, 0, width, height);


            const padding = Math.min(width, height) * 0.08;
            const fitScale = Math.min(
                Math.max(1, width - padding * 2) / rangeX,
                Math.max(1, height - padding * 2) / rangeY
            );

            const centerX = (minX + maxX) / 2;
            const centerY = (minY + maxY) / 2;
            const angle = calibration.rotation_deg * radians;
            const cosAngle = Math.cos(angle);
            const sinAngle = Math.sin(angle);

            const drawPoints = projected.map(point => {
                const x = (point.x - centerX) * fitScale;
                const y = -(point.y - centerY) * fitScale;

                const rotatedX = x * cosAngle - y * sinAngle;
                const rotatedY = x * sinAngle + y * cosAngle;

                return {
                    x: width / 2
                        + rotatedX * calibration.scale
                        + width * calibration.offset_x_pct / 100,
                    y: height / 2
                        + rotatedY * calibration.scale
                        + height * calibration.offset_y_pct / 100,
                };
            });

            context.lineJoin = 'round';
            context.lineCap = 'round';
            context.imageSmoothingEnabled = true;

            // REFERENCE LAP



            const referenceDrawPoints = referenceProjected.map(point => {
                const x = (point.x - centerX) * fitScale;
                const y = -(point.y - centerY) * fitScale;

                const rotatedX = x * cosAngle - y * sinAngle;
                const rotatedY = x * sinAngle + y * cosAngle;

                return {
                    x: width / 2
                        + rotatedX * calibration.scale
                        + width * calibration.offset_x_pct / 100,
                    y: height / 2
                        + rotatedY * calibration.scale
                        + height * calibration.offset_y_pct / 100,
                    lapDistPct: point.lapDistPct,
                    sessionTime: point.sessionTime,
                };
            });

            // Trazada completa de la vuelta de referencia.

            // Trazada de referencia: solo mostrar el tramo reproducido.
            if (referenceDrawPoints.length > 1) {
                const mainIndex = Math.min(
                    Math.floor(playbackIndex),
                    points.length - 1
                );

                const nextMainIndex = Math.min(
                    mainIndex + 1,
                    points.length - 1
                );

                const mainFraction = playbackIndex - mainIndex;

                const startTime = Number(points[mainIndex].sessionTime);
                const endTime = Number(points[nextMainIndex].sessionTime);

                let refProgress;

                if (
                    Number.isFinite(startTime) &&
                    Number.isFinite(endTime)
                ) {
                    const elapsed = startTime
                        + (endTime - startTime) * mainFraction;

                    const firstTime = Number(points[0].sessionTime);
                    const lastTime = Number(points[points.length - 1].sessionTime);

                    refProgress = Number.isFinite(firstTime) &&
                        Number.isFinite(lastTime) &&
                        lastTime > firstTime
                        ? (elapsed - firstTime) / (lastTime - firstTime)
                        : playbackIndex / Math.max(1, points.length - 1);
                } else {
                    refProgress = playbackIndex
                        / Math.max(1, points.length - 1);
                }

                const visibleCount = Math.max(
                    1,
                    Math.min(
                        referenceDrawPoints.length - 1,
                        Math.floor(
                            refProgress * (referenceDrawPoints.length - 1)
                        )
                    )
                );

                context.save();
                context.beginPath();
                context.lineWidth = 0.2;
                context.strokeStyle = getActiveReferenceLapColor();

                context.moveTo(
                    referenceDrawPoints[0].x,
                    referenceDrawPoints[0].y
                );

                for (let index = 1; index <= visibleCount; index++) {
                    context.lineTo(
                        referenceDrawPoints[index].x,
                        referenceDrawPoints[index].y
                    );
                }

                context.stroke();
                context.restore();
            }

            // Trazada completa de la vuelta de referencia.

            // // Trazada completa atenuada como referencia.
            // context.lineWidth = 1.25;
            // context.strokeStyle = 'rgba(0, 229, 255, 0.22)';
            // context.beginPath();
            // drawPoints.forEach((point, index) => {
            //     if (index === 0) context.moveTo(point.x, point.y);
            //     else context.lineTo(point.x, point.y);
            // });
            // context.stroke();


            // Vehículo de referencia sincronizado por tiempo transcurrido.
            if (referenceDrawPoints.length > 1) {
                const mainIndex = Math.min(
                    Math.floor(playbackIndex),
                    points.length - 1
                );

                const nextMainIndex = Math.min(
                    mainIndex + 1,
                    points.length - 1
                );

                const mainFraction = playbackIndex - mainIndex;
                const mainStartTime = points[mainIndex].sessionTime;
                const mainEndTime = points[nextMainIndex].sessionTime;
                const mainLapStartTime = points[0].sessionTime;

                const hasTimeData =
                    Number.isFinite(mainStartTime) &&
                    Number.isFinite(mainEndTime) &&
                    Number.isFinite(mainLapStartTime) &&
                    referenceDrawPoints.every(point =>
                        Number.isFinite(point.sessionTime)
                    );

                let refX;
                let refY;
                let refAngle;

                if (hasTimeData) {
                    // Tiempo transcurrido desde el inicio de la vuelta principal.
                    const targetElapsed =
                        mainStartTime +
                        (mainEndTime - mainStartTime) * mainFraction -
                        mainLapStartTime;

                    const referenceStartTime =
                        referenceDrawPoints[0].sessionTime;

                    const referenceEndTime =
                        referenceDrawPoints[
                            referenceDrawPoints.length - 1
                        ].sessionTime;

                    const targetReferenceTime =
                        referenceStartTime + targetElapsed;

                    // Buscar el intervalo temporal correspondiente en la referencia.
                    let refIndex = 0;

                    if (targetReferenceTime >= referenceEndTime) {
                        refIndex = referenceDrawPoints.length - 2;
                    } else {
                        for (
                            let index = 0;
                            index < referenceDrawPoints.length - 1;
                            index++
                        ) {
                            const a = referenceDrawPoints[index].sessionTime;
                            const b = referenceDrawPoints[index + 1].sessionTime;

                            if (
                                targetReferenceTime >= a &&
                                targetReferenceTime <= b
                            ) {
                                refIndex = index;
                                break;
                            }
                        }
                    }

                    const refStart = referenceDrawPoints[refIndex];
                    const refEnd = referenceDrawPoints[refIndex + 1];

                    const timeDelta =
                        refEnd.sessionTime - refStart.sessionTime;

                    const refFraction = targetReferenceTime >= referenceEndTime
                        ? 1
                        : timeDelta > 0
                            ? Math.max(
                                0,
                                Math.min(
                                    1,
                                    (targetReferenceTime - refStart.sessionTime) /
                                        timeDelta
                                )
                            )
                            : 0;

                    refX = refStart.x + (refEnd.x - refStart.x) * refFraction;
                    refY = refStart.y + (refEnd.y - refStart.y) * refFraction;

                    refAngle = Math.atan2(
                        refEnd.y - refStart.y,
                        refEnd.x - refStart.x
                    );
                } else {
                    // Sin tiempos válidos, usar progreso normalizado como alternativa.
                    const progress =
                        playbackIndex / Math.max(1, points.length - 1);

                    const refProgress = Math.max(
                        0,
                        Math.min(
                            referenceDrawPoints.length - 1,
                            progress * (referenceDrawPoints.length - 1)
                        )
                    );

                    const refIndex = Math.min(
                        Math.floor(refProgress),
                        referenceDrawPoints.length - 2
                    );

                    const fraction = refProgress - refIndex;
                    const refStart = referenceDrawPoints[refIndex];
                    const refEnd = referenceDrawPoints[refIndex + 1];

                    refX = refStart.x + (refEnd.x - refStart.x) * fraction;
                    refY = refStart.y + (refEnd.y - refStart.y) * fraction;

                    refAngle = Math.atan2(
                        refEnd.y - refStart.y,
                        refEnd.x - refStart.x
                    );
                }

                                // Coche de referencia: misma silueta que el coche principal.
                updateReferenceVehicleSvg();

                if (
                    referenceVehicleSvg.complete &&
                    referenceVehicleSvg.naturalWidth > 0
                ) {
                    context.save();

                    context.translate(refX, refY);
                    context.rotate(refAngle - Math.PI / 2);

                    context.drawImage(
                        referenceVehicleSvg,
                        -1,
                        -3.5,
                        2,
                        4
                    );

                    context.restore();
                }
            }


            // Trazada de la vuelta con su color asignado.
            const completedIndex = Math.floor(playbackIndex);
            context.lineWidth = 0.1;
            context.strokeStyle = getActiveLapColor();
            context.beginPath();
            context.moveTo(drawPoints[0].x, drawPoints[0].y);
            for (let index = 1; index <= completedIndex && index < drawPoints.length; index++) {
                context.lineTo(drawPoints[index].x, drawPoints[index].y);
            }

            // Interpolación entre muestras para que el marcador avance suavemente.
            const nextIndex = Math.min(completedIndex + 1, drawPoints.length - 1);
            const fraction = playbackIndex - completedIndex;
            const currentPoint = {
                x: drawPoints[completedIndex].x
                    + (drawPoints[nextIndex].x - drawPoints[completedIndex].x) * fraction,
                y: drawPoints[completedIndex].y
                    + (drawPoints[nextIndex].y - drawPoints[completedIndex].y) * fraction,
            };
            // Desplaza mapa y trazada juntos, manteniendo fija la orientación.
            if (isFollowEnabled) {
                view.panX = width / 2 - currentPoint.x * view.zoom;
                view.panY = height / 2 - currentPoint.y * view.zoom;
                applyContainerTransform();
            }
            if (playbackIndex > completedIndex && nextIndex !== completedIndex) {
                context.lineTo(currentPoint.x, currentPoint.y);
            }
            context.stroke();

// Capa independiente: referencias de sectores.
if (Array.isArray(sectorSegments) && sectorSegments.length > 0) {
    context.save();

    const markerStyle = window.IRTM_SECTOR_MARKER_STYLE;

    context.font =
        `${markerStyle.fontWeight} ${markerStyle.fontSize}px ` +
        markerStyle.fontFamily;

    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.lineCap = 'round';

    const validPctIndices = points
        .map((point, index) => ({
            index,
            pct: point.lapDistPct,
        }))
        .filter(item =>
            Number.isFinite(item.pct) &&
            item.pct >= 0 &&
            item.pct <= 1
        );

    sectorSegments.forEach((sector, sectorIndex) => {
        const targetPct = Number(sector.start_pct);

        if (
            !Number.isFinite(targetPct) ||
            targetPct < 0 ||
            targetPct >= 1
        ) {
            return;
        }

        if (validPctIndices.length === 0) {
            console.warn(
                '[Sectores] No hay muestras con lap_dist_pct válido.'
            );
            return;
        }

        // Buscar la muestra más cercana al porcentaje del sector.
        let nearest = validPctIndices[0];

        for (const candidate of validPctIndices) {
            if (
                Math.abs(candidate.pct - targetPct) <
                Math.abs(nearest.pct - targetPct)
            ) {
                nearest = candidate;
            }
        }

        const index = nearest.index;

        // Necesitamos un punto anterior y otro posterior para
        // calcular la dirección local de la trazada.
        if (index <= 0 || index >= drawPoints.length - 1) {
            return;
        }

        const previous = drawPoints[index - 1];
        const current = drawPoints[index];
        const next = drawPoints[index + 1];

        // Tangente local centrada en la muestra seleccionada.
        const dx = next.x - previous.x;
        const dy = next.y - previous.y;
        const length = Math.hypot(dx, dy);

        if (length < 0.001) return;

        // Perpendicular a la dirección de marcha.
        const normalX = -dy / length;
        const normalY = dx / length;

        // Longitud adaptativa de la marca de sector.
            const previousDistance = Math.hypot(
                current.x - previous.x,
                current.y - previous.y
            );

            const nextDistance = Math.hypot(
                next.x - current.x,
                next.y - current.y
            );

            // Estimación local de la separación entre muestras.
            const localSpacing = (previousDistance + nextDistance) / 2;

                        // Configuración independiente de la línea transversal.
            const sectorLine = markerStyle.sectorLine;

            const halfLength = Math.max(
                4,
                Math.min(40, sectorLine.halfLength)
            );

            const centerX = current.x + normalX * sectorLine.offset;
            const centerY = current.y + normalY * sectorLine.offset;

            const x1 = centerX - normalX * halfLength;
            const y1 = centerY - normalY * halfLength;
            const x2 = centerX + normalX * halfLength;
            const y2 = centerY + normalY * halfLength;

        const label = `S${sectorIndex}`;

        context.setLineDash([1, 0.25]);

        // Contorno de la línea transversal.
        context.lineWidth = sectorLine.outlineWidth;
        context.strokeStyle = sectorLine.outlineColor;
        context.beginPath();
        context.moveTo(x1, y1);
        context.lineTo(x2, y2);
        context.stroke();

        // Línea interior.
        context.lineWidth = sectorLine.lineWidth;
        context.strokeStyle = sectorLine.lineColor;
        context.beginPath();
        context.moveTo(x1, y1);
        context.lineTo(x2, y2);
        context.stroke();

        context.setLineDash([]);


        // Etiqueta del sector: posición independiente de la línea.
        // Compensamos el zoom solo en la etiqueta, no en la referencia transversal.
        const labelX = current.x + normalX * markerStyle.labelOffset;
        const labelY = current.y + normalY * markerStyle.labelOffset;

        context.save();

        // Mantiene constante el tamaño visual de la etiqueta al ampliar el mapa.
        context.translate(labelX, labelY);
        context.scale(1 / view.zoom, 1 / view.zoom);

        const boxWidth =
            context.measureText(label).width + markerStyle.paddingX * 2;

        const boxHeight = markerStyle.fontSize + markerStyle.paddingY * 2;

        const boxX = -boxWidth / 2;
        const boxY = -boxHeight / 2;


        // Aumentar el tamaño visual de las etiquetas un 20 %.
        const labelScale = 1.8;

        context.scale(labelScale, labelScale);


        // Fondo opaco.
        context.fillStyle = markerStyle.backgroundColor;
        context.fillRect(boxX, boxY, boxWidth, boxHeight);

        // Borde.
        context.strokeStyle = markerStyle.borderColor;
        context.lineWidth = markerStyle.borderWidth;
        context.strokeRect(boxX, boxY, boxWidth, boxHeight);

        // Texto.
        context.fillStyle = markerStyle.textColor;
        context.fillText(label, 0, 0);

        context.restore();


        console.debug('[Sectores] Referencia dibujada', {
            sector: label,
            targetPct,
            actualPct: nearest.pct,
            sampleIndex: index,
        });
    });


    // =====================================================
    // META AJEDREZADA
    // =====================================================
    const finishLine = markerStyle.finishLine;

    if (finishLine?.enabled && validPctIndices.length > 0) {
        // Buscar una muestra interior próxima al inicio/fin de vuelta.
        const candidates = validPctIndices.filter(item =>
            item.index > 0 && item.index < drawPoints.length - 1
        );

        if (candidates.length > 0) {
            const finishPoint = candidates.reduce((best, item) => {
                const distance = Math.min(item.pct, 2 - item.pct);
                const bestDistance = Math.min(best.pct, 1.5 - best.pct);

                return distance < bestDistance ? item : best;
            });

            const index = finishPoint.index;
            const previous = drawPoints[index - 1];
            const current = drawPoints[index];
            const next = drawPoints[index + 1];

            const dx = next.x - previous.x;
            const dy = next.y - previous.y;
            const length = Math.hypot(dx, dy);

            if (length > 0.001) {
                // Dirección de marcha y perpendicular a la pista.
                const tangentX = dx / length;
                const tangentY = dy / length;
                const normalX = -tangentY;
                const normalY = tangentX;

                const centerX =
                    current.x + normalX * finishLine.offset;
                const centerY =
                    current.y + normalY * finishLine.offset;

                const halfLength = Math.max(
                    1,
                    Number(finishLine.halfLength) || 18
                );

                const squareSize = Math.max(
                    1,
                    Number(finishLine.squareSize) || 6
                );

                const bandThickness = Math.max(
                    1,
                    Number(finishLine.bandThickness) || 8
                );

                context.save();
                context.translate(centerX, centerY);

                // El eje X local sigue la trazada.
                context.rotate(Math.atan2(dy, dx));

                // Dos líneas contiguas con patrones inversos.
                // Usa squareSize ya calculado arriba desde finishLine.
                const totalLength = halfLength * 2;
                const tileCount = Math.max(
                    1,
                    Math.ceil(totalLength / squareSize)
                );
                const tileLength = totalLength / tileCount;
                const rowWidth = bandThickness;

                for (let row = 0; row < 2; row++) {
                    for (let tile = 0; tile < tileCount; tile++) {
                        const isLight = (tile + row) % 2 === 0;

                        context.fillStyle = isLight
                            ? finishLine.lightColor
                            : finishLine.darkColor;

                        context.fillRect(
                            -rowWidth + row * rowWidth,
                            -halfLength + tile * tileLength,
                            rowWidth,
                            tileLength
                        );
                    }
                }

                // Contorno exterior de las dos líneas juntas.
                if ((Number(finishLine.borderWidth) || 0) > 0) {
                    context.strokeStyle = finishLine.borderColor;
                    context.lineWidth = finishLine.borderWidth;

                    context.strokeRect(
                        -rowWidth,
                        -halfLength,
                        rowWidth * 2,
                        halfLength * 2
                    );
                }

                context.restore();

                console.debug('[Meta] Línea ajedrezada dibujada', {
                    lapDistPct: finishPoint.pct,
                    sampleIndex: index,
                    halfLength,
                    bandThickness,
                    squareSize
                });
            }
        } else {
            console.warn(
                '[Meta] No hay muestras interiores válidas para dibujarla.'
            );
        }
    }


    context.restore();
}

            // Inicio, final y posición actual.
            context.fillStyle = '#22c55e';
            context.beginPath();
            context.arc(drawPoints[0].x, drawPoints[0].y, 0.2, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = '#f97316';
            context.beginPath();
            context.arc(drawPoints[drawPoints.length - 1].x, drawPoints[drawPoints.length - 1].y, 0.2, 0, Math.PI * 2);
            context.fill();

                        // Vehículo orientado según la dirección de avance en el mapa.
            const headingStart = drawPoints[
                Math.min(completedIndex, drawPoints.length - 2)
            ];

            const headingEnd = drawPoints[
                Math.min(completedIndex + 1, drawPoints.length - 1)
            ];

            const headingAngle = Math.atan2(
                headingEnd.y - headingStart.y,
                headingEnd.x - headingStart.x
            );

            // El frontal del SVG apunta hacia arriba.
            const vehicleAngle = headingAngle - Math.PI / 2;

            if (vehicleSvg.complete && vehicleSvg.naturalWidth > 0) {
                context.save();

                context.translate(currentPoint.x, currentPoint.y);
                context.rotate(vehicleAngle);

                context.drawImage(
                    vehicleSvg,
                    -1,
                    -3.5,
                    2,
                    4
                );

                context.restore();
            } else {
                // Marcador provisional mientras se carga el SVG.
                context.fillStyle = '#ffffff';
                context.beginPath();
                context.arc(currentPoint.x, currentPoint.y, 4, 0, Math.PI * 2);
                context.fill();
            }
        };

        // Indicadores de telemetría sincronizados con el reproductor.
        const gearDisplay = document.getElementById('telemetry-current-gear');
        const speedDisplay = document.getElementById('telemetry-current-speed');
        const lapTimeDisplay = document.getElementById('telemetry-current-lap-time');
        const steeringRotor = document.getElementById('telemetry-steering-wheel-rotor');
        const steeringValue = document.getElementById('telemetry-current-steering-value');

        const updateSteeringDisplay = (currentSample, nextSample, fraction) => {

            console.log('[Steering]', {
                sampleIndex: Math.floor(playbackIndex),
                raw: currentSample?.steering_angle,
                type: typeof currentSample?.steering_angle,
                next: nextSample?.steering_angle,
                keys: Object.keys(currentSample ?? {})
            });

            const first = Number(currentSample?.steering_angle);
            const second = Number(nextSample?.steering_angle);

            const radians = Number.isFinite(first)
                ? (
                    Number.isFinite(second)
                        ? first + (second - first) * fraction
                        : first
                )
                : null;

            if (radians === null) {
                if (steeringValue) steeringValue.textContent = '—°';
                return;
            }

            // SteeringWheelAngle está expresado en radianes.
            const degrees = radians * (180 / Math.PI);

            if (steeringRotor) {
                steeringRotor.setAttribute(
                    'transform',
                    `rotate(${-degrees} 32 32)`
                );
            }

            if (steeringValue) {
                const rounded = Math.round(degrees);
                steeringValue.textContent = `${rounded > 0 ? '+' : ''}${rounded}°`;
            }
        };

        // Pedales verticales, en el orden físico: embrague, freno y acelerador.
        const pedalDisplays = {
            clutch: {
                bar: document.getElementById('telemetry-current-clutch-bar'),
                value: document.getElementById('telemetry-current-clutch-value'),
            },
            brake: {
                bar: document.getElementById('telemetry-current-brake-bar'),
                value: document.getElementById('telemetry-current-brake-value'),
            },
            throttle: {
                bar: document.getElementById('telemetry-current-throttle-bar'),
                value: document.getElementById('telemetry-current-throttle-value'),
            },
        };


        const miniPedalDisplays = {
            clutch: {
                bar: document.getElementById('telemetry-mini-clutch-bar'),
                value: document.getElementById('telemetry-mini-clutch-value'),
            },
            brake: {
                bar: document.getElementById('telemetry-mini-brake-bar'),
                value: document.getElementById('telemetry-mini-brake-value'),
            },
            throttle: {
                bar: document.getElementById('telemetry-mini-throttle-bar'),
                value: document.getElementById('telemetry-mini-throttle-value'),
            },
        };


        const updatePedalDisplay = (pedal, currentSample, nextSample, fraction) => {
            const display = pedalDisplays[pedal];
            if (!display) return;

            const first = Number(currentSample?.[pedal]);
            const second = Number(nextSample?.[pedal]);
            const raw = Number.isFinite(first)
                ? (Number.isFinite(second) ? first + (second - first) * fraction : first)
                : null;
            const displayRaw = raw === null
                ? null
                : (pedal === 'clutch' ? 1 - raw : raw);

            const percent = displayRaw === null
                ? null
                : Math.round(Math.min(1, Math.max(0, displayRaw)) * 100);


            const miniDisplay = miniPedalDisplays[pedal];

            if (miniDisplay) {
                if (miniDisplay.bar) {
                    miniDisplay.bar.style.width = `${percent ?? 0}%`;
                    miniDisplay.bar.title =
                        percent === null ? 'Sin datos' : `${percent}%`;
                }

                if (miniDisplay.value) {
                    miniDisplay.value.textContent =
                        percent === null ? '—' : `${percent}%`;
                }
            }



            if (display.bar) {
                display.bar.style.height = `${percent ?? 0}%`;
                const meter = display.bar.parentElement;
                if (meter) meter.setAttribute('aria-valuenow', String(percent ?? 0));
                display.bar.title = percent === null ? 'Sin datos' : `${percent}%`;
            }
            if (display.value) display.value.textContent = percent === null ? '—' : `${percent}%`;


        };



        const formatLapTime = milliseconds => {
            const safeMs = Math.max(0, Math.floor(milliseconds));
            const minutes = Math.floor(safeMs / 60000);
            const seconds = Math.floor((safeMs % 60000) / 1000);
            const ms = safeMs % 1000;

            return `${String(minutes).padStart(2, '0')}:` +
                `${String(seconds).padStart(2, '0')}.` +
                `${String(ms).padStart(3, '0')}`;
        };

        const updateTelemetryIndicators = () => {
            if (!validSamples.length) return;

            const index = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const nextIndex = Math.min(index + 1, validSamples.length - 1);
            const fraction = playbackIndex - index;

            const currentSample = validSamples[index];
            const nextSample = validSamples[nextIndex];

            updatePedalDisplay('clutch', currentSample, nextSample, fraction);
            updatePedalDisplay('brake', currentSample, nextSample, fraction);
            updatePedalDisplay('throttle', currentSample, nextSample, fraction);
            updateSteeringDisplay(currentSample, nextSample, fraction);

            // La marcha es discreta: no interpolamos entre engranajes.
            if (gearDisplay) {
                gearDisplay.textContent =
                    currentSample.gear != null
                        ? String(currentSample.gear)
                        : '—';
            }

            // La velocidad se interpola para acompañar el movimiento del coche.
            const speed1 = Number(currentSample.speed_kph);
            const speed2 = Number(nextSample.speed_kph);

            if (speedDisplay) {
                speedDisplay.textContent =
                    Number.isFinite(speed1) && Number.isFinite(speed2)
                        ? (
                            speed1 + (speed2 - speed1) * fraction
                        ).toFixed(0)
                        : '—';
            }

            // Tiempo transcurrido desde la primera muestra de la vuelta.
            const time1 = Number(currentSample.session_time);
            const time2 = Number(nextSample.session_time);
            const startTime = Number(validSamples[0].session_time);

            if (
                lapTimeDisplay &&
                Number.isFinite(time1) &&
                Number.isFinite(time2) &&
                Number.isFinite(startTime)
            ) {
                const currentSessionTime =
                    time1 + (time2 - time1) * fraction;

                lapTimeDisplay.textContent = formatLapTime(
                    (currentSessionTime - startTime) * 1000
                );
            } else if (lapTimeDisplay) {
                lapTimeDisplay.textContent = '—';
            }
        };


        const updatePlaybackControls = () => {
            const percent =
                playbackIndex / Math.max(1, points.length - 1) * 100;

            if (progressSlider) progressSlider.value = String(percent);

            if (progressLabel) {
                progressLabel.textContent = `${Math.round(percent)}%`;
            }

            updateTelemetryIndicators();
            updateGraphFollow();
            drawSpeedGraph();
            drawGearsGraph();
            drawThrottleGraph();
            drawBrakeGraph();
        };




        const stopPlayback = () => {
            isPlaying = false;
            previousFrameTime = null;
            if (animationFrameId !== null) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        };

        const playbackFrame = timestamp => {
            if (!isPlaying) return;

            if (previousFrameTime !== null) {
                const deltaSeconds = Math.min((timestamp - previousFrameTime) / 1000, 0.1);
                playbackIndex += deltaSeconds * playbackSpeed
                    * (points.length - 1) / playbackDuration;
                playbackIndex = Math.min(points.length - 1, playbackIndex);
                drawOverlay();
                updatePlaybackControls();
            }

            previousFrameTime = timestamp;
            if (playbackIndex >= points.length - 1) {
                stopPlayback();
                return;
            }
            animationFrameId = requestAnimationFrame(playbackFrame);
        };

        const startPlayback = () => {
            if (playbackIndex >= points.length - 1) playbackIndex = 0;
            if (isPlaying) return;
            isPlaying = true;
            previousFrameTime = null;
            animationFrameId = requestAnimationFrame(playbackFrame);
        };

        playButton?.addEventListener('click', startPlayback);
        pauseButton?.addEventListener('click', stopPlayback);
        playbackResetButton?.addEventListener('click', () => {
            stopPlayback();
            playbackIndex = 0;
            drawOverlay();
            updatePlaybackControls();
        });
        progressSlider?.addEventListener('input', () => {
            stopPlayback();
            const percent = Number(progressSlider.value) / 100;
            playbackIndex = percent * (points.length - 1);
            drawOverlay();
            updatePlaybackControls();
        });
        speedSelect?.addEventListener('change', () => {
            playbackSpeed = Math.max(0.1, Number(speedSelect.value) || 1);
        });

        followButton?.addEventListener('click', () => {
            isFollowEnabled = !isFollowEnabled;
            updateFollowButton();

            if (isFollowEnabled) {
                drawOverlay();
            }
        });





        const updateView = () => {
            applyContainerTransform();

            const label = `${Math.round(view.zoom * 100)}%`;

            if (zoomLabel) {
                zoomLabel.value = label;
                zoomLabel.textContent = label;
            }

            drawOverlay();
        };




        const observer = new ResizeObserver(drawOverlay);
        observer.observe(viewport);

        mapObject.addEventListener('load', drawOverlay);

        updateFollowButton();
        drawOverlay();
        updateView();
        updatePlaybackControls();


        const observeGraphics = () => {
            const graphCanvases = [
                ['telemetry-graphics-speed', drawSpeedGraph],
                ['telemetry-graphics-gears', drawGearsGraph],
                ['telemetry-graphics-throttle', drawThrottleGraph],
                ['telemetry-graphics-brake', drawBrakeGraph],
            ];

            graphCanvases.forEach(([id, draw]) => {
                const graph = document.getElementById(id);

                if (graph?.isConnected) {
                    graphicsResizeObserver.observe(graph);
                    bindGraphNavigation(graph);
                    draw();
                }
            });
        };

        const graphicsResizeObserver = new ResizeObserver(() => {
            drawSpeedGraph();
            drawGearsGraph();
            drawThrottleGraph();
            drawBrakeGraph();
        });

        const boundGraphCanvases = new WeakSet();

        const bindGraphNavigation = canvas => {
            if (boundGraphCanvases.has(canvas)) return;

            boundGraphCanvases.add(canvas);

            let dragStart = null;
            let initialViewport = null;

            canvas.style.touchAction = 'pan-y';
            canvas.style.cursor = 'crosshair';

            canvas.addEventListener('wheel', event => {
                event.preventDefault();

                const distance = graphDistanceAtPointer(
                    canvas,
                    event.clientX
                );

                zoomGraphAt(
                    distance,
                    event.deltaY < 0
                        ? 1 / GRAPH_ZOOM_FACTOR
                        : GRAPH_ZOOM_FACTOR
                );
            }, { passive: false });

            canvas.addEventListener('pointerdown', event => {
                if (event.button !== 0) return;

                dragStart = {
                    x: event.clientX,
                    distance: graphDistanceAtPointer(canvas, event.clientX),
                    pan: event.shiftKey,
                };

                initialViewport = {
                    start: graphViewport.start,
                    end: graphViewport.end,
                };

                canvas.setPointerCapture(event.pointerId);
            });

            canvas.addEventListener('pointerup', event => {
                if (!dragStart) return;

                const start = dragStart;
                const initial = initialViewport;
                const endDistance = graphDistanceAtPointer(
                    canvas,
                    event.clientX
                );

                const pixelDistance = Math.abs(event.clientX - start.x);

                if (start.pan) {
                    // Shift + arrastre: desplazar el intervalo visible.
                    const rect = canvas.getBoundingClientRect();
                    const plotWidth = rect.width - 38 - 10;

                    if (plotWidth > 0) {
                        const distanceDelta =
                            (start.x - event.clientX)
                            / plotWidth
                            * (initial.end - initial.start);

                        graphViewport.start = initial.start + distanceDelta;
                        graphViewport.end = initial.end + distanceDelta;

                        clampGraphViewport();
                        redrawAllGraphs();
                    }
                } else if (pixelDistance >= 8) {
                    // Arrastre normal: ampliar la selección.
                    const selectionStart = Math.min(
                        start.distance,
                        endDistance
                    );

                    const selectionEnd = Math.max(
                        start.distance,
                        endDistance
                    );

                    if (selectionEnd - selectionStart >= GRAPH_MIN_RANGE) {
                        graphViewport.start = selectionStart;
                        graphViewport.end = selectionEnd;

                        clampGraphViewport();
                        redrawAllGraphs();
                    }
                }

                dragStart = null;
                initialViewport = null;
            });

            canvas.addEventListener('pointercancel', () => {
                dragStart = null;
                initialViewport = null;
            });
        };


        observeGraphics();

        document.addEventListener('click', event => {
            const button = event.target.closest('button');

            if (
                button &&
                /Speed|Gears|Throttle|Brake/.test(button.textContent)
            ) {
                requestAnimationFrame(observeGraphics);
            }
        });


        document.addEventListener('click', event => {
            if (event.target.closest('button') && event.target.closest('button').textContent.includes('Speed')) {
                requestAnimationFrame(() => {
                    drawSpeedGraph();
                });
            }
        });


        console.info('[GPS overlay]', {
            trackId,
            validPoints: points.length,
            calibration,
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLapOverlay, { once: true });
    } else {
        initLapOverlay();
    }
})();
</script>
