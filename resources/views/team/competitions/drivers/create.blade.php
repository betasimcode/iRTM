@extends('layouts.app')

@section('title', 'Add Team Driver')
@section('page-title', 'Add Team Driver')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-8">

    <div class="mb-8">

        <p
            class="text-sm uppercase
                   tracking-[0.18em]
                   text-[var(--text-muted)]"
        >
            {{ $series->name }}
        </p>

        <h2
            class="mt-2 text-3xl font-bold
                   text-[var(--text)]"
        >
            Add Driver
        </h2>

        <p
            class="mt-2 text-sm
                   text-[var(--text-muted)]"
        >
            Select a member of your Team to participate
            in this competition.
        </p>

    </div>


    <form
        method="POST"
        action="{{ route(
            'team.competitions.drivers.store',
            $series
        ) }}"
    >

        @csrf


        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--card)]
                   overflow-hidden"
        >

            @forelse($teamMembers as $member)

                <label
                    class="flex items-center gap-4
                           px-5 py-4
                           border-b
                           border-[var(--border)]
                           last:border-b-0
                           cursor-pointer
                           hover:bg-[var(--card-hover)]
                           transition"
                >

                    <input
                        type="radio"
                        name="user_id"
                        value="{{ $member->id }}"
                        required
                    >

                    <div
                        class="w-10 h-10 overflow-hidden shrink-0">

                        @if($member->iracing_helmet_path)

                            <img
                                src="{{ asset("/storage/" .
                                    $member->iracing_helmet_path
                                ) }}"
                                alt="{{ $member->name }}"
                                class="w-full h-full
                                       object-cover"
                            >

                        @endif

                    </div>


                    <div class="flex-1">

                        <div
                            class="font-medium
                                   text-[var(--text)]"
                        >
                            {{ $member->name }}
                        </div>

                        <div
                            class="text-xs
                                   text-[var(--text-muted)]"
                        >
                            {{ $member->driver_role
                                ?? 'Team Member' }}
                        </div>

                    </div>

                </label>

            @empty

                <div class="p-8 text-center">

                    <p
                        class="text-sm
                               text-[var(--text-muted)]"
                    >
                        All Team members are already
                        participating in this competition.
                    </p>

                </div>

            @endforelse

        </div>


        <div
            class="mt-6 flex
                   justify-end
                   gap-3"
        >

            <a
                href="{{ route(
                    'team.competitions.show',
                    $series
                ) }}"
                class="px-5 py-2.5
                       rounded-lg
                       border border-[var(--border)]
                       text-sm
                       text-[var(--text)]"
            >
                Cancel
            </a>

            @if($teamMembers->isNotEmpty())

                <button
                    type="submit"
                    class="px-6 py-2.5
                           rounded-lg
                           bg-[var(--accent)]
                           text-white
                           text-sm font-semibold
                           hover:opacity-90"
                >
                    Add Driver
                </button>

            @endif

        </div>

    </form>

</div>

@endsection
