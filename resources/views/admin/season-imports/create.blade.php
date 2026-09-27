@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="p-6">

    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold">
                Nueva configuración de importación
            </h1>

            <p class="mt-1 text-sm opacity-70">
                Define una competición dentro de una temporada del calendario iRacing.
            </p>
        </div>

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-6">

            <form
                method="POST"
                action="{{ route('admin.season-imports.store') }}"
                class="space-y-6"
            >

                @csrf

                {{-- Serie iRacing --}}

                <div>

                    <label
                        for="ir_serie_id"
                        class="block text-sm font-medium mb-2"
                    >
                        Serie iRacing
                    </label>

                    <select
                        id="ir_serie_id"
                        name="ir_serie_id"
                        required
                        class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                    >

                        <option value="">
                            Selecciona una serie
                        </option>

                        @foreach($iracingSeries as $series)

                            <option
                                value="{{ $series->id }}"
                                @selected(old('ir_serie_id') == $series->id)
                            >
                                {{ $series->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('ir_serie_id')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Temporada --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>

                        <label
                            for="year"
                            class="block text-sm font-medium mb-2"
                        >
                            Año
                        </label>

                        <input
                            id="year"
                            name="year"
                            type="number"
                            value="{{ old('year', now()->year) }}"
                            min="2000"
                            max="2100"
                            required
                            class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                        >

                        @error('year')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <div>

                        <label
                            for="season"
                            class="block text-sm font-medium mb-2"
                        >
                            Temporada
                        </label>

                        <select
                            id="season"
                            name="season"
                            required
                            class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                        >

                            <option value="">
                                Selecciona temporada
                            </option>

                            @for($i = 1; $i <= 4; $i++)

                                <option
                                    value="{{ $i }}"
                                    @selected(old('season') == $i)
                                >
                                    S{{ $i }}
                                </option>

                            @endfor

                        </select>

                        @error('season')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Título PDF --}}

                <div>

                    <label
                        for="title"
                        class="block text-sm font-medium mb-2"
                    >
                        Título que busca el parser
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title') }}"
                        required
                        class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                        placeholder="iRacing Lotus 79 Grand Prix Series"
                    >

                    @error('title')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Páginas --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>

                        <label
                            for="page_start"
                            class="block text-sm font-medium mb-2"
                        >
                            Página inicio
                        </label>

                        <input
                            id="page_start"
                            name="page_start"
                            type="number"
                            value="{{ old('page_start') }}"
                            min="1"
                            required
                            class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                        >

                        @error('page_start')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <div>

                        <label
                            for="page_end"
                            class="block text-sm font-medium mb-2"
                        >
                            Página fin
                        </label>

                        <input
                            id="page_end"
                            name="page_end"
                            type="number"
                            value="{{ old('page_end') }}"
                            min="1"
                            required
                            class="w-full rounded-lg border border-[var(--border)] bg-[var(--bg)]"
                        >

                        @error('page_end')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Acciones --}}

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">

                    <a
                        href="{{ route('admin.season-imports.index') }}"
                        class="px-4 py-2 rounded-lg border border-[var(--border)]"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white"
                    >
                        Guardar configuración
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
