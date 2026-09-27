<header class="h-14 z-30 bg-[var(--header)] text-[var(--text)] border-[var(--b-header)] border-b flex items-center justify-between px-6">

            {{-- LEFT SIDE --}}
            <div class="flex items-center space-x-6 ">

                   {{-- PAGE TITLE --}}
                <h1 class="text-xs text-[var(--text-topbar)] font-semibold uppercase">
                    @yield('page-title', 'Dashboard')
                </h1>


            </div>

            {{-- RIGHT SIDE --}}

        <div class="flex items-center space-x-6">
            @if(app()->environment('local'))

            <div class="fixed bottom-4 right-4 z-50 rounded-lg bg-zinc-900 text-white text-xs px-4 py-2">

                <div><strong>Workspace:</strong> {{ workspace()->type() }}</div>

                <div><strong>User:</strong> {{ workspace()->user()->name }}</div>

                <div><strong>Team:</strong>
                    {{ workspace()->team()?->name ?? '—' }}
                </div>

            </div>

            @endif
            <div class="relative">

                <ul class="flex gap-3">

                    <li>
                    <a href="{{ LaravelLocalization::getLocalizedURL('es') }}">
                    <img src="/flags/es.svg" class="w-3 h-3">
                    </a>
                    </li>

                    <li>
                    <a href="{{ LaravelLocalization::getLocalizedURL('en') }}">
                    <img src="/flags/gb.svg" class="w-3 h-3">
                    </a>
                    </li>

                    </ul>

                </div>
                {{-- NOTIFICATIONS --}}
                <button class="relative text-gray-400 transition">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032
                            2.032 0 0118 14.158V11a6.002
                            6.002 0 00-4-5.659V5a2 2 0
                            10-4 0v.341C7.67 6.165 6
                            8.388 6 11v3.159c0 .538-.214
                            1.055-.595 1.436L4 17h5m6
                            0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    <span class="absolute -top-1 -right-1 bg-red-600 text-xs
                        rounded-full px-1">
                        0
                    </span>
                </button>

                {{-- USER DROPDOWN --}}
                <div x-data="{ open: false }" class="relative">

                    <button
                        @click="open = !open"
                        class="flex items-center space-x-2 text-gray-300 hover:text-white transition">
                        @if (!empty(auth()->user()->iracing_helmet_path ))
                        <div class="h-8 w-8 flex items-center justify-center text-sm font-semibold">
                        <img src="{{ asset('storage/'.auth()->user()->iracing_helmet_path) }}">
                            @else
                        <div class="h-8 w-8 flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @endif
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open"
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 mt-2 w-56 border-[var(--border)] bg-[var(--bg)]
                                border border-gray-700 rounded-lg shadow-lg
                                py-2 z-50">

                        <a href="/profile"
                        class="flex px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)]">
                        <svg class="mr-3" fill="CurrentColor" width="20" height="20" viewBox="0 0 32 32" id="icon" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCurrentColorarrier" stroke-width="0"></g><g id="SVGRepo_tracerCurrentColorarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCurrentColorarrier"><defs><style>.cls-1{fill:none;}</style></defs><title>account</title><path d="M8,14H19v2H8Zm0,5H21v2H8Z"></path><path d="M28,4H4A2,2,0,0,0,2,6V26a2,2,0,0,0,2,2H28a2,2,0,0,0,2-2V6A2,2,0,0,0,28,4Zm0,2V8H4V6ZM4,26V10H28V26Z"></path><rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="20" height="20"></rect></g></svg>    {{ __('ui.account') }}
                        </a>

                        <a href="{{ route('team.dashboard') }}"
                        class="flex px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)]">
                        <svg class="mr-3" fill="CurrentColor" width="20" height="20" viewBox="0 0 32 32" id="icon" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCurrentColorarrier" stroke-width="0"></g><g id="SVGRepo_tracerCurrentColorarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCurrentColorarrier"> <defs> <style> .cls-1 { fill: none; } </style> </defs> <path d="M11,21H9V19a3.0033,3.0033,0,0,1,3-3h6v2H12a1.0011,1.0011,0,0,0-1,1Z" transform="translate(0 0)"></path> <path d="M15,15a4,4,0,1,1,4-4A4.0045,4.0045,0,0,1,15,15Zm0-6a2,2,0,1,0,2,2A2.0021,2.0021,0,0,0,15,9Z" transform="translate(0 0)"></path> <path d="M24,22a4,4,0,1,1,4-4A4.0045,4.0045,0,0,1,24,22Zm0-6a2,2,0,1,0,2,2A2.0021,2.0021,0,0,0,24,16Z" transform="translate(0 0)"></path> <path d="M30,28H28V26a1.0011,1.0011,0,0,0-1-1H21a1.0011,1.0011,0,0,0-1,1v2H18V26a3.0033,3.0033,0,0,1,3-3h6a3.0033,3.0033,0,0,1,3,3Z" transform="translate(0 0)"></path> <path d="M14,27.7334l-5.2344-2.791A8.9858,8.9858,0,0,1,4,17V4H24v6h2V4a2.0023,2.0023,0,0,0-2-2H4A2.0023,2.0023,0,0,0,2,4V17a10.9814,10.9814,0,0,0,5.8242,9.707L14,30Z" transform="translate(0 0)"></path> <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="20" height="20"></rect> </g></svg>  {{ __('ui.myteam') }}
                        </a>

                        <a href="#"
                        class="flex px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)]">
                        <svg class="mr-3" fill="CurrentColor" width="20" height="20" viewBox="0 0 32 32" id="icon" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCurrentColorarrier" stroke-width="0"></g><g id="SVGRepo_tracerCurrentColorarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCurrentColorarrier"><defs><style>.cls-1{fill:none;}</style></defs><title>chart--line</title><path d="M10.06,17.88A4.25,4.25,0,0,0,11,18a4,4,0,0,0,2.23-.68l3.22,2.87a3.88,3.88,0,0,0-.2,3.17A4,4,0,1,0,22.62,19l2.54-5.09a3.78,3.78,0,0,0,2.91-.53A4,4,0,1,0,23.38,13l-2.54,5.09A3.86,3.86,0,0,0,20,18a4,4,0,0,0-2.23.68l-3.22-2.87a3.88,3.88,0,0,0,.2-3.17A4,4,0,1,0,8.3,16.93L4,25V2H2V28a2,2,0,0,0,2,2H30V28H4.67ZM26,8a2,2,0,1,1-2,2A2,2,0,0,1,26,8ZM22,22a2,2,0,1,1-2-2A2,2,0,0,1,22,22ZM11,12a2,2,0,1,1-2,2A2,2,0,0,1,11,12Z"></path><rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="20" height="20"></rect></g></svg>    {{ __('ui.telem') }}
                        </a>


                        <br>
                        @if(auth()->user()->driver_role === 'team_owner')
                        @include("components.layout.admin.team")
                        @endif
                        <br>
                        @if(auth()->user()?->role === 'admin')
                        @include("components.layout.admin.admin")
                        @endif

                        <br>

                        @include('components.layout.theme-selector')
                        {{-- <a class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">
                            <button onclick="toggleTheme()">
                            Theme 🌙
                        </button>
                        </a> --}}

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="w-full text-left px-4 py-2 text-sm
                                    text-[var(--btn-logout)] hover:text-[var(--text-logout-h)] hover:bg-[var(--hover)]">
                                {{ __('ui.logout') }}
                            </button>
                        </form>

                    </div>

                </div>

        </div>

    </header>
