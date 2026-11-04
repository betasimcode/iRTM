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
<label class="text-[var(--text-soft)]">{{ __('ui.iracing_series_name') }}</label>
<input type="text"
name="name"
value="{{ old('name') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Nombre --}}
<div>
<label class="text-[var(--text-soft)]">{{ __('ui.short_name') }}</label>
<input type="text"
name="short_name"
value="{{ old('short_name') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<div>
<label class="text-[var(--text-soft)]">{{ __('ui.info_history') }}</label>
<textarea type="text"
name="serie_info"
value="{{ old('serie_info') }}"
class="w-full bg-[var(--input-bg)] text-[var(--text)] border border-[var(--border)] px-4 py-2 rounded "></textarea>
</div>

{{-- uRL --}}
<div>
<label class="text-[var(--text-soft)]">{{ __('ui.iracing_series_url') }}</label>
<input type="text"
name="ir_url"
value="{{ old('ir_url') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- stats uRL --}}
<div>
<label class="text-[var(--text-soft)]">{{ __('ui.championship_url') }}</label>
<input type="text"
name="stats_url"
value="{{ old('stats_url') }}"
class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- {{ __('ui.iracing_id') }} --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.iracing_id') }}</label>
    <input type="number"
        name="iracing_series_id"
        value="{{ old('iracing_series_id') }}"
        placeholder="{{ __('ui.example_series_id') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.config') }}</h2>
<hr class="border-[var(--card-title)]">

<div class="space-y-2">

    <label class="text-sm font-semibold">

        {{ __('ui.allowed_cars') }}

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
    <label class="text-[var(--text-soft)]">{{ __('ui.discipline') }}</label>
    <select name="discipline"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">{{ __('ui.select') }}</option>
        @foreach(['SPORTS CAR SERIES','FORMULA CAR SERIES','OVAL SERIES','DIRT','OVAL DIRT'] as $discipline)
            <option value="{{ $discipline }}">
                {{ $discipline }}
            </option>
        @endforeach
    </select>
</div>
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.category') }}</label>
    <select name="category"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">{{ __('ui.select') }}</option>
        @foreach(['IMSA','GT','GT3','GT4','GTP','LMP2','LMP3','PROTOTYPE','TCR','F1','F2','F3','F4','FORMULA FORD','FORMULA 2000','FORMULA RENAULT','INDYCAR','NASCAR'] as $class)
            <option value="{{ $class }}" {{ old('class') == $class ? 'selected' : '' }}>
                {{ $class }}
            </option>
        @endforeach
    </select>
</div>
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.licence') }}</label>
    <select name="iracing_class"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="">{{ __('ui.select') }}</option>
        @foreach(['ROOKIE','D','C','B','A','PRO'] as $iracing_class)
            <option value="{{ $iracing_class }}" {{ old('iracing_class') == $iracing_class ? 'selected' : '' }}>
                {{ $iracing_class }}
            </option>
        @endforeach
    </select>
</div>

{{-- Tipo de carrera --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.race_type') }}</label>
    <select name="race_type" id="race_type"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="laps">{{ __('ui.by_laps') }}</option>
        <option value="time">{{ __('ui.by_time') }}</option>
    </select>
</div>

{{-- Tipo de salida --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.start_type') }}</label>
    <select name="start_type"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">

        <option value="standing">{{ __('ui.standing') }}</option>
        <option value="rolling">{{ __('ui.rolling') }}</option>
    </select>
</div>

{{-- Duración --}}
<div id="race_length_wrapper">
    <label class="text-[var(--text-soft)]">{{ __('ui.duration') }}</label>
    <input type="number"
        name="race_length"
        value="{{ old('race_length') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

<br>
<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.strategy') }}</h2>
<hr class="border-[var(--card-title)]">
{{-- Fuel limit --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.fuel_limit') }}</label>
    <input type="number" step="1" name="fuel_limit"
        value="{{ old('fuel_limit') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]"
        placeholder="Ej: 50">
</div>

{{-- Override tanque --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.tank_override') }}</label>
    <input type="number" step="0.1" name="tank_capacity_override"
        value="{{ old('tank_capacity_override') }}"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
</div>

{{-- Checks --}}
<div class="flex gap-6 text-gray-300">
    <label>
        <input type="checkbox" name="mandatory_pit" value="1">
        {{ __('ui.mandatory_pit') }}
    </label>

    <label>
        <input type="checkbox" name="refuel_allowed" value="1" checked>
        {{ __('ui.refuel_allowed') }}
    </label>
</div>



{{-- ============================= --}}
{{-- CONFIGURACIÓN DE CARRERA --}}
{{-- ============================= --}}
<br>
<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.race_configuration') }}</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4">

    {{-- Setup --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.setup') }}</label>
        <select name="setup_type"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
            <option value="open">{{ __('ui.open') }}</option>
            <option value="fixed">{{ __('ui.fixed') }}</option>
        </select>
    </div>

    {{-- Fast repair --}}
    <label class="flex items-center gap-2 text-gray-300 mt-6">
        <input type="checkbox" name="fast_repair" value="1">
        Fast Repair
    </label>

    {{-- Drive Through --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.drive_through_limit') }}</label>
        <input type="number" name="drive_through_limit" value="17"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- DQ --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.disqualification_limit') }}</label>
        <input type="number" name="disqualification_limit" value="25"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

</div>


{{-- ============================= --}}
{{-- REGLAS AVANZADAS --}}
{{-- ============================= --}}

<br>
<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.advanced_rules') }}</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4 text-gray-300">

    <label><input type="checkbox" name="has_additional_penalties" value="1"> {{ __('ui.additional_penalties') }}</label>

    <label><input type="checkbox" name="network_quality_rule" value="1"> {{ __('ui.network_quality_rule') }}</label>

    <label><input type="checkbox" name="grid_by_class" value="1"> {{ __('ui.grid_by_class') }}</label>

    <label><input type="checkbox" name="tire_rules" value="1"> {{ __('ui.tyre_rules') }}</label>

    <label><input type="checkbox" name="quali_tires" value="1"> {{ __('ui.qualifying_tyres') }}</label>

    <label><input type="checkbox" name="joker_laps" value="1"> {{ __('ui.joker_laps') }}</label>

    <label><input type="checkbox" name="team_rules" value="1"> {{ __('ui.team_rules') }}</label>
</div>
{{-- Escrutinio --}}
<div>
    <label class="text-[var(--text-soft)]">{{ __('ui.qualifying_scrutineering') }}</label>
    <select name="quali_scrutiny"
        class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
        <option value="none">{{ __('ui.none') }}</option>
        <option value="lenient">{{ __('ui.lenient') }}</option>
        <option value="strict">{{ __('ui.strict') }}</option>
    </select>
</div>




{{-- ============================= --}}
{{-- CALENDARIO --}}
{{-- ============================= --}}

<br>
<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.calendar') }}</h2>
<hr class="border-[var(--card-title)]">
<div class="grid grid-cols-2 gap-4">

    {{-- Día --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.week_start_day') }}</label>
        <select name="week_start_day"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                <option value="{{ __('ui.' . strtolower($day)) }}" {{ $day == 'Tuesday' ? 'selected' : '' }}>
                    {{ $day }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Hora --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.start_time') }}</label>
        <input type="time" name="week_start_time" value="02:00"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- Intervalo --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.race_interval_minutes') }}</label>
        <input type="number" name="race_interval_minutes" value="120"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

    {{-- Registro --}}
    <div>
        <label class="text-[var(--text-soft)]">{{ __('ui.registration_open_minutes') }}</label>
        <input type="number" name="registration_open_minutes" value="30"
            class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] px-4 py-2 rounded text-[var(--text)]">
    </div>

</div>




<br>
<h2 class="text-[var(--card-title)] font-semibold">{{ __('ui.logo_and_registration') }}</h2>
<hr class="border-[var(--card-title)]">

{{-- Logo --}}
<input type="file" name="logo" class="text-white">


{{-- background --}}
<input type="file" name="background" class="text-white">

<hr class="border-[var(--card-title)]">

<button class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white">
{{ __('ui.create_series') }}
</button>

</form>
</div>

@endsection
