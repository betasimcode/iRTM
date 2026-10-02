<div class="space-y-4">

    <div
        class="flex items-center justify-between"
    >

        <div>

            <h2
                class="text-lg font-semibold
                       text-[var(--text-card-title)]"
            >
                Stints
            </h2>

            <p
                class="mt-1 text-xs
                       text-[var(--text-muted)]"
            >
                {{ $stints->count() }}
                tandas
                ·
                {{ match($reportScope) {
                    'week' => 'Semana',
                    'all' => 'Histórico',
                    default => 'Temporada',
                } }}
            </p>

        </div>

    </div>


    @if($stints->isEmpty())

        <div
            class="rounded-xl
                   border border-[var(--border)]
                   bg-[var(--bg)]
                   p-8 text-center"
        >

            <p
                class="text-sm
                       text-[var(--text-muted)]"
            >
                No hay stints disponibles
                para este período.
            </p>

        </div>

    @else

        <div
            class="overflow-hidden
                   rounded-xl
                   border border-[var(--border)]"
        >

            <table class="w-full text-sm">

                <thead
                    class="border-b
                           border-[var(--border)]
                           bg-[var(--bg)]"
                >

                    <tr>

                        <th
                            class="px-4 py-3
                                   text-left
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Driver
                        </th>

                        <th
                            class="px-4 py-3
                                   text-left
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Circuit
                        </th>

                        <th
                            class="px-4 py-3
                                   text-right
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Laps
                        </th>

                        <th
                            class="px-4 py-3
                                   text-right
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Best
                        </th>

                        <th
                            class="px-4 py-3
                                   text-right
                                   text-xs uppercase
                                   text-[var(--text-muted)]"
                        >
                            Fuel
                        </th>

                    </tr>

                </thead>


                <tbody
                    class="divide-y
                           divide-[var(--border)]"
                >

                    @foreach($stints as $stint)

                        <tr
                            class="transition
                                   hover:bg-[var(--bg)]"
                        >

                            <td
                                class="px-4 py-3
                                       text-[var(--text)]"
                            >
                                {{ $stint->user?->name ?? '—' }}
                            </td>


                            <td
                                class="px-4 py-3
                                       text-[var(--text-muted)]"
                            >
                                {{ $stint->track?->name ?? '—' }}
                            </td>


                            <td
                                class="px-4 py-3
                                       text-right
                                       text-[var(--value-data)]"
                            >
                                {{ $stint->laps_count }}
                            </td>


                            <td
                                class="px-4 py-3
                                       text-right
                                       text-[var(--lap-best)]"
                            >
                                {{ $stint->laps->min('lap_time')
                                    ? lapTime(
                                        $stint->laps->min('lap_time')
                                    )
                                    : '—'
                                }}
                            </td>


                            <td
                                class="px-4 py-3
                                       text-right
                                       text-[var(--value-data)]"
                            >
                                {{ $stint->avg_fuel !== null
                                    ? number_format(
                                        $stint->avg_fuel,
                                        2
                                    ) . ' L'
                                    : '—'
                                }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @endif

</div>
