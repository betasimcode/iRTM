<header class="h-14 shrink-0 border-b border-[var(--border)] bg-[var(--bg)] text-[var(--text)]">

    <div class="h-full flex items-center justify-end px-6 gap-5">

        {{-- LANGUAGE --}}
        <div class="flex items-center gap-2">

            <a href="{{ LaravelLocalization::getLocalizedURL('es') }}">
                <img
                    src="/flags/es.svg"
                    class="w-3 h-3"
                    alt="ES"
                >
            </a>

            <a href="{{ LaravelLocalization::getLocalizedURL('en') }}">
                <img
                    src="/flags/gb.svg"
                    class="w-3 h-3"
                    alt="EN"
                >
            </a>

        </div>

        {{-- ALERTS --}}
        <button
            type="button"
            class="relative text-[var(--text-soft)] hover:text-[var(--text)] transition"
        >

            <span class="text-sm">
                🔔
            </span>

            <span class="absolute -top-1 -right-2 min-w-3 h-3
                         flex items-center justify-center
                         rounded-full bg-red-600 text-white text-[8px]">
                0
            </span>

        </button>

        {{-- USER --}}
        <div class="flex items-center gap-2">

            @if(auth()->user()?->iracing_helmet_path)

                <img
                    src="{{ asset('storage/' . auth()->user()->iracing_helmet_path) }}"
                    class="h-8 w-8 object-contain"
                    alt="{{ auth()->user()->name }}"
                >

            @else

                <div class="h-8 w-8 rounded-full flex items-center justify-center
                            bg-[var(--hover)] text-xs font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

            @endif

            <span class="text-xs">
                {{ auth()->user()->name }}
            </span>

        </div>

    </div>

</header>
