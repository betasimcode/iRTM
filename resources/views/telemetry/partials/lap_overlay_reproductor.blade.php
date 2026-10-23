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
        const referenceStintId = @js($referenceStintId ?? null);
        const selectedLapDurationSeconds = @js($selectedLap['lap_time'] ?? null);

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

        // Colores manuales asociados a una vuelta; se conservan entre recargas.
        const manualLapColors = new Map();
        const LAP_COLOR_STORAGE_KEY = 'irtm.telemetry.manualLapColors';

        try {
            const savedColors = JSON.parse(
                window.localStorage.getItem(LAP_COLOR_STORAGE_KEY) ?? '{}'
            );

            Object.entries(savedColors).forEach(([key, color]) => {
                if (typeof color === 'string' && /^#[0-9a-f]{6}$/i.test(color)) {
                    manualLapColors.set(key, color);
                }
            });
        } catch (error) {
            // La telemetría sigue funcionando aunque el navegador bloquee storage.
            console.warn('[LapOverlay] No se pudieron recuperar colores guardados.');
        }

        const saveManualLapColor = (key, color) => {
            manualLapColors.set(key, color);

            try {
                window.localStorage.setItem(
                    LAP_COLOR_STORAGE_KEY,
                    JSON.stringify(Object.fromEntries(manualLapColors))
                );
            } catch (error) {
                console.warn('[LapOverlay] No se pudo persistir el color seleccionado.');
            }
        };

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

            const key = `reference:${referenceStintId ?? ''}:${referenceLapNumber}`;

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

                        saveManualLapColor(key, preset.value);
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
        const referenceColorHost = document.getElementById(
            'reference-lap-color-host'
        );

        if (referenceColorHost) {
            const colorContainer = referenceColorHost;
            colorContainer.style.position = 'relative';

            const referenceColorTrigger = document.createElement('button');
            referenceColorTrigger.type = 'button';
            referenceColorTrigger.id = 'reference-lap-color-trigger';
            referenceColorTrigger.className =
                'flex h-9 w-9 items-center justify-center rounded-lg ' +
                'border border-[var(--border)]';
            referenceColorTrigger.title =
                `Color de referencia: ${lapColorPresets.find(
                    color => color.value === getActiveReferenceLapColor()
                )?.name ?? 'Personalizado'}`;
            referenceColorTrigger.setAttribute(
                'aria-label',
                'Cambiar color de la vuelta de referencia'
            );
            referenceColorTrigger.disabled = referenceLapNumber == null || referenceSamples.length === 0;
            referenceColorTrigger.classList.toggle('opacity-40', referenceColorTrigger.disabled);

            const referenceColorPreview = document.createElement('span');
            referenceColorPreview.style.cssText = [
                'display:inline-block',
                'width:12px',
                'height:12px',
                'border-radius:50%',
                'background:' + getActiveReferenceLapColor(),
                'border:1px solid rgba(148,163,184,.6)'
            ].join(';');

            referenceColorTrigger.appendChild(referenceColorPreview);

            const referenceColorMenu = document.createElement('div');
            referenceColorMenu.id = 'reference-lap-color-menu';
            referenceColorMenu.className =
                'absolute right-0 top-full z-50 mt-2 hidden ' +
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

                    saveManualLapColor(
                        `reference:${referenceStintId ?? ''}:${referenceLapNumber}`,
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
            sessionTime: sample.session_time != null && Number.isFinite(Number(sample.session_time))
                ? Number(sample.session_time)
                : null,
            lapDistPct: sample.lap_dist_pct != null && Number.isFinite(Number(sample.lap_dist_pct))
                ? Number(sample.lap_dist_pct)
                : null,
        }));

        // El modo se conserva en el navegador, pero el temporal sigue siendo el predeterminado.
        const REFERENCE_MODE_STORAGE_KEY = 'irtm.telemetry.referenceMode';
        let referenceMode = 'temporal';
        try {
            referenceMode = window.localStorage.getItem(REFERENCE_MODE_STORAGE_KEY) === 'spatial'
                ? 'spatial'
                : 'temporal';
        } catch (error) {
            // El modo predeterminado continúa disponible si storage está bloqueado.
        }

        let spatialPct = 0;

        const normalizeLapPct = value => {
            if (value === null || value === undefined || value === '') return null;
            const raw = Number(value);
            if (!Number.isFinite(raw)) return null;
            const normalized = raw > 1 && raw <= 100 ? raw / 100 : raw;
            return normalized >= 0 && normalized <= 1 ? normalized : null;
        };

        const getSeriesDuration = series => {
            if (!Array.isArray(series) || series.length < 2) return 0;
            if (series[0]?.session_time == null || series[series.length - 1]?.session_time == null) return 0;
            const first = Number(series[0]?.session_time);
            const last = Number(series[series.length - 1]?.session_time);
            return Number.isFinite(first) && Number.isFinite(last) && last > first
                ? last - first
                : 0;
        };

        const getGraphDomainEnd = (mode = referenceMode) => {
            if (mode === 'spatial') return 1;

            // El cronómetro de la comparativa temporal termina con la vuelta principal.
            // Si faltan tiempos válidos, el reproductor usa su fallback de 60 segundos.
            const metadataDuration = Number(selectedLapDurationSeconds);
            const primaryDuration = getSeriesDuration(validSamples);
            return Number.isFinite(metadataDuration) && metadataDuration > 0
                ? metadataDuration
                : primaryDuration > 0 ? primaryDuration : 60;
        };

        const getGraphCoordinate = (sample, index, series) => {
            if (referenceMode === 'spatial') {
                const pct = normalizeLapPct(sample?.lap_dist_pct);
                return pct ?? index / Math.max(1, series.length - 1);
            }

            if (sample?.session_time == null || series[0]?.session_time == null) {
                return index / Math.max(1, series.length - 1)
                    * Math.max(getSeriesDuration(series), getGraphDomainEnd('temporal'));
            }
            const time = Number(sample?.session_time);
            const startTime = Number(series[0]?.session_time);
            if (Number.isFinite(time) && Number.isFinite(startTime)) {
                return Math.max(0, time - startTime);
            }

            return index / Math.max(1, series.length - 1)
                * Math.max(getSeriesDuration(series), getGraphDomainEnd('temporal'));
        };

        const formatGraphCoordinate = coordinate => {
            if (referenceMode === 'spatial') {
                const range = graphViewport.end - graphViewport.start;
                return `${(coordinate * 100).toFixed(range < 0.1 ? 1 : 0)}%`;
            }

            const milliseconds = Math.max(0, Math.round(coordinate * 1000));
            const minutes = Math.floor(milliseconds / 60000);
            const seconds = Math.floor((milliseconds % 60000) / 1000);
            const ms = milliseconds % 1000;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}.${String(ms).padStart(3, '0')}`;
        };

        const getIndexAtPct = (series, targetPct) => {
            if (!Array.isArray(series) || series.length < 2) return 0;
            const target = Math.max(0, Math.min(1, targetPct));
            const getPct = (sample, index) =>
                normalizeLapPct(sample?.lap_dist_pct ?? sample?.lapDistPct) ?? index / (series.length - 1);

            let previousIndex = 0;
            let previousPct = getPct(series[0], 0);

            for (let index = 1; index < series.length; index++) {
                const currentPct = getPct(series[index], index);
                if (currentPct >= target) {
                    const span = currentPct - previousPct;
                    const fraction = span > 0
                        ? (target - previousPct) / span
                        : 0;
                    return previousIndex + Math.max(0, Math.min(1, fraction));
                }
                previousIndex = index;
                previousPct = currentPct;
            }

            return series.length - 1;
        };

        const getCurrentGraphCoordinate = () => {
            if (referenceMode === 'spatial') return spatialPct;

            const lastIndex = Math.max(0, validSamples.length - 1);
            const index = Math.max(0, Math.min(lastIndex, playbackIndex));
            const lower = Math.floor(index);
            const upper = Math.min(lower + 1, lastIndex);
            const fraction = index - lower;
            const first = getGraphCoordinate(validSamples[lower] ?? {}, lower, validSamples);
            const second = getGraphCoordinate(validSamples[upper] ?? {}, upper, validSamples);
            return first + (second - first) * fraction;
        };

        // Invierte el eje visible y resuelve la muestra fraccional más cercana
        // para que el deslizador temporal represente segundos reales, no índices.
        const getIndexAtGraphCoordinate = coordinate => {
            if (referenceMode === 'spatial') {
                return getIndexAtPct(points, coordinate);
            }

            if (validSamples.length < 2) return 0;

            const target = Math.max(
                0,
                Math.min(getGraphDomainEnd('temporal'), Number(coordinate) || 0)
            );

            let previousX = getGraphCoordinate(validSamples[0], 0, validSamples);

            for (let index = 1; index < validSamples.length; index++) {
                const currentX = getGraphCoordinate(
                    validSamples[index],
                    index,
                    validSamples
                );

                if (target <= currentX) {
                    const span = currentX - previousX;
                    const fraction = span > 0
                        ? Math.max(0, Math.min(1, (target - previousX) / span))
                        : 0;

                    return index - 1 + fraction;
                }

                previousX = currentX;
            }

            return validSamples.length - 1;
        };

        const graphViewport = {
            start: 0,
            end: getGraphDomainEnd(referenceMode),
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
            const distance = getGraphCoordinate(sample, sampleIndex, validSamples);

            if (!Number.isFinite(distance)) return;

            const range = graphViewport.end - graphViewport.start;

            // Con la vuelta completa visible no hace falta desplazar la gráfica.
            if (range >= getGraphDomainEnd() * 0.999) return;

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

        const buildGraphData = (series, field, normalize = value => Number(value), validate = () => true) =>
            series.map((sample, index) => {
                const value = normalize(sample?.[field]);
                const x = getGraphCoordinate(sample, index, series);

                if (!Number.isFinite(x) || !Number.isFinite(value) || !validate(value, sample)) {
                    return null;
                }

                return { x, value, index, sample };
            }).filter(Boolean).sort((a, b) => a.x - b.x);

        const drawReferenceSeries = (ctx, data, xAt, yAt, plot, stepped = false) => {
            if (data.length < 2 || referenceLapNumber == null) return;

            ctx.save();
            ctx.beginPath();
            ctx.rect(plot.left, plot.top, plot.width, plot.height);
            ctx.clip();
            ctx.beginPath();
            ctx.moveTo(xAt(data[0].x), yAt(data[0].value));

            for (let index = 1; index < data.length; index++) {
                const previous = data[index - 1];
                const current = data[index];
                const x = xAt(current.x);

                if (stepped) {
                    ctx.lineTo(x, yAt(previous.value));
                    if (current.value !== previous.value) {
                        ctx.lineTo(x, yAt(current.value));
                    }
                } else {
                    ctx.lineTo(x, yAt(current.value));
                }
            }

            ctx.strokeStyle = getActiveReferenceLapColor();
            ctx.lineWidth = 1.5;
            ctx.lineJoin = 'round';
            ctx.stroke();

            const cursorXValue = getCurrentGraphCoordinate();
            if (
                Number.isFinite(cursorXValue) &&
                cursorXValue >= graphViewport.start &&
                cursorXValue <= graphViewport.end
            ) {
                const nearest = data.reduce((best, point) =>
                    Math.abs(point.x - cursorXValue) < Math.abs(best.x - cursorXValue)
                        ? point
                        : best
                );

                ctx.beginPath();
                ctx.arc(xAt(cursorXValue), yAt(nearest.value), 3, 0, Math.PI * 2);
                ctx.fillStyle = getActiveReferenceLapColor();
                ctx.fill();
            }

            ctx.restore();
        };

        // Selecciona el valor de cada vuelta en la misma coordenada del análisis.
        const findNearestGraphPoint = (series, coordinate) => {
            if (
                !Array.isArray(series) ||
                series.length === 0 ||
                !Number.isFinite(coordinate)
            ) {
                return null;
            }

            return series.reduce((best, point) =>
                Math.abs(point.x - coordinate) < Math.abs(best.x - coordinate)
                    ? point
                    : best
            );
        };

        // Indicadores actuales: vuelta principal, diferencia y vuelta de referencia.
        const drawCurrentComparison = (
            ctx,
            width,
            top,
            mainPoint,
            referencePoint,
            options = {}
        ) => {
            if (!mainPoint) return;

            const {
                valueOf = point => point.value,
                formatValue = value => String(value),
                scale = 1,
                deltaSuffix = '',
                invertDeltaColor = false,
                canCompare = () => true,
                compactFormatValue = value => String(value),
                compactDeltaSuffix = deltaSuffix,
            } = options;

            const mainValue = Number(valueOf(mainPoint));
            if (!Number.isFinite(mainValue)) return;

            const compact = width < 420;
            const mainText = compact
                ? compactFormatValue(mainValue)
                : formatValue(mainValue);

            ctx.save();
            ctx.font = `bold ${compact ? 10 : 11}px sans-serif`;
            ctx.textBaseline = 'top';
            ctx.textAlign = 'right';
            ctx.fillStyle = getActiveLapColor();

            if (!referencePoint || referenceLapNumber == null) {
                ctx.fillText(mainText, width - 10, top);
                ctx.restore();
                return;
            }

            const referenceValue = Number(valueOf(referencePoint));
            if (!Number.isFinite(referenceValue)) {
                ctx.fillText(mainText, width - 10, top);
                ctx.restore();
                return;
            }

            const mainRounded = Math.round(mainValue * scale);
            const referenceRounded = Math.round(referenceValue * scale);
            const difference = mainRounded - referenceRounded;
            const mainX = compact ? width * 0.58 : width - 200;
            const differenceX = compact ? width * 0.79 : width - 105;

            ctx.fillText(mainText, mainX, top);

            if (difference !== 0 && canCompare(mainValue, referenceValue)) {
                const isPositive = difference > 0;
                const useGreen = invertDeltaColor ? !isPositive : isPositive;
                const suffix = compact ? compactDeltaSuffix : deltaSuffix;

                ctx.textAlign = 'center';
                ctx.fillStyle = useGreen ? '#22c55e' : '#ef4444';
                ctx.fillText(
                    `${difference > 0 ? '+' : ''}${difference}${suffix}`,
                    differenceX,
                    top
                );
            }

            ctx.textAlign = 'right';
            ctx.fillStyle = getActiveReferenceLapColor();
            ctx.fillText(
                compact ? compactFormatValue(referenceValue) : formatValue(referenceValue),
                width - 10,
                top
            );

            ctx.restore();
        };

        const clampGraphViewport = () => {
            const range = graphViewport.end - graphViewport.start;

            const domainEnd = getGraphDomainEnd();
            if (range >= domainEnd) {
                graphViewport.start = 0;
                graphViewport.end = domainEnd;
                return;
            }

            if (graphViewport.start < 0) {
                graphViewport.end -= graphViewport.start;
                graphViewport.start = 0;
            }

            if (graphViewport.end > domainEnd) {
                graphViewport.start -= graphViewport.end - domainEnd;
                graphViewport.end = domainEnd;
            }

            graphViewport.start = Math.max(0, graphViewport.start);
            graphViewport.end = Math.min(domainEnd, graphViewport.end);
        };

        const resetGraphZoom = () => {
            graphViewport.start = 0;
            graphViewport.end = getGraphDomainEnd();
            redrawAllGraphs();
        };

        window.addEventListener(
            'telemetry-graph-reset-zoom',
            resetGraphZoom
        );

        const zoomGraphAt = (distance, factor) => {
            const oldRange = graphViewport.end - graphViewport.start;
            const domainEnd = getGraphDomainEnd();
            const newRange = Math.max(
                domainEnd * 0.005,
                Math.min(domainEnd, oldRange * factor)
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

            const data = buildGraphData(
                validSamples,
                'speed_kph',
                value => Number(value),
                value => value >= 0
            ).map(point => ({ ...point, speed: point.value }));

            const referenceData = buildGraphData(
                referenceSamples,
                'speed_kph',
                value => Number(value),
                value => value >= 0
            ).map(point => ({ ...point, speed: point.value }));

            if (data.length < 2) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '12px sans-serif';
                ctx.fillText('Sin datos de velocidad suficientes', 8, 20);
                return;
            }

            const maxSpeed = Math.max(
                50,
                ...data.map(point => point.speed),
                ...referenceData.map(point => point.speed)
            );
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
                    formatGraphCoordinate(distance),
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

            drawReferenceSeries(ctx, referenceData, xAt, yAt, {
                left: padding.left, top: padding.top,
                width: plotWidth, height: plotHeight,
            });

            // Cursor asociado al índice de reproducción actual.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentDistance = getCurrentGraphCoordinate();

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(
                    Math.max(graphViewport.start, Math.min(getGraphDomainEnd(), currentDistance))
                );

                ctx.beginPath();
                ctx.moveTo(cursorX, padding.top);
                ctx.lineTo(cursorX, height - padding.bottom);
                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 0.5;
                ctx.stroke();

                const nearest = findNearestGraphPoint(data, currentDistance);
                const referenceNearest = findNearestGraphPoint(referenceData, currentDistance);

                ctx.beginPath();
                ctx.arc(cursorX, yAt(nearest.speed), 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                drawCurrentComparison(
                    ctx,
                    width,
                    padding.top,
                    nearest,
                    referenceNearest,
                    {
                        valueOf: point => point.speed,
                        formatValue: value => `${Math.round(value)} km/h`,
                        compactFormatValue: value => String(Math.round(value)),
                        scale: 1,
                        deltaSuffix: ' km/h',
                        compactDeltaSuffix: '',
                    }
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

            const data = buildGraphData(
                validSamples,
                'gear',
                value => Number(value),
                (value, sample) => sample.gear != null && value >= -1 && value <= 10
            ).map(point => ({ ...point, gear: point.value, time: Number(point.sample.session_time) }));

            const referenceData = buildGraphData(
                referenceSamples,
                'gear',
                value => Number(value),
                (value, sample) => sample.gear != null && value >= -1 && value <= 10
            ).map(point => ({ ...point, gear: point.value, time: Number(point.sample.session_time) }));

            // Filtro visual de neutral transitorio.
            // Se aplica por separado a ambas series y solo afecta a la representación.
            const MAX_TRANSIENT_NEUTRAL_SECONDS = 0.06;

            const filterTransientNeutral = series => {
                for (let index = 1; index < series.length - 1;) {
                    if (series[index].gear !== 0) {
                        index++;
                        continue;
                    }

                    const neutralStart = index;
                    let neutralEnd = index;

                    while (
                        neutralEnd + 1 < series.length &&
                        series[neutralEnd + 1].gear === 0
                    ) {
                        neutralEnd++;
                    }

                    const previous = series[neutralStart - 1];
                    const next = series[neutralEnd + 1];

                    const startTime = series[neutralStart].time;
                    const endTime = series[neutralEnd].time;

                    const neutralDuration =
                        Number.isFinite(startTime) && Number.isFinite(endTime)
                            ? endTime - startTime
                            : Infinity;

                    // Solo corregimos el N breve producido durante un cambio
                    // entre dos marchas reales distintas; no ocultamos una N sostenida.
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
                            series[neutralIndex].gear = previous.gear;
                        }
                    }

                    index = neutralEnd + 1;
                }
            };

            filterTransientNeutral(data);
            filterTransientNeutral(referenceData);

            if (data.length < 2) {
                ctx.fillStyle = '#94a3b8';
                ctx.font = '12px sans-serif';
                ctx.fillText('Sin datos de marchas suficientes', 8, 20);
                return;
            }

            const gears = [...data, ...referenceData].map(point => point.gear);
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
                    formatGraphCoordinate(distance),
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

            drawReferenceSeries(ctx, referenceData, xAt, yAt, {
                left: padding.left, top: padding.top,
                width: plotWidth, height: plotHeight,
            }, true);

            // Cursor compartido con Speed y el mapa.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentSample = validSamples[sampleIndex];
            const currentDistance = getCurrentGraphCoordinate();

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(Math.max(graphViewport.start, Math.min(getGraphDomainEnd(), currentDistance)));

                ctx.beginPath();
                ctx.moveTo(cursorX, padding.top);
                ctx.lineTo(cursorX, height - padding.bottom);
                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                const nearest = findNearestGraphPoint(data, currentDistance);
                const referenceNearest = findNearestGraphPoint(referenceData, currentDistance);

                ctx.beginPath();
                ctx.arc(cursorX, yAt(nearest.gear), 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#f59e0b';
                ctx.fill();

                const formatGear = value =>
                    value === 0 ? 'N' : value === -1 ? 'R' : String(value);

                drawCurrentComparison(
                    ctx,
                    width,
                    padding.top,
                    nearest,
                    referenceNearest,
                    {
                        valueOf: point => point.gear,
                        formatValue: value => `Marcha ${formatGear(value)}`,
                        compactFormatValue: value => formatGear(value),
                        scale: 1,
                        canCompare: (main, reference) => main > 0 && reference > 0,
                    }
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

            const normalizePedal = raw => {
                const value = Number(raw);
                return value > 1 && value <= 100 ? value / 100 : value;
            };

            const data = buildGraphData(
                validSamples,
                pedal,
                normalizePedal,
                value => value >= 0 && value <= 1
            );

            const referenceData = buildGraphData(
                referenceSamples,
                pedal,
                normalizePedal,
                value => value >= 0 && value <= 1
            );

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
                    formatGraphCoordinate(distance),
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

            drawReferenceSeries(ctx, referenceData, xAt, yAt, {
                left: padding.left, top: padding.top,
                width: plotWidth, height: plotHeight,
            });

            // Posición actual sincronizada con el reproductor.
            const sampleIndex = Math.min(
                Math.floor(playbackIndex),
                validSamples.length - 1
            );

            const currentSample = validSamples[sampleIndex];
            const currentDistance = getCurrentGraphCoordinate();

            if (
                Number.isFinite(currentDistance) &&
                currentDistance >= graphViewport.start &&
                currentDistance <= graphViewport.end
            ) {
                const cursorX = xAt(
                    Math.max(graphViewport.start, Math.min(getGraphDomainEnd(), currentDistance))
                );

                const nearest = findNearestGraphPoint(data, currentDistance);
                const referenceNearest = findNearestGraphPoint(referenceData, currentDistance);

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

                drawCurrentComparison(
                    ctx,
                    width,
                    padding.top,
                    nearest,
                    referenceNearest,
                    {
                        valueOf: point => point.value,
                        formatValue: value => `${Math.round(value * 100)}%`,
                        compactFormatValue: value => `${Math.round(value * 100)}%`,
                        scale: 100,
                        deltaSuffix: '%',
                        invertDeltaColor: pedal === 'brake',
                    }
                );
            }

        };

        const drawThrottleGraph = () =>
            drawPedalGraph('throttle', '#22c55e');

        const drawBrakeGraph = () =>
            drawPedalGraph('brake', '#ef4444');


        const context = canvas.getContext('2d');

                // Silueta SVG del vehículo, vista desde arriba y con el frontal hacia arriba.
                // Siluetas SVG del coche principal y de referencia.
        const vehicleSvg = new Image();
        const referenceVehicleSvg = new Image();

        let referenceVehicleColor = null;
        let referenceVehicleSource = null;

        vehicleSvg.onload = () => {
            updateReferenceVehicleSvg();
            drawOverlay();
        };

        referenceVehicleSvg.onload = () => {
            drawOverlay();
        };

        function updateReferenceVehicleSvg() {
            const source = vehicleSvg.src;
            const referenceColor = getActiveReferenceLapColor();

            if (!source) {
                return;
            }

            if (
                referenceVehicleSource === source &&
                referenceVehicleColor === referenceColor
            ) {
                return;
            }

            const commaIndex = source.indexOf(',');

            if (commaIndex === -1) {
                return;
            }

            let svgData;

            try {
                svgData = decodeURIComponent(
                    source.substring(commaIndex + 1)
                );
            } catch (error) {
                console.error(
                    '[LapOverlay] No se pudo decodificar el SVG del coche.',
                    error
                );
                return;
            }

            const mainColor = getActiveLapColor();
            const mainShadow = shadeHexColor(mainColor, 0.78);
            const mainDark = shadeHexColor(mainColor, 0.55);

            const referenceShadow = shadeHexColor(referenceColor, 0.78);
            const referenceDark = shadeHexColor(referenceColor, 0.55);

            const referenceSvgData = svgData
                .replaceAll(mainShadow, referenceShadow)
                .replaceAll(mainDark, referenceDark)
                .replaceAll(mainColor, referenceColor);

            referenceVehicleSource = source;
            referenceVehicleColor = referenceColor;

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

        if (referenceMode === 'spatial') {
            spatialPct = normalizeLapPct(points[0]?.lapDistPct) ?? 0;
            playbackIndex = getIndexAtPct(points, spatialPct);
        }

        const hasSessionTimes = points.every(point => point.sessionTime !== null);
        const sessionDuration = hasSessionTimes
            ? points[points.length - 1].sessionTime - points[0].sessionTime
            : 0;
        // Preferir la duración de vuelta validada en el resumen; recurrir a
        // las muestras solo si el resumen no dispone de un tiempo utilizable.
        const metadataPlaybackDuration = Number(selectedLapDurationSeconds);
        const playbackDuration = Number.isFinite(metadataPlaybackDuration) && metadataPlaybackDuration > 0
            ? metadataPlaybackDuration
            : sessionDuration > 0 ? sessionDuration : 60;

        // Estado de navegación independiente de la calibración del circuito.
        const view = { zoom: 1, panX: 0, panY: 0 };
        const MIN_ZOOM = 1;
        const MAX_ZOOM = 10;
        const ZOOM_STEP = 1.2;

        let isDragging = false;
        let isSpatialDragging = false;
        let dragStart = null;

        let isFollowEnabled = false;
        let mainMapDrawPoints = [];
        let referenceMapDrawPoints = [];

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

            if (referenceMode === 'spatial' && !event.shiftKey) {
                isSpatialDragging = true;
                viewport.setPointerCapture(event.pointerId);
                viewport.style.cursor = 'crosshair';
                updateSpatialPositionFromMap(event);
                return;
            }

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
            if (isDragging && dragStart) {
                view.panX = dragStart.panX + event.clientX - dragStart.x;
                view.panY = dragStart.panY + event.clientY - dragStart.y;
                updateView();
                return;
            }

            if (
                referenceMode === 'spatial' &&
                (event.buttons === 0 || isSpatialDragging)
            ) {
                updateSpatialPositionFromMap(event);
            }
        });

        const endDrag = () => {
            isDragging = false;
            isSpatialDragging = false;
            dragStart = null;
            viewport.style.cursor = referenceMode === 'spatial'
                ? 'crosshair'
                : 'grab';
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
                lapDistPct: sample.lap_dist_pct != null && Number.isFinite(Number(sample.lap_dist_pct))
                    ? Number(sample.lap_dist_pct)
                    : null,
                sessionTime: sample.session_time != null && Number.isFinite(Number(sample.session_time))
                    ? Number(sample.session_time)
                    : null,
            }));

        const projected = points.map(point => ({
            x: (point.lon - originLon) * radians * 6371000 * cosLat,
            y: (point.lat - originLat) * radians * 6371000,
        }));

        // Mantener la calibración del mapa basada en la vuelta principal.
        // La referencia utiliza la misma transformación, sin desplazar el mapa.
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

        const updateSpatialPositionFromMap = event => {
            if (referenceMode !== 'spatial') return;

            const rect = viewport.getBoundingClientRect();
            const pointerX = event.clientX - rect.left;
            const pointerY = event.clientY - rect.top;
            let nearest = null;

            const inspectPoints = points => {
                points.forEach(point => {
                    const screenX = point.x * view.zoom + view.panX;
                    const screenY = point.y * view.zoom + view.panY;
                    const distance = (screenX - pointerX) ** 2 + (screenY - pointerY) ** 2;

                    if (nearest === null || distance < nearest.distance) {
                        nearest = {
                            distance,
                            pct: normalizeLapPct(point.lapDistPct),
                        };
                    }
                });
            };

            inspectPoints(mainMapDrawPoints);
            inspectPoints(referenceMapDrawPoints);

            // Evitar saltos si el puntero está lejos de las trazadas.
            const maxCursorDistance = 26;
            if (
                nearest &&
                nearest.pct !== null &&
                nearest.distance <= maxCursorDistance ** 2
            ) {
                setSpatialPosition(nearest.pct);
            }
        };

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

            const drawPoints = projected.map((point, index) => {
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
                    lapDistPct: normalizeLapPct(points[index]?.lapDistPct)
                        ?? index / Math.max(1, projected.length - 1),
                };
            });

            mainMapDrawPoints = drawPoints;

            context.lineJoin = 'round';
            context.lineCap = 'round';
            context.imageSmoothingEnabled = true;

            // REFERENCE LAP



            const referenceDrawPoints = referenceProjected.map((point, index) => {
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
                    lapDistPct: normalizeLapPct(point.lapDistPct)
                        ?? index / Math.max(1, referenceProjected.length - 1),
                    sessionTime: point.sessionTime,
                };
            });

            referenceMapDrawPoints = referenceDrawPoints;

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

                const visibleCount = referenceMode === 'spatial'
                    ? referenceDrawPoints.length - 1
                    : Math.max(
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

                if (referenceMode === 'spatial') {
                    const refProgress = getIndexAtPct(referenceDrawPoints, spatialPct);
                    const refIndex = Math.min(
                        Math.floor(refProgress),
                        referenceDrawPoints.length - 2
                    );
                    const refFraction = refProgress - refIndex;
                    const refStart = referenceDrawPoints[refIndex];
                    const refEnd = referenceDrawPoints[refIndex + 1];

                    refX = refStart.x + (refEnd.x - refStart.x) * refFraction;
                    refY = refStart.y + (refEnd.y - refStart.y) * refFraction;
                    refAngle = Math.atan2(
                        refEnd.y - refStart.y,
                        refEnd.x - refStart.x
                    );
                } else if (hasTimeData) {
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
            const traceEndIndex = referenceMode === 'spatial'
                ? drawPoints.length - 1
                : completedIndex;
            context.lineWidth = 0.1;
            context.strokeStyle = getActiveLapColor();
            context.beginPath();
            context.moveTo(drawPoints[0].x, drawPoints[0].y);
            for (let index = 1; index <= traceEndIndex && index < drawPoints.length; index++) {
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
            if (
                referenceMode === 'temporal' &&
                playbackIndex > completedIndex &&
                nextIndex !== completedIndex
            ) {
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
            const progressPercent = referenceMode === 'spatial'
                ? spatialPct * 100
                : getCurrentGraphCoordinate()
                    / Math.max(0.001, getGraphDomainEnd('temporal')) * 100;

            if (progressSlider) {
                progressSlider.value = String(progressPercent);
            }

            if (progressLabel) {
                progressLabel.textContent = referenceMode === 'spatial'
                    ? `${(spatialPct * 100).toFixed(1)}%`
                    : formatGraphCoordinate(getCurrentGraphCoordinate());
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

        const setSpatialPosition = pct => {
            if (referenceMode !== 'spatial') return;

            spatialPct = Math.max(0, Math.min(1, Number(pct) || 0));
            stopPlayback();
            playbackIndex = getIndexAtPct(points, spatialPct);

            // Si el cursor llega desde el mapa fuera de la zona ampliada,
            // desplazar la ventana de las gráficas conservando su nivel de zoom.
            const visibleRange = graphViewport.end - graphViewport.start;
            if (
                visibleRange < getGraphDomainEnd() * 0.999 &&
                (spatialPct < graphViewport.start || spatialPct > graphViewport.end)
            ) {
                graphViewport.start = spatialPct - visibleRange / 2;
                graphViewport.end = graphViewport.start + visibleRange;
                clampGraphViewport();
            }

            drawOverlay();
            updatePlaybackControls();
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
            if (referenceMode === 'spatial') return;
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
            const percent = Number(progressSlider.value) / 100;

            if (referenceMode === 'spatial') {
                setSpatialPosition(percent);
                return;
            }

            stopPlayback();
            playbackIndex = getIndexAtGraphCoordinate(
                percent * getGraphDomainEnd('temporal')
            );
            drawOverlay();
            updatePlaybackControls();
        });
        speedSelect?.addEventListener('change', () => {
            playbackSpeed = Math.max(0.1, Number(speedSelect.value) || 1);
        });

        const temporalModeButton = document.getElementById('reference-mode-temporal');
        const spatialModeButton = document.getElementById('reference-mode-spatial');
        const referenceModeDescription = document.getElementById('reference-mode-description');
        const playbackDescription = document.getElementById('telemetry-playback-description');
        const temporalControls = document.getElementById('telemetry-playback-time-controls');
        const speedControl = document.getElementById('telemetry-playback-speed-control');
        const progressStartLabel = document.getElementById('telemetry-lap-progress-start');
        const progressEndLabel = document.getElementById('telemetry-lap-progress-end');

        const applyModeButtonStyle = (button, active) => {
            if (!button) return;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('bg-cyan-600', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-cyan-500', active);
            button.classList.toggle('border-[var(--border)]', !active);
            button.classList.toggle('text-[var(--text)]', !active);
        };

        const updateReferenceModeUI = () => {
            const spatial = referenceMode === 'spatial';

            applyModeButtonStyle(temporalModeButton, !spatial);
            applyModeButtonStyle(spatialModeButton, spatial);

            if (referenceModeDescription) {
                referenceModeDescription.textContent = spatial
                    ? 'Sincronización por posición: 0–100 % del recorrido.'
                    : 'Sincronización por tiempo transcurrido.';
            }

            if (playbackDescription) {
                playbackDescription.textContent = spatial
                    ? 'Explora el mismo punto de pista en ambas vueltas.'
                    : 'Reproduce la trazada GPS siguiendo el tiempo de vuelta.';
            }

            if (temporalControls) temporalControls.style.display = spatial ? 'none' : '';
            if (speedControl) speedControl.style.display = spatial ? 'none' : '';
            viewport.style.cursor = spatial ? 'crosshair' : 'grab';

            if (progressSlider) {
                progressSlider.setAttribute(
                    'aria-label',
                    spatial
                        ? 'Posición de análisis en el recorrido, de 0 a 100 %'
                        : 'Posición temporal de reproducción en la vuelta'
                );
            }

            if (progressStartLabel) {
                progressStartLabel.textContent = spatial
                    ? '0 % · Salida/meta'
                    : '00:00.000 · Salida/meta';
            }

            if (progressEndLabel) {
                progressEndLabel.textContent = spatial
                    ? '100 % · Meta'
                    : `${formatLapTime(playbackDuration * 1000)} · Fin de vuelta`;
            }

            if (progressLabel) {
                progressLabel.textContent = spatial
                    ? `${(spatialPct * 100).toFixed(1)}%`
                    : formatGraphCoordinate(getCurrentGraphCoordinate());
            }
        };

        const changeReferenceMode = mode => {
            if (mode !== 'temporal' && mode !== 'spatial') return;

            stopPlayback();

            if (mode === 'spatial' && referenceMode !== 'spatial') {
                const mainIndex = Math.min(
                    Math.floor(playbackIndex),
                    Math.max(0, points.length - 1)
                );
                const nextIndex = Math.min(mainIndex + 1, points.length - 1);
                const fraction = playbackIndex - mainIndex;
                const currentPct = normalizeLapPct(points[mainIndex]?.lapDistPct);
                const nextPct = normalizeLapPct(points[nextIndex]?.lapDistPct);

                spatialPct = currentPct !== null && nextPct !== null
                    ? currentPct + (nextPct - currentPct) * fraction
                    : mainIndex / Math.max(1, points.length - 1);

                playbackIndex = getIndexAtPct(points, spatialPct);
            } else if (mode === 'temporal' && referenceMode === 'spatial') {
                playbackIndex = getIndexAtPct(points, spatialPct);
            }

            referenceMode = mode;
            if (referenceMode === 'spatial') {
                isFollowEnabled = false;
            }

            try {
                window.localStorage.setItem(REFERENCE_MODE_STORAGE_KEY, mode);
            } catch (error) {
                // El cambio funciona durante esta visita aunque no se pueda guardar.
            }

            graphViewport.start = 0;
            graphViewport.end = getGraphDomainEnd(mode);
            clampGraphViewport();

            updateReferenceModeUI();
            updateFollowButton();
            drawOverlay();
            updatePlaybackControls();
            redrawAllGraphs();
        };

        temporalModeButton?.addEventListener('click', () => {
            changeReferenceMode('temporal');
        });

        spatialModeButton?.addEventListener('click', () => {
            changeReferenceMode('spatial');
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

        updateReferenceModeUI();
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

            canvas.addEventListener('pointermove', event => {
                if (referenceMode !== 'spatial' || event.buttons !== 0) return;
                setSpatialPosition(graphDistanceAtPointer(canvas, event.clientX));
            });

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

                    if (selectionEnd - selectionStart >= getGraphDomainEnd() * GRAPH_MIN_RANGE) {
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
