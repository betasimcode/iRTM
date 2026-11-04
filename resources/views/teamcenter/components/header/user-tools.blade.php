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
                    {{ __('ui.personal') }}
                </div>

                <div class="space-y-1">

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        {{ __('ui.account') }}
                    </a>

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        {{ __('ui.comms') }}
                    </a>

                    <a
                        href="#"
                        class="block rounded-md px-2 py-1.5
                               text-sm
                               hover:bg-black/5
                               dark:hover:bg-white/5"
                    >
                        {{ __('ui.telem') }}
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
                        {{ __('ui.team') }}
                    </div>

                    <div class="space-y-1">

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            {{ __('ui.teamcenter_championships') }}
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            {{ __('ui.calendar') }}
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            {{ __('ui.drivers') }}
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            {{ __('ui.liveries') }}
                        </a>

                        <a
                            href="#"
                            class="block rounded-md px-2 py-1.5
                                   text-sm
                                   hover:bg-black/5
                                   dark:hover:bg-white/5"
                        >
                            {{ __('ui.sponsors') }}
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
                            {{ __('ui.admin_site') }}
                        </div>

                        <div class="space-y-1">

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                {{ __('ui.site_configuration') }}
                            </a>

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                {{ __('ui.editor') }}
                            </a>

                            <a
                                href="#"
                                class="block rounded-md px-2 py-1.5
                                       text-sm
                                       hover:bg-black/5
                                       dark:hover:bg-white/5"
                            >
                                {{ __('ui.layout') }}
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
                        {{ __('ui.logout') }}
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
