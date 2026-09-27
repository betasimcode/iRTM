@props([
    'competition',
    'entry' => null,
    'href' => null,
    'type' => 'private',
])

@php
    $seriesInfo = $competition->iracingSeries;
@endphp

@if($href)
    <a
        href="{{ $href }}"
        class="block"
        aria-label="{{ $competition->name }}"
    >
@endif



    <div class="relative flex flex-col justify-between
               card-min
               rounded-xl
               border border-[var(--border)]
               bg-[var(--card)]
               p-5
               w-1/5
               h-56
               shadow-md
               transition duration-200
               hover:bg-[var(--bg)]
               hover:text-[var(--text-h)]
               hover:shadow-lg"
    >

        <div class="flex items-center justify-between gap-6 z-10">

    {{-- COMPETITION IDENTITY --}}
    <div class="shrink-0">

        @if($seriesInfo?->logo_path)

            <img
                src="{{ asset(
                    'storage/' . $seriesInfo->logo_path
                ) }}"
                alt="{{ $competition->name }}"
                class="h-auto w-32
                       object-contain"
            >

        @else

            <div
                class="h-16 w-16
                       rounded-lg
                       bg-[var(--bg)]
                       flex items-center justify-center"
            >
                <span class="text-xs
                             text-[var(--text-muted)]">
                    SERIES
                </span>
            </div>

        @endif

    </div>


    {{-- SERIES INFORMATION --}}
    <div class="flex-1 min-w-0 text-center">

        <h3 class="text-lg font-semibold text-[var(--text)]">
            {{ $competition->name }}
        </h3>

        @if($seriesInfo?->short_name)

            <p
                class="mt-1 text-sm
                       uppercase
                       text-[var(--text-soft)]"
            >
                {{ $seriesInfo->short_name }}
            </p>

        @endif

    </div>


    {{-- PARTICIPATION IDENTITY --}}

</div>


        {{-- INFORMACIÓN DE LA SERIE --}}
        <div class="my-4 border-t border-[var(--border)] z-10"></div>

        <div class="flex flex-wrap z-10">

            {{-- CLASE --}}
            @if($seriesInfo?->iracing_class == 'A' )
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--bg)]
                        px-3 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $seriesInfo?->iracing_class }})]"
                >
                    CLASS
                </span>

                <span
                    class="w-10
                        text-center
                        rounded-r-md
                        border border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--{{ $seriesInfo?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-white"
                >
                    {{ $seriesInfo?->iracing_class }}
                </span>

    @elseif($seriesInfo?->iracing_class == 'ROOKIE' )
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--bg)]
                        px-3 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $seriesInfo?->iracing_class }})]"
                >
                    CLASS
                </span>

                <span
                    class="
                            w-10
                            text-center
                        rounded-r-md
                        border border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--{{ $seriesInfo?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-white"
                >
                    R
                </span>

            @else
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--bg)]
                        px-4 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $seriesInfo?->iracing_class }})]"
                >
                    CLASS
                </span>
                <span
                    class=" text-center
                        w-10
                        rounded-r-md
                        border border-[var(--{{ $seriesInfo?->iracing_class }})]
                        bg-[var(--{{ $seriesInfo?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-black"
                >
                    {{ $seriesInfo?->iracing_class }}
                </span>

            @endif


            {{-- CATEGORÍA --}}
            {{-- @if($seriesInfo?->category)

                <span
                    class="text-center
                           mx-2
                           rounded-md
                           border border-[var(--border)]
                           bg-[var(--bg)]
                           px-3 py-1
                           text-xs font-medium
                           text-[var(--text)]"
                >
                    {{ $seriesInfo->category }}
                </span>

            @endif --}}


            {{-- DISCIPLINA --}}
            @if($seriesInfo?->discipline)

                <span
                    class="mr-2
                           rounded-md
                           border border-[var(--border)]
                           bg-[var(--bg)]
                           px-3 py-1
                           text-xs font-medium
                           text-[var(--text)]"
                >
                    {{ $seriesInfo->discipline }}
                </span>

            @endif


            {{-- TIPO DE CARRERA --}}
            {{-- @if($seriesInfo?->race_type)

                <span
                    class="mr-2
                           rounded-md
                           border border-[var(--border)]
                           bg-[var(--bg)]
                           px-3 py-1
                           text-xs font-medium
                           text-[var(--text)]"
                >
                    {{ $seriesInfo->race_type }}
                </span>

            @endif --}}


            {{-- LONGITUD --}}
            @if($seriesInfo?->race_length)

                <span
                    class="mr-1
                           rounded-md
                           border border-[var(--border)]
                           bg-[var(--bg)]
                           px-3 py-1
                           text-xs font-medium
                           text-[var(--text)]"
                >
                    @if($seriesInfo->race_type === 'time')
                        {{ $seriesInfo->race_length }} Mins
                    @else
                        {{ $seriesInfo->race_length }} Laps
                    @endif
                </span>

            @endif

            @if($competition->status == "draft")

                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--info)]
                        bg-[var(--info)]
                        px-2 py-0.5
                        text-base font-bold
                        text-white"
                >
                   {{$competition->season_year}}
                </span>

                <span
                    class="w-10
                        text-center
                        rounded-r-md
                        border border-[var(--info)]
                        bg-[var(--base)]
                        px-1 py-0.5
                        mr-2
                        text-base font-bold
                        text-[var(--text)]"
                >
                    {{$competition->season_number}}
                </span>

            @elseif($competition->status == "")
            @endif

        </div>

    </div>

@if($href)
    </a>
@endif
