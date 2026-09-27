@extends('layouts.app')

@section('title', 'iRTM ' . __('ui.champs'))
@section('page-title', __('ui.champs'))

@section('content')

<div class="space-y-8">

    {{-- Barra superior --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div class="flex ">

            <h2 class="text-3xl font-microsport uppercase text-[var(--text)] align-middle pt-3 mr-5">
                2026 Season 3
            </h2>
            <img
                    src="{{ asset(
                        'storage/' .
                        auth()->user()->iracing_helmet_path
                    ) }}"
                    alt="{{ auth()->user()->name }}"
                    class="h-16 w-16
                           object-contain"
                >
        </div>

        <div class="flex gap-3">

            {{-- DRIVER WORKSPACE --}}

            <a
                href="{{ route('competitions.create') }}"
                class="inline-flex text-center items-center rounded-lg button-min bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white font-medium transition"
            >
                REGISTER
            </a>

            <a
                href="{{ route('competitions.create', ['private' => 1]) }}"
                class="inline-flex text-center items-center rounded-lg button-min bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white font-medium transition""
            >
                MAKE PRIVATE CHAMP
            </a>

            {{-- Private Driver registration
            <a
                href="{{ route('competitions.create') }}"
                class="inline-flex text-center items-center rounded-lg button-min bg-blue-600 hover:bg-blue-700 px-4 py-2 text-white font-medium transition"
            >
                🏁 Register
            </a> --}}

            {{-- Existing private championship action --}}
            {{-- <a
                href="#"
                class="inline-flex text-center items-center rounded-lg button-min border border-[var(--border)] px-4 py-2 hover:bg-[var(--card-hover)] transition"
            >
                ➕ Make private champ
            </a> --}}

        </div>

    </div>
    <hr class="border-[var(--border)]">
    {{-- Cards --}}
    <div>

        <h3 class="text-lg font-semibold mb-4">

            {{ __('ui.active_champs') }}

        </h3>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($competitions as $competition)

                <x-competition-card
                    :competition="$competition->series"
                    :entry="$competition"
                    :href="route(
                        'competitions.show',
                        $competition->series
                    )"
                    :type="$competition->participation_type"
                />

            @endforeach


        </div>

    </div>

</div>

@endsection
