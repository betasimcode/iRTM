@extends('layouts.app')

@section('title', __('ui.add_circuit'))
@section('page-title', __('ui.add_circuit'))

@section('content')

<div class="max-w-3xl">

    <div class="max-w-3xl mx-auto bg-[var(--bg)] border-[var(--border)] rounded-xl shadow overflow-hidden"">

        <form action="{{ route('circuits.store') }}" method="POST" class="rounded-2xl p-8 space-y-6" enctype="multipart/form-data">

            @csrf


            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.display_name') }}
                </label>
                <input type="text"
                       name="display_name"

                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.name') }}
                </label>
                <input type="text"
                       name="name"

                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>

            {{-- Variante --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.variant') }}
                </label>
                <input type="text"
                       name="variant"

                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.iracing_id') }}
                </label>
                <input type="text"

                       name="iracing_track_id"
                       value=""
                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.short_name') }}
                </label>
                <input type="text"
                       name="short_name"

                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
        {{-- LOGO --}}
            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    {{ __('ui.logo') }}
                </label>

                <input
                    type="file"
                    name="logo"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    {{ __('ui.logo_dark') }}
                </label>

                <input
                    type="file"
                    name="logo_dark"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
                <label class="block text-sm text-[var(--text-title)] mb-2">
                    {{ __('ui.logo_light') }}
                </label>

                <input
                    type="file"
                    name="logo_light"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">
            </div>

            <div>
             <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                {{ __('ui.circuit_type') }}
            </label>

            <select name="type"
            class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text)]">

                <option value="ROAD">{{ __('ui.road') }}</option>
                <option value="OVAL">{{ __('ui.oval') }}</option>
                <option value="DIRT ROAD">{{ __('ui.dirt_road') }}</option>
                <option value="DIRT OVAL">{{ __('ui.dirt_oval') }}</option>
            </select>
        </div>
            <div>
                <label class="block text-lg text-[var(--text-title)] mb-2">
                    {{ __('ui.map') }}
                </label>

                <input
                    type="file"
                    name="map"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border)] rounded-lg px-4 py-2 uppercase   text-[var(--text)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.map_svg') }}
                </label>
                <input type="text"
                       name="map_svg"

                       class="w-full bg-[var(--input-bg)] border-[var(--border)] rounded-lg px-4 py-2 uppercase text-[var(--text-soft)]">
            </div>
            {{-- Longitud --}}
            <div>
                <label class="block text-sm font-medium text-[var(--text-title)] mb-2">
                    {{ __('ui.length_meters') }}
                </label>
                <input type="number"
                        disabled
                       step="0.01"
                       name="length_km"

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
                    {{ __('ui.cancel') }}
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-[var(--text-title)] rounded-lg text-sm font-medium
                               transition shadow">
                    {{ __('ui.add_circuit') }}
                </button>



            </div>

        </form>

    </div>

</div>

@endsection
