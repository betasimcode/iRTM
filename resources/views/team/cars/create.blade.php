@extends('layouts.app')

@section('content')

<div class="max-w-xl mx-auto">

    <h2 class="text-xl font-bold mb-4">Añadir coche al equipo</h2>

    <form method="POST" action="{{ route('team-cars.store') }}">
        @csrf

        {{-- CAR MODEL --}}
        <div class="mb-3">
            <label class="block text-sm font-medium">Modelo</label>
            <select name="car_id" class="w-full border rounded p-2">
                @foreach($cars as $car)
                    <option value="{{ $car->id }}">
                        {{ $car->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NUMBER --}}
        <div class="mb-3">
            <label class="block text-sm font-medium">Número</label>
            <input type="text" name="number" class="w-full border rounded p-2">
        </div>

        {{-- LIVERY --}}
        <div class="mb-3">
            <label class="block text-sm font-medium">Livery</label>
            <input type="text" name="livery_file" class="w-full border rounded p-2">
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Guardar
        </button>

    </form>

</div>

@endsection