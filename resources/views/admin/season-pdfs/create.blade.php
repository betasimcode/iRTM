@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="p-6">

    <div class="max-w-3xl mx-auto">

        <div class="mb-6">

            <h1 class="text-2xl font-semibold">
                Subir calendario iRacing
            </h1>

            <p class="mt-1 text-sm opacity-70">
                El archivo será renombrado automáticamente
                según la temporada seleccionada.
            </p>

        </div>


        @if ($errors->any())

            <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 p-4">

                @foreach ($errors->all() as $error)

                    <p class="text-sm text-red-400">
                        {{ $error }}
                    </p>

                @endforeach

            </div>

        @endif


        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-6">

            <form
                method="POST"
                action="{{ route('admin.season-pdfs.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >

                @csrf


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

                    </div>

                </div>


                <div>

                    <label
                        for="file"
                        class="block text-sm font-medium mb-2"
                    >
                        Archivo PDF
                    </label>

                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept="application/pdf"
                        required
                        class="block w-full text-sm"
                    >

                    <p class="mt-2 text-xs opacity-60">
                        El nombre original del archivo no se conservará.
                    </p>

                </div>


                <div class="rounded-lg border border-[var(--border)] p-4">

                    <p class="text-sm opacity-70">
                        Nombre que utilizará el parser:
                    </p>

                    <p
                        id="generated-filename"
                        class="mt-1 font-mono font-semibold"
                    >
                        Selecciona año y temporada
                    </p>

                </div>


                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">

                    <a
                        href="{{ route('admin.season-pdfs.index') }}"
                        class="px-4 py-2 rounded-lg border border-[var(--border)]"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white"
                    >
                        Subir PDF
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

    const yearInput =
        document.getElementById('year');

    const seasonInput =
        document.getElementById('season');

    const filename =
        document.getElementById('generated-filename');

    function updateFilename()
    {
        const year =
            yearInput.value;

        const season =
            seasonInput.value;

        if (year && season) {

            filename.textContent =
                `${year}_${season}.pdf`;

        } else {

            filename.textContent =
                'Selecciona año y temporada';

        }
    }

    yearInput.addEventListener(
        'input',
        updateFilename
    );

    seasonInput.addEventListener(
        'change',
        updateFilename
    );

    updateFilename();

</script>

@endsection
