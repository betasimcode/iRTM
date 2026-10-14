<script>

(() => {

    const initLapOverlay = () => {

        const container = document.getElementById('telemetry-track-canvas');

        const canvas = document.getElementById('telemetry-lap-overlay');

        const mapObject = document.getElementById('telemetry-track-map');



        if (!container || !canvas || !mapObject) return;



        // Evitar inicializaciones duplicadas.

        if (container.dataset.gpsOverlayReady === '1') return;

        container.dataset.gpsOverlayReady = '1';



        // Muestras obtenidas por TelemetryLapController.

        const samples = @js($lapTelemetry ?? []);

        console.log('[GPS diagnóstico] Primera muestra:', samples[0]);
        console.log('[GPS diagnóstico] Posición:', samples[0]?.position);

        const trackId = String(container.dataset.trackId);



        /*
         * CALIBRACIÓN POR track_id.
         *
         * scale: escala uniforme respecto al encaje GPS inicial.
         * rotation_deg: giro en grados.
         * offset_x_pct / offset_y_pct: desplazamiento porcentual
         * respecto al ancho y alto del canvas.
         *
         * Los valores son iniciales, no una calibración validada.
         */
        const defaultCalibration = {

            scale: 1,

            rotation_deg: 0,

            offset_x_pct: 0,

            offset_y_pct: 0,

        };


/////////////////////////////////////////////////////////////////////
// TRACK CALIBRATIONS //////////////////////////////////////////////

@include('telemetry.partials.track_calibrations')

// /////////////////////////////////////////////////////////////////



        const calibration = {

            ...defaultCalibration,

            ...(calibrations[trackId] ?? {}),

        };



        // Conservar solamente posiciones GPS válidas.

        const points = samples

            .filter(sample =>

                Number.isFinite(Number(sample?.position?.lat)) &&

                Number.isFinite(Number(sample?.position?.lon)) &&

                sample.position.lat !== null &&

                sample.position.lon !== null

            )

            .map(sample => ({

                lat: Number(sample.position.lat),

                lon: Number(sample.position.lon),

                distance: Number(sample.lap_dist_pct),

            }));



        const context = canvas.getContext('2d');



        if (!context || points.length < 2) {

            console.warn(

                '[GPS overlay] No hay suficientes posiciones válidas.',

                { trackId, samples: samples.length, points: points.length }

            );

            return;

        }



        // Proyección local: longitud y latitud expresadas en metros.

        const originLat = points.reduce((sum, p) => sum + p.lat, 0) / points.length;

        const originLon = points.reduce((sum, p) => sum + p.lon, 0) / points.length;

        const radians = Math.PI / 180;

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



        const resizeAndDraw = () => {

            const rect = container.getBoundingClientRect();



            if (rect.width <= 0 || rect.height <= 0) return;



            const dpr = window.devicePixelRatio || 1;



            canvas.width = Math.round(rect.width * dpr);

            canvas.height = Math.round(rect.height * dpr);



            context.setTransform(dpr, 0, 0, dpr, 0, 0);

            context.clearRect(0, 0, rect.width, rect.height);



            // Encajar la trazada manteniendo su proporción.

            const padding = Math.min(rect.width, rect.height) * 0.08;

            const availableWidth = Math.max(1, rect.width - padding * 2);

            const availableHeight = Math.max(1, rect.height - padding * 2);



            const fitScale = Math.min(

                availableWidth / rangeX,

                availableHeight / rangeY

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

                    x: rect.width / 2

                        + rotatedX * calibration.scale

                        + rect.width * calibration.offset_x_pct / 100,

                    y: rect.height / 2

                        + rotatedY * calibration.scale

                        + rect.height * calibration.offset_y_pct / 100,

                };

            });



            context.lineWidth = 2.5;

            context.lineJoin = 'round';

            context.lineCap = 'round';

            context.strokeStyle = '#00e5ff';

            context.beginPath();



            drawPoints.forEach((point, index) => {

                if (index === 0) {

                    context.moveTo(point.x, point.y);

                } else {

                    context.lineTo(point.x, point.y);

                }

            });



            context.stroke();



            // Punto inicial y final para facilitar la orientación.

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



        const observer = new ResizeObserver(resizeAndDraw);

        observer.observe(container);



        // El objeto SVG puede tardar en terminar de cargar.

        mapObject.addEventListener('load', resizeAndDraw);



        resizeAndDraw();



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
