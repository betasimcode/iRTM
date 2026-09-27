{{-- SIDEBAR --}}
    <aside class="w-72 bg-[var(--bg)] text-[var(--text) ] flex flex-col">

        {{-- Logo height="64px" width="287px --}}
        <div class="h-14 bg-[var(--header)] flex items-center px-6 border-b border-[var(--b-header)]">

            <div
            x-data="{
                theme: '{{ auth()->user()->theme ?? 'theme-dark' }}'
            }"
            class="flex items-center justify-between w-full">

            {{-- LOGOS --}}
            <div class="flex items-center">

                <img
                    x-show="$store.theme.current === 'theme-dark'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-iracing'"
                    src="{{ asset('images/themes/irteammanager-light.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-monaco'"
                    src="{{ asset('images/themes/irteammanager-light.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-monza'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-laguna'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-hock'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-spa'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-crtg'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

                <img
                    x-show="$store.theme.current === 'theme-jerez'"
                    src="{{ asset('images/themes/irteammanager-dark.png') }}"
                    class="h-10 object-contain">

            </div>


        </div>

        </div>

        @include("components.layout.navigation")

        <div class="ml-5 ">

        </div>
        {{-- User + Logout --}}


        <div class="p-4 border-r text-[var(--text)] border-t border-[var(--border)]">
            <div class="text-sm mb-3 text-[var(--text-soft)]">
                {{ Auth::user()->name }}
            </div>




            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    class="w-full bg-[var(--btn-logout)] hover:bg-[var(--btn-logout-h)] text-[var(--btn-text)] hover:text-[var(--btn-text-logout-h)] py-2 rounded-lg text-sm transition">
                    {{ __('ui.logout') }}
                </button>
            </form>
        </div>

    </aside>
