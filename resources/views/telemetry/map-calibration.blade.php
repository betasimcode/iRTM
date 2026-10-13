
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>iRTM · Calibración del mapa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            margin: 0;
            background: #11151b;
            color: #e5e7eb;
            font-family: sans-serif;
        }

        .map-container {
            width: 100%;
            height: calc(100vh - 110px);
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 20px;
            box-sizing: border-box;
        }

        .track-map {
            display: block;
            width: 100%;
            height: 100%;
        }

        .track-map svg {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <header style="padding: 18px 24px; border-bottom: 1px solid #303743">
        <h1 style="margin: 0; font-size: 20px">
            iRTM · Calibración de circuito
        </h1>

        <p style="margin: 6px 0 0; color: #9ca3af; font-size: 13px">
            Circuito ID 157 · Mapa estático
        </p>
    </header>

    <main class="map-container">
        <object
            class="track-map"
            data="{{ asset('telemetry/tracks/157.svg') }}"
            type="image/svg+xml"
            aria-label="Mapa del circuito para calibración"
        >
            <p>
                No se ha podido cargar el mapa SVG.
            </p>
        </object>
    </main>

    <footer style="padding: 12px 24px; color: #9ca3af; font-size: 12px">
        Fase 1 · Geometría estática · Sin telemetría dinámica
    </footer>
</body>
</html>
