@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="p-6">

    <div class="max-w-4xl mx-auto">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-semibold">
                    Calendarios iRacing
                </h1>

                <p class="mt-1 text-sm opacity-70">
                    PDFs disponibles para el parser.
                </p>

            </div>

            <a
                href="{{ route('admin.season-pdfs.create') }}"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-white"
            >
                Subir PDF
            </a>

        </div>


        @if(session('success'))

            <div class="mb-6 rounded-lg border border-green-500/40 bg-green-500/10 p-4">

                <p class="text-sm text-green-400">
                    {{ session('success') }}
                </p>

            </div>

        @endif


        <div class="rounded-xl border border-[var(--border)] overflow-hidden">

            @forelse($pdfs as $pdf)

                <div class="px-5 py-4 border-b border-[var(--border)] last:border-b-0">

                    <span class="font-mono">
                        {{ $pdf->file }}
                    </span>

                </div>

            @empty

                <div class="p-6 text-sm opacity-60">
                    No hay calendarios registrados.
                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection
