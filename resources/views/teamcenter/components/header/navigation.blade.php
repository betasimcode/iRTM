<nav
    class="flex items-center justify-center gap-8"
>

    {{-- TEAM --}}

    <a
        href="{{ route('teamcenter.dashboard') }}"
        class="text-sm font-medium transition-opacity hover:opacity-70"
    >
        {{ $team->name ?? 'Team' }}
    </a>


    {{-- CHAMPIONSHIPS --}}

    <a
        href="{{ route('teamcenter.championships.index') }}"
        class="text-sm font-medium transition-opacity hover:opacity-70"
    >
        Championships
    </a>


    {{-- COMMS --}}

    <a
        href="#"
        class="text-sm font-medium transition-opacity hover:opacity-70"
    >
        Comms
    </a>


    {{-- RESOURCES --}}

    <a
        href="#"
        class="text-sm font-medium transition-opacity hover:opacity-70"
    >
        Resources
    </a>


    {{-- SPONSORS --}}

    <a
        href="#"
        class="text-sm font-medium transition-opacity hover:opacity-70"
    >
        Sponsors
    </a>


    {{-- LOGO CENTRAL --}}

    @if(($logoPosition ?? 'left') === 'center')

        <div class="mx-4 shrink-0">

            @include(
                'teamcenter.components.header.logo',
                [
                    'position' => 'center',
                ]
            )

        </div>

    @endif

</nav>
