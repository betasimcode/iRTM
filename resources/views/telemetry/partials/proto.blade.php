
vehicleSvg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg"
         width="64" height="112" viewBox="0 0 64 112">
  <!-- Definiciones de Gradientes y Sombreados -->
  <defs>
    <!-- Sombra proyectada del vehículo (ajustada para el nuevo ángulo) -->
    <filter id="car-shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="1.5" result="blur" />
      <feColorMatrix type="matrix" values="0 0 0 0 0   0 0 0 0 0   0 0 0 0 0  0 0 0 0.8 0"/>
    </filter>

    <!-- Gradiente para el cristal de la cabina -->
    <linearGradient id="cockpit-grad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#2d3748" />
      <stop offset="50%" stop-color="#4a5568" />
      <stop offset="100%" stop-color="#1a202c" />
    </linearGradient>

    <!-- Gradiente de fibra de carbono/detalles oscuros -->
    <linearGradient id="carbon-grad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#111" />
      <stop offset="100%" stop-color="#222" />
    </linearGradient>
  </defs>

  <!-- Fondo oscuro integrado adaptado a las nuevas dimensiones -->

  <!-- GRUPO PRINCIPAL CON ROTACIÓN DE 90 GRADOS -->
  <!-- Se aplica un escalado proporcional para encajar el diseño perfectamente en el lienzo de 64x112 -->
  <g transform="translate(32, 56) rotate(90) translate(0 -1.6) scale(0.11 0.121)">

    <!-- Sombra Base -->

    <!-- CARROCERÍA BLANCA PRINCIPAL (Centrada en el origen rotado) -->
    <g transform="translate(-480, -250)">
      <path d="M 100,130
               C 180,130 200,100 320,100
               C 450,100 480,140 600,140
               C 680,140 700,110 800,110
               C 860,110 880,160 880,250
               C 880,340 860,390 800,390
               C 700,390 680,360 600,360
               C 480,360 450,400 320,400
               C 200,400 180,370 100,370
               Z" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2" />

      <!-- PASOS DE RUEDA Y ENTRADAS DE AIRE OSCURAS -->
      <!-- Superior Izquierdo -->
      <path d="M 120,130 C 180,130 200,105 280,105 L 300,160 C 220,160 180,165 120,165 Z" fill="url(#carbon-grad)" />
      <!-- Superior Derecho -->
      <path d="M 680,140 C 720,140 740,112 800,112 C 840,112 860,140 860,180 L 780,180 C 740,180 720,165 680,160 Z" fill="url(#carbon-grad)" />
      <!-- Inferior Izquierdo -->
      <path d="M 120,370 C 180,370 200,395 280,395 L 300,340 C 220,340 180,335 120,335 Z" fill="url(#carbon-grad)" />
      <!-- Inferior Derecho -->
      <path d="M 680,360 C 720,360 740,388 800,388 C 840,388 860,360 860,320 L 780,320 C 740,320 720,335 680,340 Z" fill="url(#carbon-grad)" />

      <!-- NEUMÁTICOS INTERIORES -->
      <rect x="150" y="110" width="80" height="20" rx="4" fill="#0f172a" />
      <rect x="740" y="115" width="80" height="22" rx="4" fill="#0f172a" />
      <rect x="150" y="370" width="80" height="20" rx="4" fill="#0f172a" />
      <rect x="740" y="363" width="80" height="22" rx="4" fill="#0f172a" />

      <!-- DISEÑO DE VINILOS ROJOS Y NEGROS -->
      <path d="M 880,250 L 760,180 L 780,210 L 865,250 Z" fill="#ef4444" />
      <path d="M 880,250 L 760,320 L 780,290 L 865,250 Z" fill="#ef4444" />
      <path d="M 760,180 L 660,200 L 660,215 L 780,210 Z" fill="#111827" />
      <path d="M 760,320 L 660,300 L 660,285 L 780,290 Z" fill="#111827" />
      <path d="M 100,165 L 200,165 L 180,190 L 100,190 Z" fill="#ef4444" />
      <path d="M 100,335 L 200,335 L 180,310 L 100,310 Z" fill="#ef4444" />

      <!-- CABINA CENTRAL (Cockpit) -->
      <path d="M 400,200
               C 450,180 580,180 660,200
               C 710,215 710,285 660,300
               C 580,320 450,320 400,300
               C 370,280 370,220 400,200 Z" fill="url(#cockpit-grad)" stroke="#1e293b" stroke-width="3" />
      <path d="M 600,190 C 650,195 660,220 660,250 C 660,280 650,305 600,310 C 630,280 630,220 600,190 Z" fill="#0f172a" opacity="0.7"/>

      <!-- ALETA DE TIBURÓN CENTRAL -->
      <path d="M 105,247 L 480,247 L 460,253 L 105,253 Z" fill="#ef4444" />
      <path d="M 120,249 L 420,249 L 410,251 L 120,251 Z" fill="#f8fafc" />

      <!-- ALERÓN TRASERO -->
      <rect x="60" y="115" width="40" height="270" rx="2" fill="#111827" />
      <rect x="50" y="105" width="60" height="15" rx="2" fill="#ef4444" />
      <rect x="50" y="380" width="60" height="15" rx="2" fill="#ef4444" />
      <line x1="80" y1="140" x2="80" y2="360" stroke="#f8fafc" stroke-width="6" stroke-dasharray="15 10" opacity="0.9" />

      <!-- Persianas de ventilación -->
      <g stroke="#111827" stroke-width="2">
        <line x1="780" y1="130" x2="810" y2="130" />
        <line x1="780" y1="135" x2="810" y2="135" />
        <line x1="780" y1="140" x2="810" y2="140" />
        <line x1="780" y1="370" x2="810" y2="370" />
        <line x1="780" y1="365" x2="810" y2="365" />
        <line x1="780" y1="360" x2="810" y2="360" />
        <line x1="170" y1="145" x2="195" y2="145" />
        <line x1="170" y1="150" x2="195" y2="150" />
        <line x1="170" y1="355" x2="195" y2="355" />
        <line x1="170" y1="350" x2="195" y2="350" />
      </g>
    </g>
  </g>
</svg>


`);
