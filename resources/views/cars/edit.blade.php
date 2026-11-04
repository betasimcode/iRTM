@extends('layouts.app')

@section('title', __('ui.edit').' '. __('ui.car'))
@section('page-title', __('ui.edit').' '. __('ui.car'))

@section('content')

<div class="max-w-3xl">

    <div class="bg-[var(--bg)] border-[var(--border)] text-[var(--text-title)] rounded-xl shadow p-8">

        <form action="{{ route('cars.update', $car->id) }}"
              method="POST"
              class="space-y-6" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.name') }}
                </label>
                <input type="text"
                       name="name"
                       value="{{ $car->name }}"
                       required
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.short_name') }}
                </label>
                <input type="text"
                       name="short_name"
                       value="{{ $car->short_name }}"
                       required
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm text-[var(--title)] mb-2">
                    {{ __('ui.brand') }}
                </label>

                <input
                    type="file"
                    name="logo"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.setup_folder') }}
                </label>
                <input type="text"
                       name="iracing_setup_folder"
                       value="{{ $car->iracing_setup_folder }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm text-[var(--title)] mb-2">
                    {{ __('ui.image') }}
                </label>

                <input
                    type="file"
                    name="image"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            {{-- Categoría --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.category') }}
                </label>
                <select name="category"
                class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
                @foreach(['GTP','LMP2','LMP3','GT3','PORSCHE CUP','FERRARI CHALLENGUE','GT4','GT','F1','F2','SF23','SFL','F3','F4','RF1600','RT2000','FORMULA VEE','TCR','LOTUS79'] as $car_cat)
                <option value="{{ $car_cat }}"
                {{ old('category', $car->category) == $car_cat ? 'selected' : '' }}>
                {{ $car_cat }}
                </option>
                @endforeach
                </select>
            </div>

            {{-- Capacidad --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.tank_capacity_label') }} (L)
                </label>
                <input type="number"
                       step="0.1"
                       name="tank_capacity"
                       value="{{ $car->tank_capacity }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- Potencia --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.power_hp') }}
                </label>
                <input type="number"
                       name="power_hp"
                       value="{{ $car->power_hp }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

             {{-- DRIVE --}}
             <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.drive_type') }}
                </label>
                <select name="drive_type"
                class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
                @foreach(['RWD','FWD','AWD','4WD'] as $car_drive)
                <option value="{{ $car_drive }}"
                {{ old('drive_type', $car->drive_type) == $car_drive ? 'selected' : '' }}>
                {{ $car_drive }}
                </option>
                @endforeach
                </select>
            </div>

            {{-- ENGINE POS --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.engine_position') }}
                </label>
                <select name="engine_position"
                class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
                @foreach(['','FRONT','REAR','MID','MIX'] as $car_engpos)
                <option value="{{ $car_engpos }}"
                {{ old('engine_position', $car->engine_position) == $car_engpos ? 'selected' : '' }}>
                {{ $car_engpos === '' ? '' : __('ui.' . strtolower($car_engpos)) }}
                </option>
                @endforeach
                </select>
            </div>

            {{-- Peso --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.weight_kg') }}
                </label>
                <input type="number"
                       name="weight_kg"
                       value="{{ $car->weight_kg }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- wheelbase --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.wheelbase_mm') }}
                </label>
                <input type="number"
                       name="wheelbase_mm"
                       value="{{ $car->wheelbase_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- front track --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.front_track_mm') }}
                </label>
                <input type="number"
                       name="front_track_mm"
                       value="{{ $car->front_track_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- rear track --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.rear_track_mm') }}
                </label>
                <input type="number"
                       name="rear_track_mm"
                       value="{{ $car->rear_track_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- Neumático --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    {{ __('ui.tyre_type') }}
                </label>
                <input type="text"
                       name="tyre_type"
                       value="{{ $car->tyre_type }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- Potencia --}}
            {{-- <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Potencia (HP)
                </label>
                <input type="number"
                       name="power_hp"
                       value="{{ $car->power_hp }}"
                       class="w-full bg-gray-900 border border-gray-700 px-4 py-2 rounded text-white">
            </div> --}}

            {{-- Botones --}}
            <div class="flex justify-between pt-4">

                <a href="{{ route('cars.index') }}"
                   class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600
                          text-gray-200 rounded-lg text-sm transition">
                    {{ __('ui.cancel') }}
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white rounded-lg text-sm font-medium
                               transition shadow">
                    {{ __('ui.update_car') }}
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
