
<script>
(() => {
    const initLapOverlay = () => {
        const viewport = document.getElementById('telemetry-track-viewport');
        const container = document.getElementById('telemetry-track-canvas');
        const canvas = document.getElementById('telemetry-lap-overlay');
        const mapObject = document.getElementById('telemetry-track-map');

        if (!viewport || !container || !canvas || !mapObject) return;
        if (viewport.dataset.mapNavigationReady === '1') return;
        viewport.dataset.mapNavigationReady = '1';

        const samples = @js($lapTelemetry ?? []);
        const trackId = String(viewport.dataset.trackId);

        const zoomInButton = document.getElementById('telemetry-map-zoom-in');
        const zoomOutButton = document.getElementById('telemetry-map-zoom-out');
        const resetButton = document.getElementById('telemetry-map-reset');
        const zoomLabel = document.getElementById('telemetry-map-zoom-level');

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

        const points = samples
            .filter(sample =>
                sample?.position?.lat != null &&
                sample?.position?.lon != null &&
                Number.isFinite(Number(sample.position.lat)) &&
                Number.isFinite(Number(sample.position.lon))
            )
            .map(sample => ({
                lat: Number(sample.position.lat),
                lon: Number(sample.position.lon),
            }));

        const context = canvas.getContext('2d');

        if (!context || points.length < 2) {
            console.warn('[GPS overlay] No hay suficientes posiciones válidas.', {
                trackId,
                samples: samples.length,
                points: points.length,
            });
            return;
        }

        // Estado de navegación independiente de la calibración del circuito.
        const view = { zoom: 1, panX: 0, panY: 0 };
        const MIN_ZOOM = 1;
        const MAX_ZOOM = 12;
        const ZOOM_STEP = 1.2;

        let isDragging = false;
        let dragStart = null;

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

            window.__telemetryZoomDebug = () => ({
                zoom: view.zoom,
                maxZoom: MAX_ZOOM,
                label: zoomLabel?.textContent
            });

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
            if (event.button !== 0) return;

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

            // Renderizar con más resolución para compensar el zoom CSS.
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

            context.lineWidth = 0.5;
            context.lineJoin = 'round';
            context.lineCap = 'round';
            context.imageSmoothingEnabled = true;
            context.strokeStyle = '#ff0000';
            context.beginPath();

            drawPoints.forEach((point, index) => {
                if (index === 0) context.moveTo(point.x, point.y);
                else context.lineTo(point.x, point.y);
            });

            context.stroke();

            const first = drawPoints[0];
            const last = drawPoints[drawPoints.length - 1];

            context.fillStyle = '#22c55e';
            context.beginPath();
            context.arc(first.x, first.y, 4, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = '#f97316';
            context.beginPath();
            context.arc(last.x, last.y, 4, 0, Math.PI * 2);
            context.fill();
        };

        const updateView = () => {
            container.style.transformOrigin = '0 0';
            container.style.transform =
                `translate(${view.panX}px, ${view.panY}px) scale(${view.zoom})`;

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

        drawOverlay();
        updateView();

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
