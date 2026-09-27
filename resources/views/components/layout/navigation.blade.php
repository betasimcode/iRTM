{{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 border-r border-[var(--border)] bg-[var(--bg)] text-[var(--text)] px-3 space-y-1">

            <a href="{{ route('dashboard') }}"
               class="text-[var(--text)] sidebar-link {{ request()->routeIs('dashboard') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
               {{ __('ui.dashboard') }}

            </a>

            <a href="{{ route('competitions.index') }}"
               class="text-[var(--text)] sidebar-link {{ request()->routeIs('series.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">

                <span>{{ __('ui.champs') }}</span>
            </a>

            <a href="{{ route('calendar') }}"
               class="text-[var(--text)] sidebar-link">

                 {{ __('ui.calendar') }}
            </a>

            <a href="{{ route('fuel.index') }}"
               class="text-[var(--text)] sidebar-link">

                {{ __('ui.calculator') }}
            </a>

            <a href="{{ route('stints.index') }}"
               class="text-[var(--text)] sidebar-link {{ request()->routeIs('stints.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">

                {{ __('ui.stints') }}
            </a>

            <a href="{{ route('sessions.index') }}"
               class="text-[var(--text)] sidebar-link {{ request()->routeIs('sessions.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">

                {{ __('ui.sessions') }}
            </a>

             <br>

            @if(auth()->user()?->team_id)

            <a href="">
            <div class="m-auto text-center px-2 w-40 bg-[var(--card)] rounded-xl border border-[var(--border)] hover:border-[var(--text-h)] hover:bg-[var(--card-hover)] my-2">

            <img src="{{ asset('storage/' . themedLogo($team)) }}" class="w-40 h-auto p-2">

            </div>
            </a>
            @endif







        </nav>
