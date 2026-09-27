
            <p class="sidebar-label text-xs text-[var(--text-title)] uppercase">Admin site</p>
            <div class="border-t border-[var(--b-header)] my-2"></div>

                <a href="{{ route('teams.index') }}"
               class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('teams.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
                {{ __('ui.teams') }}
                </a>

                <a href="{{ route('admin.iracing-series.index') }}" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('admin.iracing-series.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
                {{ __('ui.irseries') }}
                </a>

                <a href="{{ route('admin.season-imports.index') }}" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('admin.season-imports.index') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
                Competitions
                </a>

                 <a href="{{ route('cars.index') }}"
               class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('cars.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">

                {{ __('ui.cars') }}
            </a>

            <a href="{{ route('circuits.index') }}"
               class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('circuits.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
                {{ __('ui.tracks') }}
            </a>

            <a href="{{ route('admin.setup-items.index') }}"
               class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('admin.setup-items.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
                {{ __('ui.setups') }}
            </a>

