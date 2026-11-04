@extends('layouts.app')

@section('title', __('ui.new_session'))
@section('page-title', __('ui.new_session'))

@section('content')

<div class="max-w-3xl">

    <div class="bg-gray-800 border border-gray-700 rounded-2xl shadow p-8">

        <form action="{{ route('race_sessions.store') }}"
              method="POST"
              class="space-y-6">

            @csrf

            {{-- Si viene round_id oculto --}}
            @if(request()->has('round'))
                <input type="hidden" name="round_id" value="{{ request('round') }}">
            @endif

            {{-- Tipo sesión --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.session_type') }}
                </label>

                <select name="session_type" class="form-input">
                    <option value="practice">{{ __('ui.practice') }}</option>
                    <option value="qualy">{{ __('ui.qualifying') }}</option>
                    <option value="race">{{ __('ui.race') }}</option>
                </select>
            </div>

            {{-- Si NO es oficial, elegir circuito --}}
            @if(!request()->has('round'))

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.circuit') }}
                </label>

                <select name="circuit_id" class="form-input">
                    @foreach($circuits as $circuit)
                        <option value="{{ $circuit->id }}">
                            {{ $circuit->name }} ({{ $circuit->variant }})
                        </option>
                    @endforeach
                </select>
            </div>

            @endif

            {{-- Coche --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.car') }}
                </label>

                <select name="car_id" class="form-input">
                    @foreach($cars as $car)
                        <option value="{{ $car->id }}">
                            {{ $car->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mejor vuelta --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.best_lap_seconds') }}
                </label>

                <input type="number"
                       step="0.001"
                       name="lap_time"
                       class="form-input">
            </div>

            {{-- Fuel --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                    {{ __('ui.fuel_used') }}
                </label>

                <input type="number"
                       step="0.01"
                       name="fuel_used"
                       class="form-input">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white rounded-lg text-sm font-medium shadow transition">
                    {{ __('ui.save') }}
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
