
vehicleSvg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg"
         width="64" height="112" viewBox="0 0 64 112">

        <!-- Ruedas traseras -->
        <path d="M4 27 H16 V46 H4 Z"
              fill="#171717" stroke="#555" stroke-width="1" transform="translate(-1.7 -14)"/>
        <path d="M48 27 H60 V46 H48 Z"
              fill="#171717" stroke="#555" stroke-width="1" transform="translate(2.1 -13.8)"/>

        <!-- Ruedas delanteras -->
        <path d="M7 72 H16 V88 H7 Z"
              fill="#171717" stroke="#555" stroke-width="1" transform="translate(-5.42 -0.4) scale(1.14592 1)"/>
        <path d="M47 72 H57 V88 H47 Z"
              fill="#171717" stroke="#555" stroke-width="1" transform="translate(-0.52 0.1) scale(1.0881 1)"/>

        <!-- Alerón trasero clásico -->
        <path d="M7 19 L57 19 L60 26 L4 26 Z"
              fill="#0b0b0bff" stroke="#0b2f55" stroke-width="2" transform="translate(8.38 -37.41) scale(0.755357 1.84286)"/>
        <path d="M12 15 H52 V19 H12 Z"
              fill="#e5e7eb" stroke="#0b2f55" stroke-width="1" transform="translate(0 -8.4)"/>

        <!-- Carrocería central -->
        <path d="
            M23 25
            Q32 22 41 25
            L44 39
            L41 51
            L38 67
            L35 83
            L32 98
            L29 83
            L26 67
            L23 51
            L20 39 Z"
            fill="#1687ff"
            stroke="#0b2f55"
            stroke-width="2" transform="translate(-5.19 -16.41) scale(1.1646 1.16742)"/>

        <!-- Pontones laterales -->
        <path d="M20 41 L26 45 L27 64 L22 69 L17 56 Z"
              fill="#e5e7eb" stroke="#0b2f55" stroke-width="1.5" transform="translate(-6.48 -56.68) scale(1.24 1.82143)"/>
        <path d="M44 41 L38 45 L37 64 L42 69 L47 56 Z"
              fill="#e5e7eb" stroke="#0b2f55" stroke-width="1.5" transform="translate(-6.29 -57.42) scale(1.17 1.83214)"/>

        <!-- Cockpit abierto -->
        <path d="M27 33 Q32 29 37 33 L38 47 Q32 53 26 47 Z"
              fill="#111827" stroke="#dbeafe" stroke-width="1.5" transform="translate(7.74 -3.74) scale(0.760278 1.07678)"/>

        <!-- Piloto / asiento -->
        <ellipse cx="31.75" cy="40.95" rx="3.75" ry="4.15"
                 fill="#374151" stroke="#9ca3af" stroke-width="1"/>

        <!-- Morro -->
        <path d="M28 53 H36 L34 74 L32 91 L30 74 Z"
              fill="#60a5fa" stroke="#0b2f55" stroke-width="1"/>

        <!-- Alerón delantero -->
        <path d="M13 91 H51 L58 98 L40 102 H24 L6 98 Z"
              fill="#060606ff" stroke="#0b2f55" stroke-width="2" transform="translate(7.6 2.88) scale(0.767234 0.949665)"/>
        <path d="M17 95 H47"
              stroke="#e5e7eb" stroke-width="2" transform="translate(1.84 0) scale(0.960864 1)"/>

    </svg>
`);
