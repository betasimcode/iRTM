<div

                class="border-t border-[var(--border)]

                       bg-[var(--card)] p-4"

            >

                <div

                    class="mb-3 flex flex-wrap

                           items-center justify-between gap-2"

                >

                    <div>

                        <h3

                            class="text-sm font-semibold

                                   text-[var(--text-title)]"

                        >

                            Referencia de sectores

                        </h3>



                        <p class="mt-1 text-xs text-[var(--text-muted)]">

                            Límites obtenidos del IBT

                        </p>

                    </div>



                    @if (!empty($sessionData['sectors']['count']))

                        <span

                            class="rounded-md border

                                   border-[var(--border)]

                                   px-2 py-1 text-xs

                                   text-[var(--text-muted)]"

                        >

                            {{ $sessionData['sectors']['count'] }} sectores

                        </span>

                    @endif

                </div>



                @if (!empty($sessionData['sectors']['segments']))

                    <div

                        class="grid grid-cols-1 gap-2

                               sm:grid-cols-2 xl:grid-cols-3"

                    >

                        @foreach (

                            $sessionData['sectors']['segments']

                            as $sector

                        )

                            <div

                                class="rounded-lg border

                                       border-[var(--border)]

                                       bg-[var(--bg)] p-3"

                            >

                                <div

                                    class="flex items-center

                                           justify-between gap-3"

                                >

                                    <span

                                        class="font-semibold

                                               text-[var(--text-title)]"

                                    >

                                        {{ $sector['label'] }}

                                    </span>



                                    <span

                                        class="text-xs

                                               text-[var(--text-muted)]"

                                    >

                                        {{ number_format(

                                            $sector['start_pct'] * 100,

                                            2,

                                            ',',

                                            '.'

                                        ) }} %

                                    </span>

                                </div>



                                <div

                                    class="mt-2 h-1.5 overflow-hidden

                                           rounded-full bg-[var(--border)]"

                                >

                                    <div

                                        class="h-full rounded-full

                                               bg-[var(--accent)]"

                                        style="width: {{ max(

                                            0,

                                            min(

                                                100,

                                                (

                                                    $sector['end_pct']

                                                    - $sector['start_pct']

                                                ) * 100

                                            )

                                        ) }}%"

                                    ></div>

                                </div>



                                <p

                                    class="mt-2 text-xs

                                           text-[var(--text-muted)]"

                                >

                                    Inicio:

                                    {{ number_format(

                                        $sector['start_pct'] * 100,

                                        2,

                                        ',',

                                        '.'

                                    ) }} %

                                    <span class="mx-1">·</span>

                                    Fin:

                                    {{ number_format(

                                        $sector['end_pct'] * 100,

                                        2,

                                        ',',

                                        '.'

                                    ) }} %

                                </p>

                            </div>

                        @endforeach

                    </div>

                @else

                    <p class="text-sm text-[var(--text-muted)]">

                        Este IBT no contiene límites de sector válidos.

                    </p>

                @endif

            </div>
