@extends('layouts.app')

@section('title', 'Editar Circuito')
@section('page-title', 'Editar Circuito')

@section('content')

<div class="max-w-3xl">

    <div class="max-w-3xl mx-auto bg-[var(--bg)] border-[var(--border)] rounded-xl shadow overflow-hidden"">

        <form action="{{ route('circuits.update', $circuit->id) }}"
              method="POST"
              class="rounded-2xl p-8 space-y-6"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Display
                </label>
                <input type="text"
                       name="display_name"
                       value="{{ $circuit->display_name }}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Nombre del Circuito
                </label>
                <input type="text"
                        disabled
                       name="name"
                       value="{{ $circuit->name }}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            {{-- Variante --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Variante
                </label>
                <input type="text"
                       name="variant"
                       value="{{ $circuit->variant }}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    iRacing ID
                </label>
                <input type="text"

                       name="iracing_track_id"
                       value="{{ $circuit->iracing_track_id }}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Short Name
                </label>
                <input type="text"
                       name="short_name"
                       value="{{ $circuit->short_name }}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
        {{-- LOGO --}}
            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    Logo
                </label>

                <input
                    type="file"
                    name="logo"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    Logo Dark
                </label>

                <input
                    type="file"
                    name="logo_dark"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    Logo Light
                </label>

                <input
                    type="file"
                    name="logo_light"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
             <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                Tipo
            </label>

            <select name="type"
            class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">

                <option value="ROAD">ROAD</option>
                <option value="OVAL">OVAL</option>
                <option value="DIRT ROAD">DIRT ROAD</option>
                <option value="DIRT OVAL">DIRT OVAL</option>
            </select>
        </div>
            <div>
                <label class="block text-lg text-[var(--text-title)] mb-2">
                    Map
                </label>

                <input
                    type="file"
                    name="map"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase   text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Map svg
                </label>
                <input type="text"
                       name="map_svg"
                       value="{{ $circuit->map_svg}}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            {{-- Longitud --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Longitud (m)
                </label>
                <input type="number"
                       step="0.01"
                       name="length_km"
                       value="{{ $circuit->length_km * 1000}}"
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase   text-[var(--text)]">
            </div>

            {{-- Vueltas estándar
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Vueltas estándar
                </label>
                <input type="number"
                       name="laps_standard"
                       value="{{ $circuit->laps_standard }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-[var(--text-title)]">
            </div> --}}

            {{-- Consumo
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    Consumo aproximado por vuelta (L)
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_per_lap"
                       value="{{ $circuit->fuel_per_lap }}"
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-[var(--text-title)]">
            </div> --}}

            {{-- Botones --}}
            <div class="flex justify-between pt-4">

                <a href="{{ route('circuits.index') }}"
                   class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600
                          text-gray-200 rounded-lg text-sm transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-[var(--text-title)] rounded-lg text-sm font-medium
                               transition shadow">
                    Actualizar circuito
                </button>



            </div>

        </form>

    </div>

</div>

@endsection
