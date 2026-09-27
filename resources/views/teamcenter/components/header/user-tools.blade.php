<div class="flex items-center space-x-6">

    {{-- =========================================================
         LANGUAGE
         ========================================================= --}}

    @include(
        'teamcenter.components.header.language-selector'
    )


    {{-- =========================================================
         NOTIFICATIONS
         ========================================================= --}}

    @include(
        'teamcenter.components.header.notifications'
    )


    {{-- =========================================================
         USER MENU
         ========================================================= --}}

    <div
        x-data="{ open: false }"
        class="relative"
    >

        {{-- USER BUTTON --}}

        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-2"
        >

            <span class="text-sm font-medium">
                {{ auth()->user()->name }}
            </span>

            <i
                data-lucide="chevron-down"
                class="h-4 w-4"
            ></i>

        </button>


        {{-- =====================================================
             DROPDOWN
             ===================================================== --}}

        <div
            x-show="open"
            @click.outside="open = false"
            x-transition
            class="absolute right-0 top-full z-50 mt-2 w-64
                   overflow-hidden
                   rounded-lg
                   border
                   border-[var(--border)]
                   bg-[var(--card)]
                   shadow-xl"
        >


            {{-- =================================================
                 PERSONAL
                 ================================================= --}}

            <div class="px-4 py-3">

                <div
                    class="mb-2 text-xs font-semibold uppercase
                           tracking-wider
                           text-[var(--text-card-title)]"
                >
                    Personal
                </div>

                <div class="space-y-1">

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        Mi cuenta
                    </a>

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        Comms
                    </a>

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        Telemetría
                    </a>

                </div>

            </div>


            {{-- =================================================
                 TEAM
                 ================================================= --}}

            <div
                class="border-t border-[var(--border)]"
            >

                <div class="px-4 py-3">

                    <div
                        class="mb-2 text-xs font-semibold uppercase
                               tracking-wider
                               text-[var(--text-card-title)]"
                    >
                        Team
                    </div>

                    <div class="space-y-1">

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            Championships
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            Calendar
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            Drivers
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            Liveries
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            Sponsors
                        </a>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 ADMIN SITE
                 ================================================= --}}

            @if(auth()->user()->driver_role === 'team_owner')

                <div
                    class="border-t border-[var(--border)]"
                >

                    <div class="px-4 py-3">

                        <div
                            class="mb-2 text-xs font-semibold uppercase
                                   tracking-wider
                                   text-[var(--text-card-title)]"
                        >
                            Admin Site
                        </div>

                        <div class="space-y-1">

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                Configuración del sitio
                            </a>

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                Editor
                            </a>

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                Layout
                            </a>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =================================================
                 PREFERENCES
                 ================================================= --}}

            <div
                class="border-t border-[var(--border)]"
            >

                <div class="px-4 py-3">

                    @include(
                        'components.layout.theme-selector',
                        [
                            'teamThemes' => [
                                [
                                    'value' => 'theme-miura',
                                    'label' => 'Miura',
                                ],
                            ],
                        ]
                    )

                </div>

            </div>


            {{-- =================================================
                 LOGOUT
                 ================================================= --}}

            <div
                class="border-t border-[var(--border)]"
            >

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="w-full px-4 py-3
                               text-left
                               text-sm
                               transition-colors
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        Desconectar
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
