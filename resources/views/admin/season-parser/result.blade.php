@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="max-w-6xl mx-auto py-8 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[var(--text)]">
            Resultado de la importación
        </h1>

        <p class="mt-2 text-sm text-[var(--text-soft)]">
            {{ $result['year'] }} S{{ $result['season'] }}
        </p>
    </div>

    @if ($result['successful'])

        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 p-5">
            <div class="text-lg font-semibold text-green-800">
                ✓ Importación completada correctamente
            </div>

            <p class="mt-1 text-sm text-green-700">
                El parser finalizó sin errores.
            </p>
        </div>

    @else

        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-5">
            <div class="text-lg font-semibold text-red-800">
                ✗ La importación ha fallado
            </div>

            <p class="mt-1 text-sm text-red-700">
                Código de salida: {{ $result['exit_code'] }}
            </p>
        </div>

    @endif

    <div class="grid gap-6 md:grid-cols-3">

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-[var(--text-title)]">
                Temporada
            </div>

            <div class="mt-2 text-xl font-semibold text-[var(--text)]">
                {{ $result['year'] }} S{{ $result['season'] }}
            </div>
        </div>

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">
                PDF
            </div>

            <div class="mt-2 text-xl font-semibold text-[var(--text)]">
                {{ $result['pdf'] }}
            </div>
        </div>

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wide text-gray-500">
                Configuraciones
            </div>

            <div class="mt-2 text-xl font-semibold text-[var(--text)]">
                {{ $result['imports'] }}
            </div>
        </div>

    </div>

    <div class="mt-6 rounded-xl border border-[var(--border)] bg-[var(--card)]">

        <div class="border-b border-[var(--border)] px-6 py-4">
            <h2 class="font-semibold text-[var(--text-title)]">
                Salida del parser
            </h2>
        </div>

        <pre class="max-h-[600px] overflow-auto whitespace-pre-wrap p-6 text-xs leading-relaxed text-[var(--text)]">{{ $result['output'] }}</pre>

    </div>

    @if (!empty($result['error_output']))

        <div class="mt-6 rounded-xl border border-red-300 bg-red-50 shadow-sm">

            <div class="border-b border-red-200 px-6 py-4">
                <h2 class="font-semibold text-red-800">
                    Error output
                </h2>
            </div>

            <pre class="overflow-auto whitespace-pre-wrap p-6 text-xs leading-relaxed text-red-700">{{ $result['error_output'] }}</pre>

        </div>

    @endif

    <div class="mt-6 flex justify-between">

        <a
            href="{{ route('admin.season-parser.create') }}"
            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm
                   font-semibold text-[var(--btn-text)] bg-[var(--btn-bg)]
                   hover:bg-[var(--btn-hover)]"
        >
            ← Volver al parser
        </a>

    </div>

</div>

@endsection
