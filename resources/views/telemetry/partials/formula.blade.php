        vehicleSvg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(`
            <svg xmlns="http://www.w3.org/2000/svg"
                 width="64" height="112" viewBox="0 0 64 112">

                <!-- Ruedas traseras -->
                <path d="M5 29 L16 29 L16 47 L5 47 Z"
                      fill="#171717" stroke="#555" stroke-width="1"/>
                <path d="M48 29 L59 29 L59 47 L48 47 Z"
                      fill="#171717" stroke="#555" stroke-width="1"/>

                <!-- Ruedas delanteras -->
                <path d="M8 72 L17 72 L17 88 L8 88 Z"
                      fill="#171717" stroke="#555" stroke-width="1"/>
                <path d="M47 72 L56 72 L56 88 L47 88 Z"
                      fill="#171717" stroke="#555" stroke-width="1"/>

                <!-- Alerón trasero -->
                <path d="M9 20 L55 20 L58 27 L6 27 Z"
                      fill="#1687ff" stroke="#0b2f55" stroke-width="2"/>
                <path d="M13 17 L51 17 L51 21 L13 21 Z"
                      fill="#e5e7eb" stroke="#0b2f55" stroke-width="1"/>

                <!-- Carrocería principal -->
                <path d="
                    M22 25
                    Q32 21 42 25
                    L45 42
                    L41 58
                    L38 76
                    L35 91
                    L32 101
                    L29 91
                    L26 76
                    L23 58
                    L19 42 Z"
                    fill="#1687ff"
                    stroke="#0b2f55"
                    stroke-width="2"/>

                <!-- Pontones laterales -->
                <path d="M19 43 L26 47 L27 66 L21 70 L17 58 Z"
                      fill="#e5e7eb" stroke="#0b2f55" stroke-width="1.5"/>
                <path d="M45 43 L38 47 L37 66 L43 70 L47 58 Z"
                      fill="#e5e7eb" stroke="#0b2f55" stroke-width="1.5"/>

                <!-- Cockpit -->
                <path d="M27 37 Q32 32 37 37 L38 52
                         Q32 58 26 52 Z"
                      fill="#111827" stroke="#dbeafe" stroke-width="1.5"/>

                <!-- Halo -->
                <path d="M25 40 Q32 34 39 40 L37 48
                         Q32 52 27 48 Z"
                      fill="none" stroke="#f8fafc" stroke-width="2.5"/>
                <path d="M32 37 L32 49"
                      stroke="#f8fafc" stroke-width="2"/>

                <!-- Morro -->
                <path d="M28 55 L36 55 L34 78 L32 94 L30 78 Z"
                      fill="#60a5fa" stroke="#0b2f55" stroke-width="1"/>

                <!-- Alerón delantero -->
                <path d="M13 94 L51 94 L57 100
                         L39 103 L25 103 L7 100 Z"
                      fill="#1687ff" stroke="#0b2f55" stroke-width="2"/>
                <path d="M18 97 L46 97"
                      stroke="#e5e7eb" stroke-width="2"/>

                <!-- Detalle frontal -->
                <path d="M29 85 L35 85 L32 96 Z"
                      fill="#f8fafc"/>
            </svg>
        `);
