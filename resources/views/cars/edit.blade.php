@extends('layouts.app')

@section('title', 'Editar Coche')
@section('page-title', 'Editar Coche')

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
                    Nombre
                </label>
                <input type="text"
                       name="name"
                       value="{{ $car->name }}"
                       required
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Short name
                </label>
                <input type="text"
                       name="short_name"
                       value="{{ $car->short_name }}"
                       required
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm text-[var(--title)] mb-2">
                    Marca
                </label>

                <input
                    type="file"
                    name="logo"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Setup folder
                </label>
                <input type="text"
                       name="iracing_setup_folder"
                       value="{{ $car->iracing_setup_folder }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm text-[var(--title)] mb-2">
                    Imagen
                </label>

                <input
                    type="file"
                    name="image"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>
            {{-- Categoría --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Categoría
                </label>
                <select name="category"
                class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
                @foreach(['','GT3','GT4','GT','F1','F2','F3','F4','TCR'] as $car_cat)
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
                    Capacidad de combustible (L)
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
                    Potencia hp
                </label>
                <input type="number"
                       name="power_hp"
                       value="{{ $car->power_hp }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

             {{-- DRIVE --}}
             <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Drive type
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
                    Engine position
                </label>
                <select name="engine_position"
                class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
                @foreach(['','FRONT','REAR','MID','MIX'] as $car_engpos)
                <option value="{{ $car_engpos }}"
                {{ old('engine_position', $car->engine_position) == $car_engpos ? 'selected' : '' }}>
                {{ $car_engpos }}
                </option>
                @endforeach
                </select>
            </div>

            {{-- Peso --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Weight kg
                </label>
                <input type="number"
                       name="weight_kg"
                       value="{{ $car->weight_kg }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- wheelbase --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Wheel Base (mm)
                </label>
                <input type="number"
                       name="wheelbase_mm"
                       value="{{ $car->wheelbase_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- front track --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Front Track (mm)
                </label>
                <input type="number"
                       name="front_track_mm"
                       value="{{ $car->front_track_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- rear track --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Rear track (mm)
                </label>
                <input type="number"
                       name="rear_track_mm"
                       value="{{ $car->rear_track_mm }}"
                       class="w-full bg-[var(--input-bg)] border border-[var(--border)] px-4 py-2 rounded text-[var(--text)]">
            </div>

            {{-- Neumático --}}
            <div>
                <label class="block text-sm font-medium text-[var(--title)] mb-2">
                    Tipo de neumático
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
                    Cancelar
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white rounded-lg text-sm font-medium
                               transition shadow">
                    Actualizar coche
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
