@extends('layouts.app')

@section('title', __('ui.new').' '. __('ui.car'))
@section('page-title', __('ui.new').' '. __('ui.car'))

@section('content')

<div class="max-w-3xl">

    <div class="bg-gray-800 border border-gray-700 rounded-xl shadow p-8">

        <form action="{{ route('cars.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.name') }}
                </label>
                <input type="text"
                       name="name"
                       required
                       class="form-input">
            </div>

            {{-- Categoría --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.category') }}
                </label>
                <input type="text"
                       name="category"
                       placeholder="{{ __('ui.car_category_examples') }}"
                       class="form-input">
            </div>

            {{-- Tank --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.tank_capacity_label') }} (L)
                </label>
                <input type="number"
                       step="0.1"
                       name="tank_capacity"
                       class="form-input">
            </div>

            {{-- Consumo --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.fuel_consumption_per_lap') }}
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_consumption"
                       class="form-input">
            </div>

            {{-- Neumático --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.tyre_type') }}
                </label>
                <input type="text"
                       name="tyre_type"
                       placeholder="{{ __('ui.soft') }}, {{ __('ui.medium') }}, {{ __('ui.hard') }}"
                       class="form-input">
            </div>

            {{-- Potencia --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.power_hp') }}
                </label>
                <input type="number"
                       name="power_hp"
                       class="form-input">
            </div>

            {{-- Botones --}}
            <div class="flex justify-between pt-4">

                <a href="{{ route('cars.index') }}"
                   class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg text-sm transition">
                    {{ __('ui.cancel') }}
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                               rounded-lg text-sm font-medium transition shadow">
                    {{ __('ui.save_car') }}
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
