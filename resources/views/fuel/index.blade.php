@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto py-8 space-y-8">

    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Calculadora de combustible
        </h2>
    </div>

    <form method="GET"
          action="{{ route('fuel.index') }}"
          class="bg-white shadow rounded-xl p-6 space-y-6">

        {{-- Coche --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Coche
            </label>
            <select name="car"
                    id="carSelect"
                    required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">

                <option value="">Selecciona coche</option>

                @foreach($cars as $car)
                    <option value="{{ $car }}"
                        {{ (isset($selectedCar) && $selectedCar==$car)?'selected':'' }}>
                        {{ $car }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Circuito --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Circuito
            </label>
            <select name="track"
                    id="trackSelect"
                    required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecciona circuito</option>
            </select>
        </div>

        {{-- Duración --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Duración carrera (min)
            </label>
            <input name="minutes"
                   value="{{ $minutes ?? '' }}"
                   required
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- Contingencia --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Vueltas de contingencia
            </label>
            <select name="extra_laps"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @for($i=0;$i<=3;$i++)
                    <option value="{{ $i }}"
                        {{ request('extra_laps',1)==$i ? 'selected':'' }}>
                        {{ $i }} vueltas
                    </option>
                @endfor
            </select>
        </div>

        {{-- Tráfico --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tráfico estimado tras parada (seg)
            </label>
            <input type="number"
                   step="0.1"
                   name="traffic_loss"
                   value="{{ request('traffic_loss',0) }}"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow">
                Calcular
            </button>
        </div>

    </form>


    {{-- RESULTADO --}}
    @if(isset($fuel))
    <div class="bg-white shadow rounded-xl p-6 space-y-4">

        <h3 class="text-lg font-semibold text-gray-800">
            Resultado
        </h3>

        <p><strong>{{ $fuel }} litros</strong> para {{ $minutes }} min</p>
        <p>Consumo estimado: <strong>{{ $fuel_per_lap }} L/vuelta</strong></p>
        <p>Vueltas estimadas: <strong>{{ $estimated_laps }}</strong></p>

        @if(isset($strategy) && $strategy)
        <hr class="my-4">

        <h4 class="font-semibold">Estrategia</h4>

        @if($strategy['stops']==0)
            <p>Sin parada — salir con <strong>{{ $strategy['start_fuel'] }} L</strong></p>
        @else
            <p>Paradas: <strong>{{ $strategy['stops'] }}</strong></p>
            <p>Combustible salida: <strong>{{ $strategy['start_fuel'] }} L</strong></p>

            @foreach($strategy['refuels'] as $i => $refuel)
                <p>Parada {{ $i+1 }}: repostar <strong>{{ $refuel }} L</strong></p>
            @endforeach
        @endif
        @endif

        @if(isset($pit_windows) && count($pit_windows))
        <hr class="my-4">
        <h4 class="font-semibold">Ventanas de parada</h4>
        @foreach($pit_windows as $i=>$w)
            <p>Parada {{ $i+1 }}: <strong>Vuelta {{ $w['from'] }} → {{ $w['to'] }}</strong></p>
        @endforeach
        @endif

        @if(isset($strategy_call))
        <hr class="my-4">
        <h4 class="font-semibold">Decisión estratégica</h4>
        <p><strong>{{ $strategy_call }}</strong></p>
        <p>Diferencia estimada: {{ $undercut_gain }} s</p>
        @endif

    </div>
    @endif


    {{-- ERROR --}}
    @if(!empty($calculated) && isset($error))
        <div class="p-3 bg-red-100 text-red-800 rounded">
            {{ $error }}
        </div>
    @endif

</div>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const carSelect = document.getElementById('carSelect');
    const trackSelect = document.getElementById('trackSelect');

    async function loadTracks(car, selected=null)
    {
        if(!car) return;

        trackSelect.innerHTML='<option>Cargando...</option>';

        try {
            const res = await fetch('/api/tracks/'+encodeURIComponent(car),{
                headers:{'Accept':'application/json'}
            });

            const data = await res.json();

            trackSelect.innerHTML='<option value="">Selecciona circuito</option>';

            data.forEach(t=>{
                const sel = (t===selected)?'selected':'';
                trackSelect.innerHTML += `<option value="${t}" ${sel}>${t}</option>`;
            });

        } catch(e){
            trackSelect.innerHTML='<option>Error cargando circuitos</option>';
        }
    }

    carSelect.addEventListener('change', ()=>{
        loadTracks(carSelect.value);
    });

    @if(!empty($selectedCar))
        loadTracks(@json($selectedCar), @json($selectedTrack));
    @endif

});
</script>

@endsection
