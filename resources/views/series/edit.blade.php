@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto py-10">

    <h1 class="text-2xl font-bold text-white mb-6">
        Editar {{ $series->name }}
    </h1>

    <form action="{{ route('series.update', $series) }}"
          method="POST"
          class="bg-gray-800 border border-gray-700 rounded-2xl p-8 space-y-4">

        @csrf
        @method('PUT')

        <div>
            <label class="block text-lg text-gray-400 mb-2">
                Año
            </label>

            <input type="number"
                   name="season_year"
                   value="{{ $series->season_year }}"
                   required disabled
                   class="w-full bg-gray-700 text-gray-400 border border-gray-700 px-4 py-2 rounded cursor-not-allowed">
        </div>

        <div>
            <label class="block text-lg text-gray-400 mb-2">
                Temporada
            </label>

            <select name="season_number " disabled
                    class="w-full bg-gray-700 text-gray-400 border border-gray-700 px-4 py-2 rounded cursor-not-allowed">
                <option value="1">S1</option>
                <option value="2">S2</option>
                <option value="3">S3</option>
                <option value="4">S4</option>
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-2">
                Estado
            </label>

            <select name="status"
                    class="w-full bg-gray-900 border border-gray-700
                        rounded-lg px-4 py-2 text-white">

                <option value="draft" {{ $series->status == 'draft' ? 'selected' : '' }}>
                    Borrador
                </option>

                <option value="active" {{ $series->status == 'active' ? 'selected' : '' }}>
                    Activa
                </option>

                <option value="archived" {{ $series->status == 'archived' ? 'selected' : '' }}>
                    Finalizada
                </option>

            </select>
        </div>

        <div class="flex justify-between items-center">

            {{-- ACTUALIZAR --}}
            <form action="{{ route('series.update', $series) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- campos -->

                <button type="submit"
                        class="bg-indigo-600 px-4 py-2 rounded">
                    Actualizar
                </button>
            </form>

             {{-- CANCELAR --}}
            <div class="space-x-4">
                <a href="{{ route('series.show', $series) }}"
                   class="px-4 py-2 bg-gray-700 hover:bg-gray-600
                          text-white rounded-lg">
                    Cancelar
                </a>
            </div>
            {{-- ELIMINAR --}}
            <div x-data="{ open: false }">

                <button @click="open = true"
                        type="button"
                        class="text-red-500 hover:text-red-400">
                    Eliminar
                </button>

                {{-- MODAL --}}
                <div x-show="open"
                    x-transition
                    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">

                    <div @click.away="open = false"
                        class="bg-gray-800 border border-gray-700
                                rounded-2xl p-8 w-96 space-y-6">

                        <h3 class="text-lg font-semibold text-white">
                            Confirmar eliminación
                        </h3>

                        <p class="text-sm text-gray-400">
                            Esta acción no se puede deshacer.
                        </p>

                        <div class="flex justify-end space-x-4">

                            <button @click="open = false"
                                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600
                                        text-white rounded-lg">
                                Cancelar
                            </button>

                            <form action="{{ route('series.destroy', $series) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700
                                            text-white rounded-lg">
                                    Eliminar
                                </button>
                            </form>

                        </div>

                    </div>
                </div>

            </div>




        </div>

    </form>

</div>

@endsection
