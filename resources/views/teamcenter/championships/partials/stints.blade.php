<div
    class="flex items-center justify-between"
>
    <div class="flex mb-4 mx-4">
        <p
            class="mt-1 text-xs
                   text-[var(--text-title)]"
        >
        Obtenidos
            {{ $stints->total() }}
            registros de
            {{ match($reportScope) {
                'week' => 'esta Semana',
                'all' => 'todo el Histórico',
                default => 'esta Temporada',
            } }}

            en

            @if($stints->isNotEmpty() && $stints->first()?->track?->name)
                {{ $stints->first()->track->display_name }} - {{ $stints->first()->track->variant }}
            @endif
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
                               text-[var(--text-title)]"
                    >
                        Stint
                    </th>

                    <th
                        class="px-4 py-3
                               text-left
                               text-xs uppercase
                               text-[var(--text-title)]"
                    >
                        Date
                    </th>

                    <th
                        class="px-4 py-3
                               text-left
                               text-xs uppercase
                               text-[var(--text-title)]"
                    >
                        Driver
                    </th>

                    <th
                        class="px-4 py-3
                               text-right
                               text-xs uppercase
                               text-[var(--text-title)]"
                    >
                        Laps
                    </th>

                    <th
                        class="px-4 py-3
                               text-right
                               text-xs uppercase
                               text-[var(--text-title)]"
                    >
                        Best
                    </th>

                    <th
                        class="px-4 py-3
                               text-right
                               text-xs uppercase
                               text-[var(--text-title)]"
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
                            #{{ $stint->id ?? '—' }}
                        </td>

                        <td
                            class="px-4 py-3
                                   text-[var(--text)]"
                        >
                            {{ $stint->updated_at ?? '—' }}
                        </td>


                        <td
                            class="px-4 py-3
                                   text-[var(--text-muted)]"
                        >
                            {{ $stint->user?->name ?? '—' }}
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


    @if($stints->hasPages())

        <div
            class="flex items-center justify-between
                   pt-2"
        >

            <div
                class="text-xs
                       text-[var(--text-muted)]"
            >
                Mostrando
                {{ $stints->firstItem() }}
                –
                {{ $stints->lastItem() }}
                de
                {{ $stints->total() }}
            </div>


            <div
                class="flex items-center gap-1"
            >

                @if($stints->onFirstPage())

                    <span
                        class="px-3 py-1.5
                               rounded-lg
                               border border-[var(--border)]
                               text-xs
                               text-[var(--text-muted)]
                               opacity-50"
                    >
                        Anterior
                    </span>

                @else

                    @php
                        $previousUrl = $stints->previousPageUrl();

                        $previousUrl .=
                            (str_contains($previousUrl, '?')
                                ? '&'
                                : '?')
                            . http_build_query([
                                'report_scope' => $reportScope,
                            ]);
                    @endphp

                    <a
                        href="{{ $previousUrl }}"
                        onclick="
                            event.preventDefault();

                            const root = this.closest('[x-data]');
                            const component = Alpine.$data(root);

                            component.loading = true;

                            fetch(this.href, {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(
                                        'HTTP ' + response.status
                                    );
                                }

                                return response.text();
                            })
                            .then(html => {
                                component.toolContent = html;
                            })
                            .catch(error => {
                                console.error(error);

                                component.error =
                                    'No se ha podido cargar la página de stints.';
                            })
                            .finally(() => {
                                component.loading = false;
                            });
                        "
                        class="px-3 py-1.5
                               rounded-lg
                               border border-[var(--border)]
                               text-xs
                               text-[var(--text-muted)]
                               hover:text-[var(--text)]
                               hover:bg-[var(--bg)]
                               transition"
                    >
                        Anterior
                    </a>

                @endif


                @for($page = 1; $page <= $stints->lastPage(); $page++)

                    @php
                        $pageUrl = $stints->url($page);

                        $pageUrl .=
                            (str_contains($pageUrl, '?')
                                ? '&'
                                : '?')
                            . http_build_query([
                                'report_scope' => $reportScope,
                            ]);
                    @endphp

                    @if($page == $stints->currentPage())

                        <span
                            class="px-3 py-1.5
                                   rounded-lg
                                   border border-[var(--border)]
                                   bg-[var(--btn-app)]
                                   text-xs
                                   font-semibold
                                   text-[var(--text-title)]"
                        >
                            {{ $page }}
                        </span>

                    @else

                        <a
                            href="{{ $pageUrl }}"
                            onclick="
                                event.preventDefault();

                                const root = this.closest('[x-data]');
                                const component = Alpine.$data(root);

                                component.loading = true;

                                fetch(this.href, {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'text/html'
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(
                                            'HTTP ' + response.status
                                        );
                                    }

                                    return response.text();
                                })
                                .then(html => {
                                    component.toolContent = html;
                                })
                                .catch(error => {
                                    console.error(error);

                                    component.error =
                                        'No se ha podido cargar la página de stints.';
                                })
                                .finally(() => {
                                    component.loading = false;
                                });
                            "
                            class="px-3 py-1.5
                                   rounded-lg
                                   border border-[var(--border)]
                                   text-xs
                                   text-[var(--text-muted)]
                                   hover:text-[var(--text)]
                                   hover:bg-[var(--bg)]
                                   transition"
                        >
                            {{ $page }}
                        </a>

                    @endif

                @endfor


                @if($stints->hasMorePages())

                    @php
                        $nextUrl = $stints->nextPageUrl();

                        $nextUrl .=
                            (str_contains($nextUrl, '?')
                                ? '&'
                                : '?')
                            . http_build_query([
                                'report_scope' => $reportScope,
                            ]);
                    @endphp

                    <a
                        href="{{ $nextUrl }}"
                        onclick="
                            event.preventDefault();

                            const root = this.closest('[x-data]');
                            const component = Alpine.$data(root);

                            component.loading = true;

                            fetch(this.href, {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(
                                        'HTTP ' + response.status
                                    );
                                }

                                return response.text();
                            })
                            .then(html => {
                                component.toolContent = html;
                            })
                            .catch(error => {
                                console.error(error);

                                component.error =
                                    'No se ha podido cargar la página de stints.';
                            })
                            .finally(() => {
                                component.loading = false;
                            });
                        "
                        class="px-3 py-1.5
                               rounded-lg
                               border border-[var(--border)]
                               text-xs
                               text-[var(--text-muted)]
                               hover:text-[var(--text)]
                               hover:bg-[var(--bg)]
                               transition"
                    >
                        Siguiente
                    </a>

                @else

                    <span
                        class="px-3 py-1.5
                               rounded-lg
                               border border-[var(--border)]
                               text-xs
                               text-[var(--text-muted)]
                               opacity-50"
                    >
                        Siguiente
                    </span>

                @endif

            </div>

        </div>

    @endif

@endif
