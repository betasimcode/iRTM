@extends('layouts.app')

@section('title', 'Coches')
@section('page-title', __('ui.admincar'))

@section('content')

<div class="flex justify-between items-center mb-6">

    {{-- <a href="{{ route('cars.create') }}"
       class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
              text-white text-sm font-medium rounded-lg transition shadow">
        + Nuevo coche
    </a> --}}

</div>


@if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-600/20 border border-emerald-600 text-emerald-400 text-sm">
        {{ session('success') }}
    </div>
@endif


<div class="bg-[var(--bg)] border border-[var(--border)] rounded-xl shadow-md overflow-hidden">

    <div class="overflow-x-auto">

        <table class="min-w-full text-m">

            <thead class="bg-[var(--card)] border-[var(--border)] text-[var(--text-title)]  uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Imagen</th>
                    <th class="px-4 py-2 text-left">Brand</th>
                    <th class="px-4 py-2 text-left">iR-ID</th>
                    <th class="px-4 py-2 text-left">Model</th>
                    <th class="px-4 py-2 text-left">Code</th>
                    <th class="px-4 py-2 text-left">Depósito</th>
                    <th class="px-4 py-2 text-left">Categoría</th>
                    <th class="px-4 py-2 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[var(--div)]">

                @foreach($cars as $car)
                <tr class="hover:bg-[var(--card-hover)] text-[var(--text)] transition">

                    <td class="w-3 px-6 py-4 text-xs">
                        {{ $car->id }}
                    </td>
                    <td class="px-4 py-2 w-64">

                    @if($car->image_path)
                        <img
                            src="{{ asset('storage/'.$car->image_path) }}"
                            class="h-9 w-auto object-contain">
                    @endif

                    </td>
                    <td class="px-4 py-2 w-64">

                    @if($car->logo_path)
                        <img
                            src="{{ asset('storage/'.$car->logo_path) }}"
                            class="h-9 w-auto object-contain">
                    @endif

                    </td>
                    <td class="px-6 py-4  text-xs w-96">
                        {{ $car->iracing_car_id }}
                    </td>

                    <td class="px-6 py-4 text-xs w-96">
                        {{ $car->name }}
                    </td>
                    <td class="px-6 py-4 text-xs w-96">
                        {{ $car->short_name }}
                    </td>
                    <td class="px-6 py-4 dark:text-green-300 text-xs text-green-700 w-48">
                        {{ $car->tank_capacity ?? '—' }} L
                    </td>

                    <td class="px-6 py-4 text-xs">
                        {{ $car->category ?? '—' }}
                    </td>

                    <td class="flex px-6 pt-4 text-right space-x-4">

                        <x-icon-action icon="edit" label="Editar coche" :href="route('cars.edit', $car->id)" />

                        <form action="{{ route('cars.destroy', $car->id) }}"
                              method="POST"
                              class="inline">
                            @csrf
                            @method('DELETE')

                            <x-icon-action icon="delete" label="Eliminar coche" variant="danger" type="submit" onclick="return confirm('¿Seguro que quieres eliminar este coche?')" />
                        </form>

                    </td>

                </tr>
                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection
