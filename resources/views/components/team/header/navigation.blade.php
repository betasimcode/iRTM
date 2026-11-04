<header class="h-16 shrink-0 border-b border-[var(--border)] bg-[var(--bg)] text-[var(--text)]">

    <div class="h-full flex items-center px-6 gap-8">

        {{-- LOGO --}}
        <div class="h-12 w-40 shrink-0 flex items-center">
            <x-team.logo
                :team="$team"
                class="max-h-12 max-w-40"
            />
        </div>

        {{-- TEAM NAVIGATION --}}
        <nav class="flex items-center gap-6 text-xs uppercase tracking-wide">

            <a href="#"
               class="px-3 py-2 hover:border-b-2 hover:border-[var(--border)] transition">
                {{ __('ui.dashboard') }}
            </a>

            <a href="#"
               class="px-3 py-2 hover:border-b-2 hover:border-[var(--border)] transition">
                {{ __('ui.competitions') }}
            </a>

            <a href="#"
               class="px-3 py-2 hover:border-b-2 hover:border-[var(--border)] transition">
                {{ __('ui.drivers') }}
            </a>

            <a href="#"
               class="px-3 py-2 hover:border-b-2 hover:border-[var(--border)] transition">
                {{ __('ui.cars') }}
            </a>

            <a href="#"
               class="px-3 py-2 hover:border-b-2 hover:border-[var(--border)] transition">
                {{ __('ui.reports') }}
            </a>

        </nav>

        {{-- RIGHT SIDE --}}
        <div class="ml-auto flex items-center gap-4">

            <span class="text-xs opacity-60">
                {{ $team?->name ?? __('ui.team') }}
            </span>

        </div>

    </div>

</header>
