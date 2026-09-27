<aside class="w-60 shrink-0 flex flex-col border-r border-[var(--border)] bg-[var(--bg)] text-[var(--text)]">

    {{-- NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto p-4 space-y-1">

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Dashboard
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Competitions
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Drivers
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Cars
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Sessions
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Stints
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Strategy
        </a>

        <a href="#"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Reports
        </a>

    </nav>

    {{-- EXIT TEAM CENTER --}}
    <div class="p-4 border-t border-[var(--border)]">

        <a href="{{ route('competitions.index') }}"
           class="block px-3 py-2 rounded-lg hover:bg-[var(--hover)]">
            Driver Workspace
        </a>

    </div>

</aside>
