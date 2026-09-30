<p class="sidebar-label text-xs text-[var(--text-title)] uppercase">Team management</p>
            <div class="border-t border-[var(--b-header)] my-2"></div>
<a href="{{ route('teamcenter.dashboard') }}" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)] {{ request()->routeIs('team.*') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
{{ __('ui.director') }}
</a>

<a href="#" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--hover)] hover:text-[var(--text-h)]  {{ request()->routeIs('#') ? 'bg-[var(--hover)] text-[var(--text-h)]' : '' }}">
{{ __('ui.invite') }}</a>
