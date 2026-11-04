@extends('teamcenter.layouts.a')

@section('title', __('ui.teamcenter_championships'))

@section('teamcenter-main')

<div class="mx-auto w-full max-w-7xl px-6 py-8">

    {{-- HEADER --}}

    <div class="mb-8">

        <h1
            class="text-2xl font-semibold tracking-tight
                   text-[var(--text)]"
        >
            {{ __('ui.teamcenter_championships') }}
        </h1>

        <p
            class="mt-2 text-sm
                   text-[var(--text-muted)]"
        >
            {{ __('ui.championships_available_teamcenter') }}
        </p>

    </div>


    {{-- EMPTY STATE --}}

    @if($championships->isEmpty())

        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--card)]
                   p-8"
        >

            <p
                class="text-sm
                       text-[var(--text-muted)]"
            >
                {{ __('ui.no_championships_available') }}
            </p>

        </div>

    @else

        {{-- CHAMPIONSHIP GRID --}}

        <div
            class="grid
                   gap-6
                   md:grid-cols-2
                   xl:grid-cols-3"
        >

            @foreach($championships as $championship)

                <article
                    class="rounded-xl
                           border border-[var(--border)]
                           bg-[var(--card)]
                           p-5
                           transition
                           hover:border-[var(--text-muted)]"
                >

                    {{-- NAME --}}

                    <div>

                        <h2
                            class="text-lg
                                   font-semibold
                                   text-[var(--text)]"
                        >
                            {{ $championship->name }}
                        </h2>

                        <p
                            class="mt-1
                                   text-sm
                                   text-[var(--text-muted)]"
                        >
                            {{ $championship->season_year }}
                            ·
                            S{{ $championship->season_number }}
                        </p>

                    </div>


                    {{-- PARTICIPATION --}}

                    <div class="mt-5">

                        @if($championship->team_participating)

                            <span
                                class="inline-flex
                                       items-center
                                       rounded-full
                                       border
                                       border-[var(--border)]
                                       px-3
                                       py-1
                                       text-xs
                                       font-medium
                                       text-[var(--value-data)]"
                            >
                                {{ __('ui.team_participating') }}
                            </span>

                        @else

                            <span
                                class="inline-flex
                                       items-center
                                       rounded-full
                                       border
                                       border-[var(--border)]
                                       px-3
                                       py-1
                                       text-xs
                                       font-medium
                                       text-[var(--text-muted)]"
                            >
                                {{ __('ui.not_registered') }}
                            </span>

                        @endif

                    </div>


                    {{-- ACTION --}}

                    <div class="mt-6">

                        <a
                            href="{{ route(
                                'teamcenter.championships.show',
                                $championship
                            ) }}"
                            class="inline-flex
                                items-center
                                justify-center
                                rounded-lg
                                border
                                border-[var(--border)]
                                px-4
                                py-2
                                text-sm
                                font-medium
                                text-[var(--text)]
                                transition
                                hover:bg-[var(--bg)]"
                        >
                            {{ __('ui.view_championship') }}
                        </a>

                    </div>

                </article>

            @endforeach

        </div>

    @endif

</div>

@endsection
