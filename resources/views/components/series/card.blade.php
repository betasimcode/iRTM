<div class="relative z-10">
<div class="relative
    bg-[var(--card)]
    border border-[var(--border)]
    rounded-xl p-5 flex flex-col justify-between
    transition duration-200 hover:shadow-lg shadow-md hover:-translate-y-1">

    <a href="{{ route('series.show', $competition) }}"
       class="absolute inset-0 z-0"
       aria-label="Ver serie"></a>

    {{-- MENU --}}
    <div class="absolute top-3 right-3 z-20">
        <button onclick="toggleMenu({{ $competition->id }})"
                class="text-[var(--text)] hover:text-[var(--text-h)] text-lg px-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </button>

        <div id="menu-{{ $competition->id }}"
            class="hidden absolute right-0 mt-2 w-40
                   bg-white dark:bg-gray-900
                   border border-gray-200 dark:border-gray-700
                   rounded-lg shadow-lg">

            <form method="POST" action="{{ route('series.update', $competition) }}">
                @csrf
                @method('PUT')

                <input type="hidden"
                    name="status"
                    value="{{ $competition->status === 'active' ? 'draft' : 'active' }}">

                <button class="w-full text-left px-4 py-2 text-sm
                               text-gray-700 dark:text-gray-300
                               hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ $competition->status === 'active' ? 'Desactivar' : 'Activar' }}
                </button>
            </form>

            <a href="{{ route('series.edit', $competition) }}"
               class="block px-4 py-2 text-sm
                      text-gray-700 dark:text-gray-300
                      hover:bg-gray-100 dark:hover:bg-gray-700">
                Editar
            </a>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="flex items-center gap-4 mb-4">

        @if($competition->iracingSeries?->logo_path)
            <img
                src="{{ asset('storage/'.$competition->iracingSeries->logo_path) }}"
                class="w-40 h-auto object-contain"
                title="{{ $competition->iracingSeries->name }}">
        @else
            <div class="h-12 w-12
                bg-[var(--bg)] rounded flex items-center justify-center
                text-[var(--text)] text-xs">
                N/A
            </div>
        @endif

        <div>
            <h3 class="text-[var(--text)] font-semibold text-lg">
                {{ $competition->name }}
            </h3>

            <h3 class="text-[var(--text-soft)] font-semibold text-sm">
                {{ $competition->season_year }} - {{ __('ui.season')  }} {{ $competition->season_number }}
            </h3>
        </div>
    </div>

    {{-- STATUS --}}
@php
    $config = $competition->getDisplayConfig();
@endphp

<div class="border-t border-[var(--border)] my-2"></div>

<div class="flex flex-wrap gap-2 mt-3">

    {{-- Clase --}}
    @if($competition->iracingSeries->iracing_class == 'A')
    <span class="flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium border border-blue-600 bg-[var(--{{ $competition->iracingSeries->iracing_class }})]">{{ $competition->iracingSeries->class_label }}   </span>
    @elseif($competition->iracingSeries->iracing_class == 'B')
    <span class="flex items-center gap-1.5 px-3 py-1 text-[var(--BT)] rounded-md text-xs font-medium border border-green-600 bg-[var(--{{ $competition->iracingSeries->iracing_class }})]">{{ $competition->iracingSeries->class_label }}   </span>
    @elseif($competition->iracingSeries->iracing_class == 'C')
    <span class="flex items-center gap-1.5 px-3 py-1 text-[var(--CT)] rounded-md text-xs font-medium border border-yellow-600 bg-[var(--{{ $competition->iracingSeries->iracing_class }})]">{{ $competition->iracingSeries->class_label }}   </span>
    @elseif($competition->iracingSeries->iracing_class == 'D')
    <span class="flex items-center gap-1.5 px-3 py-1 text-[var(--DT)] rounded-md text-xs font-medium border border-black bg-[var(--{{ $competition->iracingSeries->iracing_class }})]">{{ $competition->iracingSeries->class_label }}   </span>
    @elseif($competition->iracingSeries->iracing_class == 'R')
    <span class="flex items-center gap-1.5 px-3 py-1 text-[var(--RT)] rounded-md text-xs font-medium border border-black bg-[var(--{{ $competition->iracingSeries->iracing_class }})]">{{ $competition->iracingSeries->class_label }}   </span>
    {{ $competition->iracingSeries->class_label }}
    </span>
    @endif

    {{-- Salida --}}
    @if($config['start'])
    <span class="flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium
        bg-blue-100 text-blue-700 border border-blue-300
        dark:bg-blue-500/10 dark:border-blue-500 dark:text-blue-400">
        {{ $config['start'] === 'rolling' ? __('ui.rolling') : __('ui.standing') }}
    </span>
    @endif

    {{-- Tiempo --}}
    @if($config['type'] === 'time')
    <span class="flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium
        bg-purple-100 text-purple-700 border border-purple-300
        dark:bg-purple-500/10 dark:border-purple-500 dark:text-purple-400">
        {{ $config['length'] }} {{ __('ui.min') }}
    </span>
    @endif

    {{-- Vueltas --}}
    @if($config['type'] === 'laps')
    <span class="flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium
        bg-purple-100 text-purple-700 border border-purple-300
        dark:bg-purple-600/20 dark:text-purple-400">
        {{ $config['length'] }} {{ __('ui.laps') }}
    </span>
    @endif

    {{-- Distancia --}}
    @if($competition->estimatedDistance())
    <span class="px-3 py-1 rounded-md text-xs font-medium
        bg-green-100 text-green-700 border border-green-300
        dark:bg-green-600/20 dark:text-green-400">
        {{ $competition->estimatedDistance() }} km
    </span>
    @endif

    {{-- Estado --}}
    <span class="px-3 py-1 rounded-md text-xs font-medium border
        {{ $competition->status === 'active'
            ? 'bg-green-100 border-green-300 text-green-700 dark:bg-green-600/20 dark:border-green-500 dark:text-green-400'
            : 'bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-600/20 dark:border-gray-500 dark:text-gray-400' }}">
        {{ ucfirst($competition->status) }}
    </span>

</div>

</div>
</div>
