<header class="w-full border-b border-[var(--border)] bg-[var(--header)]">

    @if($navigationPosition === 'bottom')

        {{-- =========================================================
             HEADER + NAVIGATION BOTTOM
             ========================================================= --}}

        <div class="relative flex min-h-20 w-full items-center px-6">

            {{-- LOGO --}}

            @if($logoPosition === 'center')

                <div
                    class="absolute inset-0
                        flex items-center justify-center
                        pointer-events-none"
                >

                    @include(
                        'teamcenter.components.header.logo',
                        [
                            'position' => 'center',
                        ]
                    )

                </div>

            @else

                <div class="shrink-0">

                    @include(
                        'teamcenter.components.header.logo',
                        [
                            'position' => 'left',
                        ]
                    )

                </div>

            @endif


            {{-- USER TOOLS --}}

            <div class="ml-auto shrink-0">

                @include(
                    'teamcenter.components.header.user-tools'
                )

            </div>

        </div>


        {{-- NAVIGATION --}}

        <div class="w-full px-6 py-3">

            @include(
                'teamcenter.components.header.navigation',
                [
                    'position' => $navigationPosition,
                    'logoPosition' => null,
                ]
            )

        </div>


    @else

        {{-- =========================================================
             HEADER + NAVIGATION FULL
             ========================================================= --}}

        <div
            class="flex min-h-20 w-full items-center px-6"
        >

            {{-- LOGO LEFT --}}

            @if($logoPosition === 'left')

                <div class="shrink-0">

                    @include(
                        'teamcenter.components.header.logo',
                        [
                            'position' => 'left',
                        ]
                    )

                </div>

            @endif


            {{-- NAVIGATION --}}

            <div class="flex-1">

                @include(
                    'teamcenter.components.header.navigation',
                    [
                        'position' => $navigationPosition,
                        'logoPosition' => $logoPosition,
                    ]
                )

            </div>


            {{-- USER TOOLS --}}

            <div class="shrink-0">

                @include(
                    'teamcenter.components.header.user-tools'
                )

            </div>

        </div>

    @endif

</header>
