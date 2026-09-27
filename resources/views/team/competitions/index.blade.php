@extends('layouts.app')

@section('title', 'iRTM ata Competitions')
@section('page-title', 'Team Competitions')

@section('content')

<div class="space-y-8">

    <div class="flex items-start justify-between gap-4">

    <div>
        <h2 class="text-2xl font-bold text-[var(--text)]">
            Team Competitions
        </h2>

        <p class="text-sm text-[var(--text-muted)] mt-1">
            Competitions in which your team participates.
        </p>
    </div>

    <a
        href="{{ route('team.competitions.create') }}"
                        class="inline-flex text-center items-center rounded-lg button-min bg-green-600 hover:bg-green-700 px-4 py-2 text-white font-medium transition">
        <span></span>
        Register
    </a>

</div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

        @forelse($competitions as $entry)

            <x-competition-card
                :competition="$entry->series"
                :href="route(
                    'team.competitions.show',
                    $entry->series
                )"
            />

        @empty

            <div class="md:col-span-2 xl:col-span-3
                        rounded-xl
                        border border-dashed border-[var(--border)]
                        p-8 text-center">

                <div class="text-4xl mb-4">
                    🏁
                </div>

                <h4 class="font-semibold text-[var(--text)]">
                    No team competitions
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-2">
                    Your team is not currently registered
                    in any competitions.
                </p>

            </div>

        @endforelse

    </div>

</div>

@endsection
