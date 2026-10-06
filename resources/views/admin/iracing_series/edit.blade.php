@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto py-10">

<h1 class="text-2x3 font-bold text-[var(--text-title)] mb-6">
Editar {{ $iracingSeries->name }}
</h1>

<form method="POST"
action="{{ route('admin.iracing-series.update', $iracingSeries) }}"
enctype="multipart/form-data"
      class="bg-[var(--card)] p-8 rounded-xl space-y-6">

@csrf
@method('PUT')

{{-- Nombre --}}
<div>
<label class="text-[var(--text-soft)]">iRacing Series Name</label>
<input type="text"
name="name"
value="{{ old('name', $iracingSeries->name) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"></div>

{{-- Nombre --}}
<div>
<label class="text-[var(--text-soft)]">Short name</label>
<input type="text"
name="short_name"
value="{{ old('short_name', $iracingSeries->short_name) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"></div>

<div>
<label class="text-[var(--text-soft)]">Info/History</label>
<textarea type="text"
name="serie_info"
value="{{ old('serie_info', $iracingSeries->serie_info) }}"
class="w-full bg-[var(--input-bg)] text-[var(--text)] border border-[var(--border)] px-4 py-2 rounded "></textarea>
</div>

{{-- uRL --}}
<div>
<label class="text-[var(--text-soft)]">iRacing Series url</label>
<input type="text"
name="ir_url"
value="{{ old('ir_url', $iracingSeries->ir_url) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"></div>

{{-- stats uRL --}}
<div>
<label class="text-[var(--text-soft)]">Championship url</label>
<input type="text"
name="stats_url"
value="{{ old('stats_url', $iracingSeries->stats_url) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"></div>

{{-- iRacing Series ID --}}
<div>
    <label class="text-[var(--text-soft)]">iRacing Series ID</label>
    <input type="number"
        name="iracing_series_id"
        value="{{ old('iracing_series_id', $iracingSeries->iracing_series_id) }}"
        placeholder="Ej: 228"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<h2 class="text-[var(--card-title)] font-semibold">Config</h2>
<hr class="border-[var(--card-title)]">



<div class="space-y-2">

    <label class="text-sm font-semibold">

        Allowed Cars

    </label>

    <div class="grid grid-cols-2 gap-2">

        @foreach($cars as $car)

            <label class="flex items-center gap-2">

                <input
                    type="checkbox"
                    name="cars[]"
                    value="{{ $car->id }}"

                    @checked(
                        isset($iracingSeries)
                        && $iracingSeries
                            ->cars
                            ->contains($car->id)
                    )
                >

                {{ $car->name }}

            </label>

        @endforeach

    </div>

</div>

{{--  ir class --}}
<div>
    <label class="text-[var(--text-soft)]">Discipline</label>
    <select name="discipline"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">        <option value="">Seleccionar</option>
        @foreach(['SPORTS CAR SERIES','FORMULA CAR SERIES','OVAL SERIES','DIRT','OVAL DIRT'] as $discipline)
            <option value="{{ $discipline }}"
            {{ old('discipline', $iracingSeries->discipline) == $discipline ? 'selected' : '' }}>
                {{ $discipline }}
            </option>
        @endforeach
    </select>
</div>

<div>
<label class="text-[var(--text-soft)]">Clase</label>
<select name="iracing_class"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
@foreach(['ROOKIE','D','C','B','A','PRO'] as $ir_class)
<option value="{{ $ir_class }}"
{{ old('iracing_class', $iracingSeries->iracing_class) == $ir_class ? 'selected' : '' }}>
{{ $ir_class }}
</option>
@endforeach
</select>
</div>
{{-- Clase --}}
<div>
<label class="text-[var(--text-soft)]">Category</label>
<select name="category"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">


@foreach(['IMSA','GT','GT3','GT4','GTP','LMP2','LMP3','PROTOTYPE','TCR','F1','F2','F3','F4','FORMULA FORD','FORMULA 2000','FORMULA RENAULT','INDYCAR','NASCAR']  as $class)
<option value="{{ $class }}"
{{ old('category', $iracingSeries->category) == $class ? 'selected' : '' }}>
{{ $class }}
</option>
@endforeach

</select>
</div>


{{-- Tipo --}}
<div>
<label class="text-[var(--text-soft)]">Tipo</label>
<select name="race_type"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

<option value="laps" {{ $iracingSeries->race_type == 'laps' ? 'selected' : '' }}>Vueltas</option>
<option value="time" {{ $iracingSeries->race_type == 'time' ? 'selected' : '' }}>Tiempo</option>

</select>
</div>

{{-- Salida --}}
<div>
<label class="text-[var(--text-soft)]">Salida</label>
<select name="start_type"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

<option value="standing" {{ $iracingSeries->start_type == 'standing' ? 'selected' : '' }}>Parado</option>
<option value="rolling" {{ $iracingSeries->start_type == 'rolling' ? 'selected' : '' }}>Lanzada</option>

</select>
</div>

{{-- Duración --}}
<div>
<label class="text-[var(--text-soft)]">Duración</label>
<input type="number"
name="race_length"
value="{{ old('race_length', $iracingSeries->race_length) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<hr class="border-gray-700">

<h2 class="text-[var(--card-title)] font-semibold">Reglas de estrategia</h2>
<hr class="border-[var(--card-title)]">

{{-- Fuel limit --}}
<div>
<label class="text-[var(--text-soft)]">Fuel limit (%)</label>
<input type="number"
name="fuel_limit"
value="{{ old('fuel_limit', $iracingSeries->fuel_limit) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Tank override --}}
<div>
<label class="ttext-[var(--text-soft)]">Tank override (L)</label>
<input type="number"
name="tank_capacity_override"
value="{{ old('tank_capacity_override', $iracingSeries->tank_capacity_override) }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Checks --}}
<div class="flex gap-6 text-[var(--text-soft)]">
<label>
<input type="checkbox" name="mandatory_pit" value="1"
{{ $iracingSeries->mandatory_pit ? 'checked' : '' }}>
Mandatory pit
</label>

<label>
<input type="checkbox" name="refuel_allowed" value="1"
{{ $iracingSeries->refuel_allowed ? 'checked' : '' }}>
Refuel allowed
</label>
</div>


{{-- ============================= --}}
{{-- CONFIGURACIÓN DE CARRERA --}}
{{-- ============================= --}}

<br>
<h2 class="text-[var(--card-title)] font-semibold">Configuración de carrera</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4">

    {{-- Setup --}}
    <div>
        <label class="text-[var(--text-soft)]">Setup</label>
        <select name="setup_type"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

            <option value="open"
                {{ old('setup_type', $iracingSeries->setup_type) == 'open' ? 'selected' : '' }}>
                Open
            </option>

            <option value="fixed"
                {{ old('setup_type', $iracingSeries->setup_type) == 'fixed' ? 'selected' : '' }}>
                Fixed
            </option>

        </select>
    </div>

    {{-- Fast repair --}}
    <label class="flex items-center gap-2 text-[var(--text-soft)] mt-6">
        <input type="checkbox" name="fast_repair" value="1"
            {{ old('fast_repair', $iracingSeries->fast_repair) ? 'checked' : '' }}>
        Fast Repair
    </label>

    {{-- Drive Through --}}
    <div>
        <label class="text-[var(--text-soft)]">Drive Through (x)</label>
        <input type="number" name="drive_through_limit"
            value="{{ old('drive_through_limit', $iracingSeries->drive_through_limit) }}"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- DQ --}}
    <div>
        <label class="text-[var(--text-soft)]">Disqualification (x)</label>
        <input type="number" name="disqualification_limit"
            value="{{ old('disqualification_limit', $iracingSeries->disqualification_limit) }}"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

</div>

<br>
<h2 class="text-[var(--card-title)] font-semibold">Reglas avanzadas</h2>
<hr class="border-[var(--card-title)]">

<div class="grid grid-cols-2 gap-4 text-[var(--text-soft)]">

    <label>
        <input type="checkbox" name="has_additional_penalties" value="1"
        {{ old('has_additional_penalties', $iracingSeries->has_additional_penalties) ? 'checked' : '' }}>
        Sanciones adicionales
    </label>

    <label>
        <input type="checkbox" name="network_quality_rule" value="1"
        {{ old('network_quality_rule', $iracingSeries->network_quality_rule) ? 'checked' : '' }}>
        Regla calidad red
    </label>

    <label>
        <input type="checkbox" name="grid_by_class" value="1"
        {{ old('grid_by_class', $iracingSeries->grid_by_class) ? 'checked' : '' }}>
        Parrilla por clase
    </label>

    <label>
        <input type="checkbox" name="tire_rules" value="1"
        {{ old('tire_rules', $iracingSeries->tire_rules) ? 'checked' : '' }}>
        Reglas neumáticos
    </label>

    <label>
        <input type="checkbox" name="quali_tires" value="1"
        {{ old('quali_tires', $iracingSeries->quali_tires) ? 'checked' : '' }}>
        Neumáticos clasificación
    </label>

    <label>
        <input type="checkbox" name="joker_laps" value="1"
        {{ old('joker_laps', $iracingSeries->joker_laps) ? 'checked' : '' }}>
        Joker laps
    </label>

    <label>
        <input type="checkbox" name="team_rules" value="1"
        {{ old('team_rules', $iracingSeries->team_rules) ? 'checked' : '' }}>
        Team rules
    </label>

{{-- Scrutineering --}}
<div>
    <label class="text-[var(--text-soft)]">Scrutineering clasificación</label>
    <select name="quali_scrutiny"
        class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--border)] rounded-lg p-2 text-[var(--text)]">

        @foreach(['none','lenient','strict'] as $opt)
            <option value="{{ $opt }}"
                {{ old('quali_scrutiny', $iracingSeries->quali_scrutiny) == $opt ? 'selected' : '' }}>
                {{ ucfirst($opt) }}
            </option>
        @endforeach

    </select>
</div>

</div>


<br>
<h2 class="text-[var(--card-title)] font-semibold">Calendario</h2>
<hr class="border-[var(--card-title)]">

<div class="grid grid-cols-2 gap-4">

    {{-- Día --}}
    <div>
        <label class="text-[var(--text-soft)]">Día inicio semana</label>
        <select name="week_start_day"
            class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--border)] rounded-lg p-2 text-[var(--text)]">

            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                <option value="{{ $day }}"
                    {{ old('week_start_day', $iracingSeries->week_start_day) == $day ? 'selected' : '' }}>
                    {{ $day }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- Hora --}}
    <div>
        <label class="text-[var(--text-soft)]">Hora inicio</label>
        <input type="time" name="week_start_time"
            value="{{ old('week_start_time', $iracingSeries->week_start_time ?? '02:00') }}"
            class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--border)] rounded-lg p-2 text-[var(--text)]">
    </div>

    {{-- Intervalo --}}
    <div>
        <label class="text-[var(--text-soft)]">Intervalo carrera (min)</label>
        <input type="number" name="race_interval_minutes"
            value="{{ old('race_interval_minutes', $iracingSeries->race_interval_minutes) }}"
            class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--border)] rounded-lg p-2 text-[var(--text)]">
    </div>

    {{-- Registro --}}
    <div>
        <label class="text-[var(--text-soft)]">Apertura registro (min)</label>
        <input type="number" name="registration_open_minutes"
            value="{{ old('registration_open_minutes', $iracingSeries->registration_open_minutes) }}"
            class="w-full mt-2 bg-[var(--input-bg)] border border-[var(--border)] rounded-lg p-2 text-[var(--text)]">
    </div>

</div>

<div>


{{-- Logo --}}
@if($iracingSeries->logo_path)
<img src="{{ asset('storage/'.$iracingSeries->logo_path) }}" class="border border-[var(--border)] max-h-44 max-w-auto  my-2">
@else
<svg class="border border-[var(--border)] max-h-44 max-w-auto  my-2" width="168px" height="168px" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracurrentColorerCarrier" stroke-linecurrentcap="round" stroke-linejoin="round"></g><g id="SVGRepo_icurrentColoronCarrier"> <recurrentct width="120" height="120" fill="currentColor"></recurrentct> <path fill-rule="evenodd" currentclip-rule="evenodd" d="M33.2503 38.4816C33.2603 37.0472 34.4199 35.8864 35.8543 35.875H83.1463C84.5848 35.875 85.7503 37.0431 85.7503 38.4816V80.5184C85.7403 81.9528 84.5807 83.1136 83.1463 83.125H35.8543C34.4158 83.1236 33.2503 81.957 33.2503 80.5184V38.4816ZM80.5006 41.1251H38.5006V77.8751L62.8921 53.4783C63.9172 52.4536 65.5788 52.4536 66.6039 53.4783L80.5006 67.4013V41.1251ZM43.75 51.6249C43.75 54.5244 46.1005 56.8749 49 56.8749C51.8995 56.8749 54.25 54.5244 54.25 51.6249C54.25 48.7254 51.8995 46.3749 49 46.3749C46.1005 46.3749 43.75 48.7254 43.75 51.6249Z" fill="#687787"></path> </g></svg>
@endif

<input type="file" name="logo" class="text-[var(--text)]">
</div>

<div>
{{-- background --}}
@if($iracingSeries->background_img)
<img src="{{ asset('storage/'.$iracingSeries->background_img) }}" class="border border-[var(--border)] max-h-168 max-w-168 my-2">
@else
<svg class="border border-[var(--border)] max-h-44 max-w-auto  my-2" width="168px" height="168px" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracurrentColorerCarrier" stroke-linecurrentcap="round" stroke-linejoin="round"></g><g id="SVGRepo_icurrentColoronCarrier"> <recurrentct width="120" height="120" fill="currentColor"></recurrentct> <path fill-rule="evenodd" currentclip-rule="evenodd" d="M33.2503 38.4816C33.2603 37.0472 34.4199 35.8864 35.8543 35.875H83.1463C84.5848 35.875 85.7503 37.0431 85.7503 38.4816V80.5184C85.7403 81.9528 84.5807 83.1136 83.1463 83.125H35.8543C34.4158 83.1236 33.2503 81.957 33.2503 80.5184V38.4816ZM80.5006 41.1251H38.5006V77.8751L62.8921 53.4783C63.9172 52.4536 65.5788 52.4536 66.6039 53.4783L80.5006 67.4013V41.1251ZM43.75 51.6249C43.75 54.5244 46.1005 56.8749 49 56.8749C51.8995 56.8749 54.25 54.5244 54.25 51.6249C54.25 48.7254 51.8995 46.3749 49 46.3749C46.1005 46.3749 43.75 48.7254 43.75 51.6249Z" fill="#687787"></path> </g></svg>
@endif

<input type="file" name="background" class="text-[var(--text)]">

</div>
<br>
<hr class="border-[var(--card-title)]">
<div class=" flex-auto right-1">
    <button class="bg-[var(--btn-bg)] hover:bg-[var(--btn-hover)] px-6 py-2 rounded text-[var(--text)]">
    Actualizar
    </button>
</div>
</form>
</div>

@endsection
