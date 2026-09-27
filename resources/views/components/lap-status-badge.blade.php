<div class="flex items-center gap-1">

    {{-- Número de vuelta --}}
    <span>{{ $lap->lap_number }}</span>

    {{-- PIT --}}
    @if($lap->is_pit_lap)
        <span class="text-red-400 text-xs">(P)</span>
    @endif

    {{-- INVALID LAP --}}
    @if(!$lap->is_clean)
        <span 
             class="hover:text-red-500 dark:text-gray-600 text-gray-400 cursor-help ml-1"
            title="Vuelta inválida - {{ $lap->incident_count }} inc / {{ $lap->offtrack_count }} offtracks"
        >
        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13.0619 4.4295C12.6213 3.54786 11.3636 3.54786 10.9229 4.4295L3.89008 18.5006C3.49256 19.2959 4.07069 20.2317 4.95957 20.2317H19.0253C19.9142 20.2317 20.4923 19.2959 20.0948 18.5006L13.0619 4.4295ZM9.34196 3.6387C10.434 1.45376 13.5508 1.45377 14.6429 3.63871L21.6758 17.7098C22.6609 19.6809 21.2282 22 19.0253 22H4.95957C2.75669 22 1.32395 19.6809 2.3091 17.7098L9.34196 3.6387Z" fill="CurrentColor"></path> <path d="M12 8V13" stroke="#DF1463" stroke-width="1.7" stroke-linecap="round"></path> <path d="M12 16L12 16.5" stroke="#DF1463" stroke-width="1.7" stroke-linecap="round"></path> </g></svg>

        </span>
        @elseif(!$lap->is_complete_lap)
        <span title="Accident / Retired" class="text-red-500 cursor-help ml-1">
            <svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" viewBox="0 0 48.312 48.312" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M42.624,25.483l-5.091-9.054c-0.312-0.553-0.896-0.895-1.53-0.895h-8.529c-0.971,0-1.754,0.785-1.754,1.754v8.16h-6.029 v-10.14c0-0.585-0.292-1.133-0.781-1.458L8.392,6.841L8.386,6.837L8.381,6.833C7.742,6.445,7.127,6.321,6.527,6.641 C5.93,6.958,5.588,7.605,5.655,8.277c0,0.002,0,0.002,0,0.003c0,0.002,0,0.004,0,0.007S5.654,8.292,5.654,8.293v8.553 c0,0.591-0.479,1.073-1.073,1.073c-0.593,0-1.073-0.482-1.073-1.073c0-0.97-0.785-1.754-1.754-1.754 C0.786,15.092,0,15.877,0,16.847c0,2.526,2.056,4.581,4.582,4.581s4.582-2.055,4.582-4.581v-3.853l4.093,4.723v7.729H3.732 c-0.969,0-1.754,0.786-1.754,1.754v8.366c0,0.97,0.785,1.754,1.754,1.754H5.03c0.594,2.592,2.906,4.533,5.674,4.533 c2.77,0,5.083-1.941,5.677-4.533h16.068c0.596,2.592,2.907,4.533,5.674,4.533c2.771,0,5.084-1.941,5.678-4.533h2.756 c0.971,0,1.755-0.784,1.755-1.754V31.47C48.312,28.266,45.787,25.662,42.624,25.483z M10.693,38.338 c-1.279,0-2.314-1.037-2.314-2.314c0-1.278,1.038-2.315,2.314-2.315c1.279,0,2.316,1.037,2.316,2.315 C13.009,37.301,11.973,38.338,10.693,38.338z M29.228,25.447v-6.406h5.75l3.604,6.406H29.228z M38.138,38.338 c-1.279,0-2.314-1.037-2.314-2.314c0-1.278,1.037-2.315,2.314-2.315c1.276,0,2.313,1.037,2.313,2.315 C40.451,37.301,39.416,38.338,38.138,38.338z"></path> </g> </g> </g></svg>
        </span>
         @endif


</div>
