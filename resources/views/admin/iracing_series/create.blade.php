@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto py-10">

<h1 class="text-2x3 font-bold text-[var(--text-title)] mb-6">
Crear iRacing Series
</h1>

<form method="POST"
      action="{{ route('admin.iracing-series.store') }}"
      enctype="multipart/form-data"
      class="bg-[var(--card)] p-8 rounded-xl space-y-6">

@csrf

{{-- Nombre --}}
<div>
<label class="text-[var(--text-soft)]">iRacing Series Name</label>
<input type="text"
name="name"
value="{{ old('name') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Nombre --}}
<div>
<label class="text-[var(--text-soft)]">Short name</label>
<input type="text"
name="short_name"
value="{{ old('short_name') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<div>
<label class="text-[var(--text-soft)]">Info/History</label>
<textarea type="text"
name="serie_info"
value="{{ old('serie_info') }}"
class="w-full bg-[var(--input-bg)] text-[var(--text)] border border-[var(--border)] px-4 py-2 rounded "></textarea>
</div>

{{-- uRL --}}
<div>
<label class="text-[var(--text-soft)]">iRacing Series url</label>
<input type="text"
name="ir_url"
value="{{ old('ir_url') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- stats uRL --}}
<div>
<label class="text-[var(--text-soft)]">Championship url</label>
<input type="text"
name="stats_url"
value="{{ old('stats_url') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- iRacing Series ID --}}
<div>
    <label class="text-[var(--text-soft)]">iRacing Series ID</label>
    <input type="number"
        name="iracing_series_id"
        value="{{ old('iracing_series_id') }}"
        placeholder="Ej: 228"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>
<div>
    <x-input-label for="car_class_id" value="Car Class ID" />

    <x-text-input
        id="car_class_id"
        name="car_class_id"
        type="number"
        min="0"
        class="mt-1 block w-full"
        :value="old('car_class_id')"
    />

    <x-input-error
        :messages="$errors->get('car_class_id')"
        class="mt-2"
    />
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

{{-- Clase coche --}}
<div>
    <label class="text-[var(--text-soft)]">Discipline</label>
    <select name="discipline"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">Seleccionar</option>
        @foreach(['SPORTS CAR SERIES','FORMULA CAR SERIES','OVAL SERIES','DIRT','OVAL DIRT'] as $discipline)
            <option value="{{ $discipline }}">
                {{ $discipline }}
            </option>
        @endforeach
    </select>
</div>
<div>
    <label class="text-[var(--text-soft)]">Category</label>
    <select name="category"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">Seleccionar</option>
        @foreach(['IMSA','GT','GT3','GT4','GTP','LMP2','LMP3','PROTOTYPE','TCR','F1','F2','F3','F4','FORMULA FORD','FORMULA 2000','FORMULA RENAULT','INDYCAR','NASCAR'] as $class)
            <option value="{{ $class }}" {{ old('class') == $class ? 'selected' : '' }}>
                {{ $class }}
            </option>
        @endforeach
    </select>
</div>
<div>
    <label class="text-[var(--text-soft)]">Licencia</label>
    <select name="iracing_class"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">Seleccionar</option>
        @foreach(['ROOKIE','D','C','B','A','PRO'] as $iracing_class)
            <option value="{{ $iracing_class }}" {{ old('iracing_class') == $iracing_class ? 'selected' : '' }}>
                {{ $iracing_class }}
            </option>
        @endforeach
    </select>
</div>

{{-- Tipo de carrera --}}
<div>
    <label class="text-[var(--text-soft)]">Tipo de carrera</label>
    <select name="race_type" id="race_type"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="laps">Por vueltas</option>
        <option value="time">Por tiempo</option>
    </select>
</div>

{{-- Tipo de salida --}}
<div>
    <label class="text-[var(--text-soft)]">Salida</label>
    <select name="start_type"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="standing">Parado</option>
        <option value="rolling">Lanzada</option>
    </select>
</div>

{{-- Duración --}}
<div id="race_length_wrapper">
    <label class="text-[var(--text-soft)]">Duración</label>
    <input type="number"
        name="race_length"
        value="{{ old('race_length') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<br>
<h2 class="text-[var(--card-title)] font-semibold">Reglas de estrategia</h2>
<hr class="border-[var(--card-title)]">
{{-- Fuel limit --}}
<div>
    <label class="text-[var(--text-soft)]">Límite de combustible (%)</label>
    <input type="number" step="1" name="fuel_limit"
        value="{{ old('fuel_limit') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"
        placeholder="Ej: 50">
</div>

{{-- Override tanque --}}
<div>
    <label class="text-[var(--text-soft)]">Override depósito (L)</label>
    <input type="number" step="0.1" name="tank_capacity_override"
        value="{{ old('tank_capacity_override') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Checks --}}
<div class="flex gap-6 text-gray-300">
    <label>
        <input type="checkbox" name="mandatory_pit" value="1">
        Parada obligatoria
    </label>

    <label>
        <input type="checkbox" name="refuel_allowed" value="1" checked>
        Repostaje permitido
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
            <option value="open">Open</option>
            <option value="fixed">Fixed</option>
        </select>
    </div>

    {{-- Fast repair --}}
    <label class="flex items-center gap-2 text-gray-300 mt-6">
        <input type="checkbox" name="fast_repair" value="1">
        Fast Repair
    </label>

    {{-- Drive Through --}}
    <div>
        <label class="text-[var(--text-soft)]">Drive Through (x)</label>
        <input type="number" name="drive_through_limit" value="17"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- DQ --}}
    <div>
        <label class="text-[var(--text-soft)]">Disqualification (x)</label>
        <input type="number" name="disqualification_limit" value="25"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

</div>


{{-- ============================= --}}
{{-- REGLAS AVANZADAS --}}
{{-- ============================= --}}

<br>
<h2 class="text-[var(--card-title)] font-semibold">Reglas avanzadas</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4 text-gray-300">

    <label><input type="checkbox" name="has_additional_penalties" value="1"> Sanciones adicionales</label>

    <label><input type="checkbox" name="network_quality_rule" value="1"> Regla calidad red</label>

    <label><input type="checkbox" name="grid_by_class" value="1"> Parrilla por clase</label>

    <label><input type="checkbox" name="tire_rules" value="1"> Reglas neumáticos</label>

    <label><input type="checkbox" name="quali_tires" value="1"> Neumáticos clasificación</label>

    <label><input type="checkbox" name="joker_laps" value="1"> Joker laps</label>

    <label><input type="checkbox" name="team_rules" value="1"> Team rules</label>
</div>
{{-- Escrutinio --}}
<div>
    <label class="text-[var(--text-soft)]">Scrutineering clasificación</label>
    <select name="quali_scrutiny"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
        <option value="none">None</option>
        <option value="lenient">Lenient</option>
        <option value="strict">Strict</option>
    </select>
</div>




{{-- ============================= --}}
{{-- CALENDARIO --}}
{{-- ============================= --}}

<br>
<h2 class="text-[var(--card-title)] font-semibold">Calendario</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4">

    {{-- Día --}}
    <div>
        <label class="text-[var(--text-soft)]">Día inicio semana</label>
        <select name="week_start_day"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                <option value="{{ $day }}" {{ $day == 'Tuesday' ? 'selected' : '' }}>
                    {{ $day }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Hora --}}
    <div>
        <label class="text-[var(--text-soft)]">Hora inicio</label>
        <input type="time" name="week_start_time" value="02:00"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- Intervalo --}}
    <div>
        <label class="text-[var(--text-soft)]">Intervalo carrera (min)</label>
        <input type="number" name="race_interval_minutes" value="120"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- Registro --}}
    <div>
        <label class="text-[var(--text-soft)]">Apertura registro (min)</label>
        <input type="number" name="registration_open_minutes" value="30"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

</div>




<br>
<h2 class="text-[var(--card-title)] font-semibold">Logo y registro</h2>
<hr class="border-[var(--card-title)]">

{{-- Logo --}}
<input type="file" name="logo" class="text-white">


{{-- background --}}
<input type="file" name="background" class="text-white">

<hr class="border-[var(--card-title)]">

<button class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white">
Crear serie
</button>

</form>
</div>

@endsection
