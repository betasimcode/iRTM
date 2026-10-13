    @php
    $jsonLaps = [];

    $stintDate = $stint->created_at ?? null;

    if ($stintDate && $stint->user_id && $stint->id) {
        $lapsJsonPath = storage_path(
            'app/private/telemetry/' .
            $stintDate->format('Y/m') .
            '/user_' . $stint->user_id .
            '/stint_' . $stint->id .
            '/stint_' . $stint->id . '_laps.json'
        );

        if (is_file($lapsJsonPath)) {
            $decoded = json_decode(
                file_get_contents($lapsJsonPath),
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE
                && isset($decoded['laps'])
                && is_array($decoded['laps'])
            ) {
                $jsonLaps = $decoded['laps'];
            }
        }
    }

    $lapMetrics = app(
        \App\Services\Stints\StintJsonMetricsService::class
    )->calculate($jsonLaps);

    $bestLap = $lapMetrics['best_lap'];
@endphp

    <div class="grid-cols-3 pb-3 align-middle items-center bg-[var(--bg)] rounded-xl border border-[var(--border)] mb-5 px-5 pt-3">
                 {{-- BOTONES --}}
        <div class="flex gap-2 width:100%">
                <x-ui.button class="hover:text-red-400" variant="optionbox" size="sm" href="{{url()->previous()}}">
                    <svg fill="currentColor" height="25" width="25" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 472.615 472.615" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <polygon points="205.783,139.662 205.783,30.525 0,236.308 205.783,442.09 205.783,332.955 472.615,332.955 472.615,139.662 "></polygon> </g> </g> </g></svg>
                </x-ui.button>

                <x-ui.button variant="optionbox" size="sm" @click="view='all'">
                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                    width="25" height="25" viewBox="0 0 285.000000 285.000000"
                    preserveAspectRatio="xMidYMid meet">

                    <g transform="translate(0.000000,285.000000) scale(0.100000,-0.100000)"
                    fill="currentcolor" stroke="none">
                    <path d="M1100 2176 c-102 -7 -198 -19 -215 -25 -76 -31 -127 -94 -255 -316
                    -34 -60 -65 -112 -67 -114 -1 -3 -13 5 -26 17 -20 19 -35 22 -104 22 -96 0
                    -136 -12 -152 -46 -15 -33 -13 -85 3 -107 16 -20 66 -37 113 -37 17 0 34 -3
                    36 -7 3 -5 -15 -28 -39 -53 -25 -25 -59 -74 -77 -110 l-32 -65 1 -355 c1 -386
                    2 -393 57 -434 25 -19 45 -21 170 -24 209 -5 246 13 257 122 l5 51 665 3 c366
                    1 675 0 688 -3 19 -5 22 -12 22 -53 0 -54 18 -85 60 -107 45 -23 303 -21 350
                    3 60 29 61 39 67 415 5 305 4 348 -11 401 -19 64 -55 123 -105 170 -17 17 -31
                    33 -31 36 0 4 25 10 56 13 30 3 66 13 79 21 49 32 40 125 -15 153 -14 7 -62
                    13 -112 13 -76 0 -91 -3 -109 -21 -11 -11 -22 -19 -23 -17 -1 1 -45 74 -97
                    161 -114 192 -137 221 -201 253 -40 20 -73 27 -167 34 -216 17 -599 19 -791 6z
                    m690 -137 c192 -12 220 -19 253 -62 40 -53 168 -279 164 -290 -4 -12 -1486
                    -16 -1494 -4 -5 8 108 210 159 284 21 31 39 45 66 52 99 26 586 37 852 20z
                    m-1120 -674 c175 -33 211 -49 240 -110 21 -44 22 -51 9 -74 -15 -26 -15 -26
                    -169 -29 -172 -4 -247 7 -278 41 -49 53 -52 125 -5 171 32 32 38 32 203 1z
                    m1784 -6 c36 -43 37 -115 1 -157 -35 -41 -92 -52 -279 -52 -140 0 -154 2 -171
                    20 -37 41 17 136 93 161 55 18 252 57 294 58 29 1 41 -5 62 -30z"/>
                    </g>
                    </svg>
                </x-ui.button>
                <x-ui.button variant="optionbox" size="sm" @click="view='tyre'">
                    <svg fill="currentColor" height="25" width="25" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M298.662,256.006c0-23.531-19.136-42.667-42.667-42.667s-42.667,19.136-42.667,42.667s19.136,42.667,42.667,42.667 S298.662,279.537,298.662,256.006z"></path> <path d="M330.044,151.799c-15.403-10.987-33.323-18.581-52.715-21.867v43.776c7.744,2.005,15.019,5.077,21.739,9.045 L330.044,151.799z"></path> <path d="M182.758,212.934l-30.976-30.976c-10.987,15.403-18.581,33.323-21.867,52.715h43.776 C175.697,226.929,178.769,219.654,182.758,212.934z"></path> <path d="M329.233,299.078l30.976,30.976c10.987-15.403,18.581-33.323,21.867-52.715H338.3 C336.294,285.084,333.222,292.358,329.233,299.078z"></path> <path d="M173.692,277.34h-43.776c3.285,19.392,10.88,37.312,21.867,52.715l30.976-30.976 C178.769,292.358,175.697,285.084,173.692,277.34z"></path> <path d="M181.948,360.22c15.403,10.987,33.323,18.581,52.715,21.867V338.31c-7.744-2.005-15.019-5.077-21.739-9.067 L181.948,360.22z"></path> <path d="M256,0C114.837,0,0,114.859,0,256c0,141.163,114.837,256,256,256s256-114.837,256-256C512,114.859,397.163,0,256,0z M377.003,376.213c-0.149,0.149-0.192,0.341-0.32,0.469c-0.128,0.128-0.32,0.171-0.469,0.299 C345.344,407.68,302.848,426.667,256,426.667c-46.869,0-89.365-18.987-120.235-49.685c-0.128-0.128-0.32-0.171-0.448-0.299 c-0.149-0.128-0.192-0.32-0.32-0.469C104.32,345.344,85.333,302.848,85.333,256c0-46.848,18.987-89.323,49.664-120.213 c0.128-0.128,0.171-0.32,0.32-0.448c0.128-0.149,0.32-0.192,0.448-0.32C166.635,104.341,209.131,85.333,256,85.333 c46.848,0,89.344,19.008,120.213,49.685c0.149,0.128,0.341,0.171,0.469,0.32c0.128,0.128,0.171,0.32,0.32,0.448 c30.656,30.891,49.664,73.365,49.664,120.213C426.667,302.848,407.659,345.344,377.003,376.213z"></path> <path d="M329.239,212.932c3.968,6.741,7.04,13.995,9.067,21.739h43.755c-3.264-19.392-10.859-37.312-21.845-52.715 L329.239,212.932z"></path> <path d="M234.656,173.698v-43.755c-19.392,3.264-37.312,10.88-52.715,21.845l30.976,30.976 C219.659,178.797,226.912,175.725,234.656,173.698z"></path> <path d="M277.335,338.315v43.755c19.392-3.264,37.312-10.88,52.715-21.845l-30.976-30.976 C292.333,333.216,285.079,336.288,277.335,338.315z"></path> </g> </g> </g> </g></svg>
                </x-ui.button>

                <x-ui.button variant="optionbox" size="sm" @click="view='susp'">
                    <svg width="25" height="25" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="currentColor" d="M326.4 26.52c-.7 0-1.7.14-2.9.44-3.4.82-8 3.38-11.7 6.89-3.8 3.51-6.7 7.92-7.9 11.34-1.1 3.41-.8 4.92.5 6.57 27.4 33.77 52.6 72.04 71.9 105.74 19.3 33.8 32.7 62.2 36.6 79.9.4 1.8.7 2 .7 1.9 1.2 0 5.7-1.7 9.9-5.2 4.3-3.5 8.6-8.6 11.1-13.3 2.6-4.7 3.1-8.8 2.6-10.4-15.1-55.3-42.6-116.41-107.9-182.57-.7-.67-1.3-1.09-2.2-1.24-.2 0-.4-.1-.7-.1zm53.2 30.97c11 13.92 20.6 27.66 28.9 41.17 13 7.24 28.5 16.34 43.9 26.04 15.2 9.4 29.4 18.9 40.9 27.4l-1.8-28.2c-16.4-16.3-45-33.55-74.1-48.31-13.1-6.65-25.8-12.61-37.8-18.1zM247.1 105.8c-.7 0-1.7.1-2.9.4-3.4.8-7.9 3.4-11.7 6.9-3.7 3.5-6.6 7.9-7.7 11.3-1.1 3.3-.9 4.8.5 6.5 27.4 33.8 52.6 72 71.9 105.7 19.3 33.8 32.6 62.3 36.6 79.8v.1c.4 1.8.7 2 .7 1.9 1.1 0 5.6-1.7 9.9-5.2 4.3-3.5 8.5-8.6 11.1-13.3 2.5-4.7 3-8.9 2.6-10.4-15.3-55.2-42.8-116.2-108.1-182.4-.7-.7-1.3-1.1-2.2-1.2-.2-.1-.4-.1-.7-.1zm53.1 30.9c11.1 14 20.7 27.8 29.1 41.3 13 7.3 28.5 16.4 43.9 26 3.5 2.2 7 4.4 10.4 6.6-5.8-12.7-13.6-27.7-22.7-43.7-7.4-4.2-15-8.2-22.7-12.1-13.2-6.6-25.9-12.6-38-18.1zm-126.5 57.1c-.7 0-1.7.1-2.9.4-3.4.8-8 3.4-11.7 6.9-3.8 3.5-6.7 7.9-7.9 11.3-1.1 3.4-.8 4.9.5 6.5 27.4 33.7 52.6 71.9 71.9 105.7 19.3 33.8 32.7 62.2 36.6 79.9.4 1.8.7 2 .7 1.9 1.1 0 5.7-1.7 9.9-5.2 4.3-3.5 8.6-8.6 11.1-13.3 2.6-4.8 3.1-8.9 2.6-10.5-15.2-55.1-42.6-116.1-107.9-182.4-.7-.6-1.3-1-2.2-1.2h-.7zm53.1 30.9c11.1 13.9 20.6 27.7 29 41.2 13 7.3 28.5 16.4 43.9 26 2.7 1.7 5.4 3.4 8 5-5.2-11.9-12.5-26.6-21.3-42.5-7.1-4-14.4-7.9-21.8-11.6-13.1-6.7-25.8-12.6-37.8-18.1zM94.5 272.9c-.77 0-1.71.2-2.97.5-3.34.8-7.91 3.3-11.67 6.8s-6.68 7.9-7.79 11.3c-1.1 3.4-.86 4.9.48 6.5 27.43 33.8 52.65 72 71.95 105.7 19.3 33.8 32.7 62.2 36.6 79.8v.1c.4 1.8.7 2 .7 1.9 1.2 0 5.7-1.7 9.9-5.2 4.3-3.5 8.6-8.6 11.1-13.3 2.6-4.7 3.1-8.8 2.6-10.4-15.3-55.1-42.7-116.1-108.02-182.4-.7-.6-1.3-1.1-2.17-1.2-.22-.1-.46-.1-.71-.1zm53.1 30.9c11.1 14 20.7 27.8 29 41.3 13 7.3 28.5 16.4 43.9 26 3.5 2.2 7 4.4 10.4 6.6-5.8-12.7-13.5-27.7-22.6-43.7-7.4-4.1-15.1-8.2-22.8-12.1-13.1-6.6-25.8-12.6-37.9-18.1zM18.72 353c.34 2.1 1.03 4.8 2.88 8.4 5.75 11.3 20.09 27.8 46.74 42.6 23.89 13.3 46.86 28.4 85.06 56.4-6-13.5-14.4-30.1-24.5-47.7l-.3-.6C92.89 388 46.1 366 18.72 353z"></path></g></svg>
                </x-ui.button>

                <x-ui.button variant="optionbox" size="sm" @click="view='chassis'">
                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                    width="25" height="25" viewBox="0 0 283.000000 283.000000"
                    preserveAspectRatio="xMidYMid meet">

                    <g transform="translate(0.000000,283.000000) scale(0.100000,-0.100000)"
                    fill="currentcolor" stroke="none">
                    <path d="M2178 2659 c-84 -13 -88 -29 -88 -367 0 -173 4 -291 11 -310 19 -54
                    43 -62 189 -62 l132 0 29 29 29 29 0 311 0 311 -27 29 c-27 29 -31 30 -135 33
                    -58 2 -121 0 -140 -3z"/>
                    <path d="M437 2649 c-43 -25 -47 -52 -47 -359 0 -195 4 -299 11 -313 26 -51
                    45 -57 186 -57 132 0 133 0 164 28 l32 29 -1 312 0 313 -28 29 -28 29 -135 0
                    c-75 0 -144 -5 -154 -11z"/>
                    <path d="M946 2645 c-11 -12 -16 -35 -16 -82 0 -55 4 -70 25 -95 26 -31 32
                    -67 15 -99 -9 -16 -22 -19 -75 -19 l-65 0 0 -95 0 -94 68 -3 67 -3 5 -70 c4
                    -59 11 -80 45 -135 l40 -65 3 -187 c3 -166 0 -203 -23 -335 -17 -94 -33 -155
                    -43 -167 -15 -16 -17 -50 -20 -249 -4 -270 -3 -267 -89 -267 l-53 0 0 -100 0
                    -99 68 -3 c64 -3 67 -4 70 -29 2 -15 -5 -32 -18 -44 -17 -15 -20 -30 -20 -91
                    0 -62 3 -75 19 -84 31 -16 223 -13 247 5 15 12 62 14 244 14 182 0 229 -2 244
                    -14 29 -22 213 -21 244 1 34 23 32 114 -4 165 -17 25 -24 44 -19 58 6 19 14
                    21 76 21 l70 0 -3 98 -3 97 -52 3 c-42 2 -56 8 -73 29 -19 24 -20 40 -20 247
                    0 184 -3 225 -16 244 -8 12 -26 85 -40 163 -22 124 -25 163 -22 333 l3 193 38
                    60 c32 53 37 69 37 120 0 79 11 92 85 95 l60 3 0 90 0 90 -60 3 c-66 3 -85 17
                    -85 63 0 14 11 43 25 63 33 48 35 136 5 166 -18 18 -33 20 -128 20 -61 0 -112
                    -4 -118 -10 -11 -11 -354 -8 -605 5 -100 5 -119 4 -133 -10z m709 -187 c14
                    -13 9 -99 -7 -109 -22 -14 -412 -11 -426 3 -16 16 -15 93 1 106 7 6 42 13 77
                    15 102 5 344 -5 355 -15z m-11 -301 c16 -11 17 -28 14 -156 l-3 -143 -33 -29
                    -32 -29 -143 0 c-153 0 -176 6 -211 53 -17 23 -21 46 -24 152 -6 175 -20 165
                    223 165 141 0 196 -3 209 -13z m-20 -559 c29 -29 34 -42 39 -103 4 -38 18
                    -126 31 -195 l24 -125 -23 -36 c-18 -27 -24 -53 -26 -117 -6 -119 -2 -116
                    -211 -120 -94 -2 -181 -1 -194 2 -44 11 -54 32 -54 112 0 62 -4 83 -25 118
                    l-25 43 24 124 c13 68 27 156 31 194 6 59 12 75 38 103 l31 32 154 0 154 0 32
                    -32z m14 -874 c21 -14 22 -22 22 -144 0 -171 13 -162 -235 -158 -171 3 -188 5
                    -201 22 -10 15 -14 52 -14 136 0 163 -7 158 235 159 139 1 175 -2 193 -15z"/>
                    <path d="M454 950 c-12 -4 -31 -21 -43 -36 -20 -26 -21 -35 -21 -328 0 -263 2
                    -305 16 -325 27 -38 74 -51 185 -51 109 0 160 15 181 53 15 29 15 616 -1 645
                    -22 41 -61 52 -183 51 -62 0 -123 -4 -134 -9z"/>
                    <path d="M2153 950 c-13 -5 -32 -24 -43 -42 -19 -31 -20 -51 -20 -321 0 -385
                    -7 -372 205 -368 125 2 131 3 158 28 l27 27 0 309 c0 192 -4 316 -10 328 -21
                    38 -62 49 -182 48 -62 0 -123 -4 -135 -9z"/>
                    </g>
                    </svg>
                </x-ui.button>
                <x-ui.button variant="optionbox" size="sm" @click="view='aero'">
                    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                    width="25" height="25" viewBox="0 0 283.000000 283.000000"
                    preserveAspectRatio="xMidYMid meet">

                    <g transform="translate(0.000000,283.000000) scale(0.100000,-0.100000)"
                    fill="currentcolor" stroke="none">
                    <path d="M113 1976 l-28 -24 0 -425 c0 -404 1 -426 19 -446 13 -14 31 -21 57
                    -21 59 0 69 22 69 157 0 131 -15 121 140 98 58 -9 181 -24 275 -33 l169 -17
                    10 -33 c5 -19 20 -41 33 -49 22 -15 23 -19 23 -165 l0 -149 -35 -36 c-20 -20
                    -35 -46 -35 -60 l0 -23 138 0 c76 0 141 2 144 5 12 12 -6 58 -33 85 l-29 28 0
                    146 c0 93 4 147 11 152 6 3 19 19 28 35 l18 29 367 3 c201 1 366 -1 366 -4 0
                    -3 13 -23 29 -43 l29 -36 -5 -143 c-5 -139 -6 -143 -34 -172 -15 -16 -31 -42
                    -34 -57 l-7 -28 146 0 c125 0 146 2 146 15 0 25 -29 73 -50 85 -19 10 -20 21
                    -20 165 0 142 2 155 19 165 11 5 27 27 37 49 l17 38 76 7 c42 4 152 18 245 32
                    93 14 175 23 182 20 11 -4 14 -32 14 -119 0 -130 9 -147 73 -147 68 0 67 -8
                    67 471 l0 428 -26 20 c-15 12 -34 21 -44 21 -10 0 -29 -9 -44 -21 -24 -19 -26
                    -27 -26 -92 0 -40 -4 -78 -8 -85 -7 -10 -251 -12 -1188 -10 l-1179 3 -3 82
                    c-3 75 -5 84 -29 103 -34 26 -56 25 -90 -4z m205 -521 c488 -102 1218 -131
                    1772 -69 121 13 448 66 493 80 13 4 17 -1 17 -24 l0 -29 -132 -26 c-193 -38
                    -238 -45 -388 -61 -511 -56 -1048 -42 -1580 40 -244 37 -264 43 -268 77 -4 32
                    -4 31 86 12z m655 -205 c53 0 57 -1 57 -24 0 -13 -6 -26 -12 -28 -25 -9 -138
                    5 -149 18 -18 22 -3 47 24 40 12 -3 48 -6 80 -6z m1057 -14 c0 -30 -16 -36
                    -102 -36 -66 0 -69 1 -66 23 2 18 11 23 53 28 106 12 115 11 115 -15z"/>
                    </g>
                    </svg>
                </x-ui.button>
                <x-ui.button class="hover:text-emerald-600" variant="optionbox" size="sm" @click="view='telemetry'">
                    <svg width="25" height="25" viewBox="0 0 50.8 50.8" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g style="display:inline"> <path d="M7.854 33.546 16 22.893l7.52 16.293 6.267-27.572 3.76 8.773 5.64-6.893 3.76 8.146" style="fill:none;stroke:currentColor;stroke-width:2.50658;stroke-linecap:round;stroke-linejoin:round"></path> </g> </g></svg>
                </x-ui.button>

            <div x-data="{ showAlerts: false }" class="flex gap-2">
                <x-ui.button class="hover:text-red-600" variant="optionbox" size="sm" @click="showAlerts = true">
                    <svg width="25" height="25" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="add" fill="CurrentColor" transform="translate(32.000000, 42.666667)"> <path d="M246.312928,5.62892705 C252.927596,9.40873724 258.409564,14.8907053 262.189374,21.5053731 L444.667042,340.84129 C456.358134,361.300701 449.250007,387.363834 428.790595,399.054926 C422.34376,402.738832 415.04715,404.676552 407.622001,404.676552 L42.6666667,404.676552 C19.1025173,404.676552 7.10542736e-15,385.574034 7.10542736e-15,362.009885 C7.10542736e-15,354.584736 1.93772021,347.288125 5.62162594,340.84129 L188.099293,21.5053731 C199.790385,1.04596203 225.853517,-6.06216498 246.312928,5.62892705 Z M225.144334,42.6739678 L42.6666667,362.009885 L407.622001,362.009885 L225.144334,42.6739678 Z M224,272 C239.238095,272 250.666667,283.264 250.666667,298.624 C250.666667,313.984 239.238095,325.248 224,325.248 C208.415584,325.248 197.333333,313.984 197.333333,298.282667 C197.333333,283.264 208.761905,272 224,272 Z M245.333333,106.666667 L245.333333,234.666667 L202.666667,234.666667 L202.666667,106.666667 L245.333333,106.666667 Z" id="Combined-Shape"> </path> </g> </g> </g></svg>
                </x-ui.button>

                <x-ui.button class="hover:text-[var(--text-card-title)]" variant="optionbox" size="sm">
                    <svg fill="CurrentColor" width="25" height="25" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" stroke="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>info</title> <path d="M16.247 4.733c0 1.684 1.271 2.825 2.84 2.825s2.842-1.142 2.842-2.825c0-1.685-1.272-2.826-2.842-2.826-1.568 0-2.84 1.141-2.84 2.826zM10.096 14.375c0 0.334-0.061 1.163 0.008 1.662l2.479-2.983c0.513-0.562 1.106-0.955 1.409-0.849s0.47 0.463 0.371 0.795l-4.103 13.59c-0.473 1.588 0.421 3.148 2.599 3.504 3.189 0 5.084-2.158 6.948-4.955 0-0.334 0.111-1.213 0.044-1.713l-2.479 2.982c-0.514 0.562-1.151 0.955-1.455 0.85-0.28-0.098-0.444-0.41-0.389-0.721l4.132-13.653c0.344-1.734-0.59-3.312-2.564-3.514-2.076 0.001-5.136 2.209-7 5.005z"></path> </g></svg>            </x-ui.button>

                @if($setup?->setup_file_path)

                <x-ui.button class="hover:text-orange-400" title="Download setup" variant="optionbox" size="sm" href="{{ route('setups.download', $setup) }}">
                    <svg width="25" height="25" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#505050"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M512 791.466667L277.333333 512h469.333334zM426.666667 85.333333h170.666666v85.333334h-170.666666zM426.666667 213.333333h170.666666v85.333334h-170.666666z" fill="CurrentColor"></path><path d="M426.666667 341.333333h170.666666v234.666667h-170.666666zM128 853.333333h768v85.333334H128z" fill="CurrentColor"></path></g></svg>
                </x-ui.button>

                <x-ui.button id="install-setup-btn" data-setup-id="{{ $setup->id }}" class="hover:text-green-500" title="Install file setup" variant="optionbox" size="sm">
                    <svg fill="CurrentColor" width="25" height="25" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M19.059 10.898l-3.171-7.927A1.543 1.543 0 0 0 14.454 2H12.02l.38 4.065h2.7L10 10.293 4.9 6.065h2.7L7.98 2H5.546c-.632 0-1.2.384-1.434.971L.941 10.898a4.25 4.25 0 0 0-.246 2.272l.59 3.539A1.544 1.544 0 0 0 2.808 18h14.383c.755 0 1.399-.546 1.523-1.291l.59-3.539a4.22 4.22 0 0 0-.245-2.272zm-2.1 4.347a.902.902 0 0 1-.891.755H3.932a.902.902 0 0 1-.891-.755l-.365-2.193A.902.902 0 0 1 3.567 12h12.867c.558 0 .983.501.891 1.052l-.366 2.193z"></path></g></svg>
                </x-ui.button>

                @endif

            <div x-show="showAlerts" class="fixed z-50 inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded w-96">

                    <h2 class="text-lg font-bold mb-4">Alertas</h2>

                    @forelse($telemetryAlerts as $alert)
                        <div class="mb-2 p-2 border rounded bg-yellow-100">
                            <strong>{{ $alert['name'] }}</strong><br>
                            {{ $alert['message'] }}<br>
                            <em>{{ $alert['suggestion'] }}</em>
                        </div>
                    @empty
                        <p>No hay alertas</p>
                    @endforelse

                    <button
                        @click="showAlerts = false"
                        class="mt-4 px-3 py-1 bg-gray-600 text-white rounded">
                        Cerrar
                    </button>
                </div>
            </div>


    </div>

        <div class="w-2/3 text-center ml-5 px-8 pt-0.5">
            <table class="text-xs text-center px-5 w-full">

        {{-- DATA --}}
        <tbody class="">
            <tr class="flex transition w-full">

                {{-- Vueltas --}}
                <td title="{{ __('ui.laps') }}" class="flex border border-[var(--border)] text-[var(--value-info)] bg-[var(--card)] mx-1 rounded-lg font-semibold px-3 font-mono-timing">
                    <div class="flex pr-1 pt-0.5">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M14 7H15.9992C19.3129 7 21.9992 9.68629 21.9992 13C21.9992 16.3137 19.3129 19 15.9992 19H8C4.68629 19 2 16.3137 2 13C2 9.68629 4.68629 7 8 7H10M7 4L10 7M10 7L7 10" stroke="CurrentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                    </div>
                    <div class="flex text-center items-center pl-1 pt-0.5">
                        {{ $lapMetrics['valid_lap_count'] }}
                    </div>
                </td>

                {{-- Duración --}}
                <td title="{{ __('ui.duration') }}" class="flex mx-1 bg-[var(--card)] rounded-lg border border-[var(--border)] text-[var(--text-soft)] font-semibold font-mono-timing px-3">
                    <div class="flex pr-1 pt-0.5">
                    <svg fill="CurrentColor" width="23" height="23" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCurrentColorarrier" stroke-width="0"></g><g id="SVGRepo_tracerCurrentColorarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCurrentColorarrier"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-7.59V4h2v5.59l3.95 3.95-1.41 1.41L9 10.41z"></path></g></svg>
                    </div>
                    <div class="flex text-center items-center pl-1 pt-0.5">
                        {{ gmdate('H:i:s', (int) ($jsonDuration ?? 0)) }}
                    </div>
                </td>


                {{-- Ritmo --}}
                <td title="{{ __('ui.avglap') }}" class="flex mx-1 bg-[var(--card)] rounded-lg border border-[var(--border)] text-[var(--value-data)]  font-semibold font-mono-timing px-3">
                <div class="flex pr-1">
                <svg fill="currentColor" width="25" height="25" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4,8.39l2,2.09L8.39,7.76l2.69,2.75,4.7-5.89L14.22,3.38l-3.3,4.11L8.29,4.81,6,7.52l-1.91-2L.29,9.29l1.42,1.42ZM0,12.3v1.4H16V12.3Z"></path> </g></svg>
                </div>
                <div class="flex text-center items-center pl-1 pt-0.5">
                        {{ $avgLap !== null ? lapTime($avgLap) : '—' }}
                    </div>
                </td>

                {{-- Consumo --}}
                <td class="text-[var(--fuel)] font-semibold">

                </td>

                {{-- Clima --}}
                <td class="flex items-center  bg-[var(--card)] mx-1 rounded-lg border border-[var(--border)]  text-[var(--text)] font-semibold font-mono-timing px-3">

                    <div
                        x-data="{ weatherOpen: false }"
                        @mouseenter="weatherOpen = true"
                        @mouseleave="weatherOpen = false"
                        @keydown.escape.window="weatherOpen = false"
                        class="relative"
                    >
                        {{-- Disparador: sustituye aquí el SVG meteorológico actual --}}
                        <button
                            type="button"
                            :aria-expanded="weatherOpen.toString()"
                            aria-label="Consultar condiciones meteorológicas"
                            title="Condiciones meteorológicas"
                            class="flex items-center justify-center rounded-lg
                                text-[var(--text)] transition">
                            {{-- Conserva aquí el SVG del icono meteorológico --}}
                            <svg
                                class="hover:text-[var(--value-info)]"
                                xmlns="http://www.w3.org/2000/svg"
                                width="22"
                                height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2"/>
                                <path d="M12 20v2"/>
                                <path d="m4.93 4.93 1.42 1.42"/>
                                <path d="m17.65 17.65 1.42 1.42"/>
                                <path d="M2 12h2"/>
                                <path d="M20 12h2"/>
                                <path d="m6.35 17.65-1.42 1.42"/>
                                <path d="m19.07 4.93-1.42 1.42"/>
                            </svg>
                        </button>

                        {{-- Popup meteorológico --}}
                        <div
                            x-cloak
                            x-show="weatherOpen"
                            x-transition
                            class="absolute right-0 top-12 z-50 mt-2
                                w-[min(90vw,820px)] rounded-xl
                                border border-[var(--value-info)]
                                bg-[var(--card)] p-4 shadow-xl"
                        >
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="font-semibold text-[var(--text)]">
                                    Condiciones meteorológicas
                                </h3>

                                <button
                                    type="button"
                                    @click="weatherOpen = false"
                                    aria-label="Cerrar condiciones meteorológicas"
                                    class="rounded-md p-1 text-[var(--text)]
                                        hover:bg-[var(--border)]"
                                >
                                    ✕
                                </button>
                            </div>

                            @include('stints.partials.weather', ['weather' => $weather])
                        </div>
                    </div>

                </td>

                {{-- Fastest --}}
                <td title="{{ __('ui.bestlap') }}" class="flex mx-1 bg-[var(--card)] rounded-lg border border-[var(--border)] text-center items-center px-3">
                    <div class="flex pr-3">
                       <svg fill="CurrentColor" height="25" width="25" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 488.7 488.7" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M145.512,284.7c0,7-5.6,12.8-12.7,12.8c-3.5,0-6.7-1.4-9-3.7c-2.3-2.3-3.7-5.5-3.7-9c0-7,5.6-12.8,12.7-12.8 C139.712,272,145.512,277.7,145.512,284.7z M154.012,348.2c-5,5-4.9,13,0,18l0,0c5,4.9,13.1,4.9,18-0.1c5-5,4.9-13.1-0.1-18 C167.012,343.2,158.913,343.2,154.012,348.2z M235.313,194.5c7,0,12.8-5.7,12.8-12.8s-5.7-12.8-12.8-12.8s-12.8,5.7-12.8,12.8 c0,3.5,1.4,6.7,3.7,9C228.613,193.1,231.813,194.5,235.313,194.5z M153.512,221.2c5,4.9,13.1,4.9,18-0.1l0.1-0.1 c0.1-0.1,0.1-0.1,0.2-0.2c5-5,5-13,0-18s-13.1-5-18,0c-0.1,0.1-0.1,0.1-0.2,0.2c-0.1,0.1-0.1,0.1-0.2,0.2 C148.512,208.1,148.512,216.2,153.512,221.2L153.512,221.2z M235.613,374.3c-7.1,0-12.7,5.7-12.7,12.8c0,3.5,1.4,6.7,3.7,9 s5.5,3.7,9.1,3.7c7,0,12.7-5.7,12.7-12.8C248.413,380,242.712,374.3,235.613,374.3z M299.112,347.8c-5,5-5,13.1,0,18 c5,5,13.1,5,18,0c5-5,4.9-13.1,0-18.1C312.112,342.8,304.013,342.8,299.112,347.8z M338.013,271.5c-7.1,0-12.8,5.7-12.8,12.8 c0,3.5,1.4,6.7,3.7,9c2.3,2.3,5.5,3.8,9,3.7c7.1,0,12.7-5.7,12.8-12.8C350.813,277.2,345.112,271.5,338.013,271.5z M235.913,488.7 c-112.5,0-204.1-91.6-204.1-204.1c0-104.4,78.9-190.7,180.2-202.6V51.1h-12.7c-6.4,0-11.5-5.2-11.5-11.5V11.5 c0-6.4,5.2-11.5,11.5-11.5h73.2c6.4,0,11.5,5.2,11.5,11.5v28.1c0,6.4-5.2,11.5-11.5,11.5h-12.7v30.8 c38.5,4.5,73.7,19.8,102.6,42.7l16.6-16.6l-1.5-1.5c-4.5-4.5-4.5-11.8,0-16.3l22.8-22.8c4.5-4.5,11.8-4.5,16.3,0l36.9,36.9 c4.5,4.5,4.5,11.8,0,16.3l-22.8,22.8c-4.5,4.5-11.8,4.5-16.3,0l-1.5-1.5l-16.6,16.6c27.4,34.7,43.7,78.5,43.7,126 C440.013,397.1,348.413,488.7,235.913,488.7z M392.612,284.6c0-86.4-70.3-156.7-156.7-156.7s-156.7,70.3-156.7,156.7 s70.2,156.7,156.7,156.7C322.313,441.3,392.612,371,392.612,284.6z M317.913,201.8c4.7,4.7,5,12.3,0.7,17.3l-52,60.9 c1.3,9.5-1.6,19.4-8.9,26.7c-12.3,12.3-32.3,12.3-44.7,0c-12.3-12.3-12.3-32.3,0-44.7c7.3-7.3,17.2-10.2,26.7-8.9l60.9-52 C305.712,196.8,313.212,197.1,317.913,201.8L317.913,201.8z M244.413,275.4c-5-5-13.1-5-18,0c-5,5-5,13.1,0,18c5,5,13.1,5,18,0 C249.413,288.4,249.413,280.4,244.413,275.4z"></path> </g> </g> </g></svg>
                    </vid>
                    <div class="flex pl-2 pt-0.5 text-sm font-semibold text-center items-center text-[var(--lap-best)] font-mono-timing">
                        {{ $bestLap !== null ? laptime($bestLap) : '—' }}
                    </div>
                </td>

            </tr>
        </tbody>

    </table>
    </div>
    @if(
    !$setup->setup_file_path ||
    !$setup->track_id ||
    !$setup->car_id
)
    <x-ui.button id="repair-setup-file-btn" data-setup-id="{{ $setup->id }}" class="hover:text-red-600" title="repair setup file" variant="optionbox" size="sm" @click="showAlerts = true">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" width="25" height="25" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <rect x="224.236" y="84.672" transform="matrix(-0.7071 -0.7071 0.7071 -0.7071 251.8026 619.7746)" style="fill:#D7D5D9;" width="60.049" height="346.13"></rect> <path style="fill:#FF3F62;" d="M301.182,253.278l-47.095,47.095c-8.99,8.99-8.99,23.701,0,32.692l153.555,153.555 c8.99,8.99,23.702,8.99,32.692,0l47.095-47.095c8.99-8.99,8.99-23.701,0-32.692L333.874,253.278 C324.884,244.288,310.173,244.288,301.182,253.278z"></path> <polygon style="fill:#D7D5D9;" points="82.211,19.389 20.989,80.611 57.004,116.626 72.013,101.618 227.017,257.43 257.431,227.017 102.427,71.203 118.226,55.404 "></polygon> <g> <rect x="327.715" y="127.531" transform="matrix(-0.7071 -0.7071 0.7071 -0.7071 501.6223 516.2964)" style="opacity:0.3;fill:#3E3B43;enable-background:new ;" width="60.049" height="53.456"></rect> <rect x="129.012" y="331.181" transform="matrix(-0.7071 -0.7071 0.7071 -0.7071 21.903 715.0185)" style="opacity:0.3;fill:#3E3B43;enable-background:new ;" width="60.049" height="43.584"></rect> </g> <g> <path style="fill:#77757E;" d="M488.276,74.56l-50.32,50.32l-50.837-50.835l50.32-50.32l-12.132-12.132 c-15.457-15.457-40.519-15.457-55.976,0l-44.664,44.664c-15.457,15.457-15.457,40.519,0,55.976l75.099,75.099 c15.457,15.457,40.519,15.457,55.976,0l44.665-44.665c15.457-15.457,15.457-40.519,0-55.976L488.276,74.56z"></path> <path style="fill:#77757E;" d="M23.725,437.439l50.32-50.32l50.836,50.836l-50.32,50.32l12.132,12.132 c15.457,15.457,40.519,15.457,55.976,0l44.665-44.665c15.457-15.457,15.457-40.519,0-55.976l-75.099-75.099 c-15.457-15.457-40.519-15.457-55.976,0l-44.665,44.665c-15.457,15.457-15.457,40.519,0,55.976L23.725,437.439z"></path> </g> <path style="fill:#C70024;" d="M301.182,253.278l-47.095,47.095c-8.99,8.99-8.99,23.701,0,32.692l16.919,16.919l79.787-79.787 l-16.919-16.919C324.884,244.288,310.173,244.288,301.182,253.278z"></path> <g> <rect x="207.016" y="220.106" transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 551.4321 228.509)" style="opacity:0.3;fill:#3E3B43;enable-background:new ;" width="42.748" height="16.708"></rect> <rect x="21.519" y="44.57" transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 155.3011 62.1083)" style="opacity:0.07;fill:#3E3B43;enable-background:new ;" width="86.538" height="37.297"></rect> </g> <path style="fill:#FF728B;" d="M427.612,441.54L322.604,336.532c-4.069-4.069-4.069-10.668,0-14.738l0,0 c4.069-4.069,10.668-4.069,14.738,0L442.35,426.803c4.069,4.069,4.069,10.668,0,14.738l0,0 C438.281,445.61,431.682,445.61,427.612,441.54z"></path> </g></svg>
    </x-ui.button>

    @endif

    <div class="relative -right-32 ml-5 w-1/3">

            {{-- <button @click="view='drivetrain'" class="btn">DRIVE</button> --}}

            <div class=" pl-16 gap-2">

                <x-ui.button
                x-on:click="$dispatch(
                    'open-modal',
                    'setup-profile'
                )"
                class="hover:text-[var(--value-info)] w-96 h-8"
                variant="optionbox"
                size="sm"
            >
            {{ $setup->setup_name ?? 'Unnamed Setup' }}
            </x-ui.button>

            </div>
    </div>

</div>

{{--  -------------------  MODAL ---------------- --}}

            <x-modal
            name="setup-profile"
            max-width="lg"
        >

            <div class="p-6 bg-[var(--card)] ">

                <h2 class="text-mds text-[var(--text-card-title)] font-semibold">
                    Setup Profile
                </h2>
                <div class="border-t border-[var(--border)] my-2"></div>
                <div class="mt-4">

                    <div class="text-xs uppercase text-[var(--text)]">
                        Generated Name
                    </div>

                    <div class="mt-2 font-mono text-sm text-[var(--text-muted)]">
                        {{ $setup->setup_name }}
                    </div>

                </div>
                <div class="border-t border-[var(--border)] my-2"></div>
                <form
                    id="setup-profile-form"
                >

                    <div class="grid grid-cols-1s gap-2  uppercase">

                        <div class="mt-3">


                            <div class="grid grid-cols-3 gap-3">

                                {{-- TEST --}}
                                <label
                                    class="cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="T"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'T' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Test
                                    </div>
                                </label>

                                {{-- PRACTICE --}}
                                <label class="cursor-pointer">

                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="P"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'P' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Practice
                                    </div>

                                </label>

                                {{-- QUALIFY --}}
                                <label class="cursor-pointer">

                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="Q"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'Q' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Qualify
                                    </div>

                                </label>

                                {{-- RACE --}}
                                <label class="cursor-pointer">

                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="RAC"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'RAC' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Race
                                    </div>

                                </label>

                                {{-- SPRINT --}}
                                <label class="cursor-pointer">

                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="SPR"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'SPR' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Sprint
                                    </div>

                                </label>

                                {{-- ENDURANCE --}}
                                <label class="cursor-pointer">

                                    <input
                                        type="radio"
                                        name="setup_type"
                                        value="END"
                                        class="hidden peer"
                                        {{ $setup->setup_type === 'END' ? 'checked' : '' }}
                                    >

                                    <div
                                        class="
                                            rounded-lg
                                            border
                                            border-gray-400
                                            p-3
                                            text-center
                                            text-sm

                                            peer-checked:bg-blue-500
                                            peer-checked:text-white
                                            peer-checked:border-blue-500

                                            hover:border-blue-400
                                        "
                                    >
                                        Endurance
                                    </div>

                                </label>

                            </div>

                        </div>





                    </div>
                    <div class="border-t border-[var(--border)] my-2"></div>
                    <div class="mt-6 grid-cols-3">

                        <div class="text-xs uppercase text-gray-500">
                            Weather
                        </div>

                        <div class="mt-2">
                           {{ $stint->track->display_name }} - {{ $setup->setup_weather }} - {{ $stint->car_name }}
                        </div>

                    </div>
                    <div class="border-t border-[var(--border)] my-2"></div>
                    <div class="mt-8 flex justify-end gap-2">

                        <x-ui.button
                            variant="secondary"
                            x-on:click="$dispatch(
                                'close-modal',
                                'setup-profile'
                            )"
                        >
                            Cancel
                        </x-ui.button>

                        <x-ui.button
                            type="button"
                            id="save-setup-profile"
                            variant="add"
                        >
                            Save
                        </x-ui.button>

                    </div>

                </form>

            </div>

        </x-modal>




        <script>

            document

                .getElementById(
                    'save-setup-profile'
                )

                .addEventListener(
                    'click',
                    async () => {

                        const selectedType =

                            document.querySelector(
                                'input[name="setup_type"]:checked'
                            )?.value;

                        const response = await fetch(

                            '{{ route("setups.type", $setup) }}',

                            {
                                method: 'PATCH',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'X-CSRF-TOKEN':

                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .content
                                },

                                body: JSON.stringify({

                                    setup_type:
                                        selectedType
                                })
                            }
                        );

                        const data =
                            await response.json();

                        console.log(data);

                        location.reload();
                    }
                );

            </script>




        </div>



<script>

    document
        .getElementById('install-setup-btn')
        ?.addEventListener('click', async function () {

            try {

                const setupId =
                    this.dataset.setupId;

                const response =
                    await fetch(

                        `/setups/${setupId}/install`,

                        {
                            method: 'POST',

                            headers: {

                                'X-CSRF-TOKEN':

                                    document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                            }
                        }
                    );

                const data =
                    await response.json();

                console.log(
                    'SETUP DATA',
                    data
                );

                console.log(
                    'ENVIANDO A LOGGER',
                    data
                );

                const loggerResponse =
                    await fetch(

                        'http://127.0.0.1:53999/install-setup',

                        {
                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json'
                            },

                            body: JSON.stringify(
                                data
                            )
                        }
                    );

                console.log(
                    'LOGGER STATUS',
                    loggerResponse.status
                );

            } catch (error) {

                console.error(
                    'ERROR INSTALL SETUP',
                    error
                );
            }
        });


        document
    .getElementById('repair-setup-file-btn')
    ?.addEventListener('click', async function () {

        try {

            const setupId = this.dataset.setupId;

            const response = await fetch(

                `/setups/${setupId}/repair-file`,

                {
                    method: 'POST',

                    headers: {

                        'X-CSRF-TOKEN':

                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content
                    }
                }

            );

            const data = await response.json();

            console.log(
                'REPAIR DATA',
                data
            );

            const loggerResponse = await fetch(

                'http://127.0.0.1:53999/repair-setup-file',

                {
                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json'
                    },

                    body: JSON.stringify(
                        data
                    )
                }

            );

            console.log(
                'LOGGER STATUS',
                loggerResponse.status
            );

        } catch (error) {

            console.error(
                'ERROR REPAIR SETUP',
                error
            );
        }

    });
    </script>
