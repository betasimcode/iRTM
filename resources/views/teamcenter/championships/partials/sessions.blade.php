<div class="space-y-5">

    {{-- CONTEXT --}}

    @if($activeRound)

        <div
            class="flex flex-wrap items-center justify-between gap-3
                   rounded-xl
                   border border-[var(--border)]
                   bg-[var(--bg)]
                   px-4 py-3"
        >

            <div>

                <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">
                    {{ __('ui.active_round') }}
                </p>

                <p class="mt-1 text-sm font-semibold text-[var(--text-title)]">
                    {{ __('ui.round') }} {{ $activeRound->round_number }}
                    ·
                    {{ $activeRound->track?->display_name ?? __('ui.track') }}
                </p>

            </div>

            @if($competitionCar)

                <div class="text-right">

                    <p class="text-xs uppercase tracking-wide text-[var(--text-muted)]">
                        {{ __('ui.car') }}
                    </p>

                    <p class="mt-1 text-sm font-semibold text-[var(--text-title)]">
                        {{ $competitionCar->name ?? __('ui.competition_car') }}
                    </p>

                </div>

            @endif

        </div>

    @endif


    {{-- MESSAGE --}}

    @if($message)

        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--bg)]
                   p-8
                   text-center"
        >

            <p class="text-sm text-[var(--text-muted)]">
                {{ $message }}
            </p>

        </div>

    @elseif($sessions->isEmpty())

        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--bg)]
                   p-8
                   text-center"
        >

            <p class="text-sm text-[var(--text-muted)]">
                {{ __('ui.no_sessions_registered_this_round') }}
            </p>

        </div>

    @else

        {{-- SESSION LIST --}}

        <div
            class="overflow-hidden
                   rounded-xl
                   border border-[var(--border)]
                   bg-[var(--bg)]"
        >

            <div
                class="divide-y
                       divide-[var(--border)]"
            >

                @foreach($sessions as $session)

                    <div
                        class="flex flex-wrap
                               items-center
                               justify-between
                               gap-4
                               px-5 py-4
                               hover:bg-[var(--card)]
                               transition"
                    >

                        <div>

                            <p
                                class="text-sm
                                       font-semibold
                                       text-[var(--text-title)]"
                            >
                                {{ __('ui.session_number', ['id' => $session->id]) }}
                            </p>

                            <p
                                class="mt-1
                                       text-xs
                                       text-[var(--text-muted)]"
                            >
                                {{ $session->created_at?->format('d/m/Y H:i') }}
                            </p>

                        </div>


                        <div
                            class="flex
                                   items-center
                                   gap-6
                                   text-sm"
                        >

                            <div class="text-center">

                                <p
                                    class="text-[10px]
                                           uppercase
                                           tracking-wide
                                           text-[var(--text-muted)]"
                                >
                                    {{ __('ui.stints') }}
                                </p>

                                <p
                                    class="mt-1
                                           font-semibold
                                           text-[var(--text-title)]"
                                >
                                    {{ $session->stints_count }}
                                </p>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>


        {{-- PAGINATION --}}

        @if($sessions instanceof \Illuminate\Contracts\Pagination\Paginator)

            <div class="pt-2">
                {{ $sessions->links() }}
            </div>

        @endif

    @endif

</div>
