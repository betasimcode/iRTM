
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


        // Presets de color para las trazadas.
        const lapColorPresets = [
            { name: 'Cian', value: '#0000ff' },
            { name: 'Orange', value: '#FFB454' },
            { name: 'Violet', value: '#A78BFA' },
            { name: 'Green', value: '#00ff00' },
            { name: 'Red', value: '#ff0000' },
            { name: 'Yellow', value: '#FACC15' },
        ];

        const lapSelector = document.getElementById('lap');
        const lapColorTrigger = document.getElementById('lap-color-trigger');
        const lapColorPreview = document.getElementById('lap-color-preview');

        // Conserva los cambios manuales mientras permanezca activa la página.
        const manualLapColors = new Map();

        const getSelectedLapKey = () => lapSelector?.value ?? '';

        const getAutomaticLapColor = () => {
            const index = lapSelector?.selectedIndex ?? 0;
            return lapColorPresets[
                Math.max(0, index) % lapColorPresets.length
            ].value;
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
                    'absolute right-0 top-full z-50 mt-2 hidden ' +
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
                        colorMenu.classList.add('hidden');
                        drawOverlay();
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
                    drawOverlay();
                });
            }
        }

        updateLapColorPreview();



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

        const context = canvas.getContext('2d');

                // Silueta SVG del vehículo, vista desde arriba y con el frontal hacia arriba.
        const vehicleSvg = new Image();

        vehicleSvg.onload = () => {
            drawOverlay();
        };


@if($stint->car_id == 25)
@include('telemetry.partials.fclass')
@elseif($stint->car_id == 128)
@include('telemetry.partials.proto')
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
        const MAX_ZOOM = 8;
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

            // // Trazada completa atenuada como referencia.
            // context.lineWidth = 1.25;
            // context.strokeStyle = 'rgba(0, 229, 255, 0.22)';
            // context.beginPath();
            // drawPoints.forEach((point, index) => {
            //     if (index === 0) context.moveTo(point.x, point.y);
            //     else context.lineTo(point.x, point.y);
            // });
            // context.stroke();

            // Trazada de la vuelta con su color asignado.
            const completedIndex = Math.floor(playbackIndex);
            context.lineWidth = 0.5;
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
                    -2.45,
                    -6.5,
                    5,
                    8
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
            const percent = playbackIndex / Math.max(1, points.length - 1) * 100;
            if (progressSlider) progressSlider.value = String(percent);
            if (progressLabel) progressLabel.textContent = `${Math.round(percent)}%`;

                      updateTelemetryIndicators();
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
