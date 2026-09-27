@extends('layouts.app')

@section('title', 'Setup Items')

@section('page-title',   __('ui.set_item_database') )

@section('content')

<div class="space-y-6">

<br>

<div class="dark:bg-gray-800 bg-gray-100 rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-lg shadow-black/20">

    {{-- Header --}}
    <div class="px-6 py-4 border-b dark:border-gray-700 flex items-center justify-between">

        <h2 class="text-lg font-semibold text-gray-600 dark:text-gray-300">
            {{ __('ui.set_items') }}
        </h2>

        {{-- 🔥 BOTÓN SYNC --}}
        <form method="POST" action="{{ route('admin.setup-items.sync') }}">
            @csrf   
            <button class="dark:bg-yellow-700 dark:hover:bg-yellow-900 dark:text-white bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                {{ __('ui.sync') }}
            </button>
        </form>

    </div>

    <div class="px-6 py-4 border-b dark:border-gray-700 flex flex-wrap gap-3 items-center">

        {{-- 🔍 SEARCH --}}
        <form method="GET" class="flex gap-2">
    
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar key, label..."
                   class="px-3 py-1 rounded bg-white dark:bg-gray-700 
                          border border-gray-300 dark:border-gray-600 
                          text-sm text-gray-700 dark:text-gray-200">
    
            <button class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm">
                {{ __('ui.search') }}
            </button>
    
        </form>
    
        {{-- 🔁 FILTROS --}}
        <a href="{{ route('admin.setup-items.index') }}"
           class="px-3 py-1 rounded text-sm {{ request('filter') == null ? 'bg-indigo-500 text-white' : 'bg-gray-300 dark:bg-gray-700' }}">
           {{ __('ui.all') }}
        </a>
    
        <a href="{{ route('admin.setup-items.index', ['filter' => 'pending', 'search' => request('search')]) }}"
           class="px-3 py-1 rounded text-sm {{ request('filter') == 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-300 dark:bg-gray-700' }}">
           {{ __('ui.pending') }}
        </a>
    
        <a href="{{ route('admin.setup-items.index', ['filter' => 'incomplete', 'search' => request('search')]) }}"
           class="px-3 py-1 rounded text-sm {{ request('filter') == 'incomplete' ? 'bg-orange-500 text-white' : 'bg-gray-300 dark:bg-gray-700' }}">
           {{ __('ui.incomplete') }}
        </a>
    
        <a href="{{ route('admin.setup-items.index', ['filter' => 'ok', 'search' => request('search')]) }}"
           class="px-3 py-1 rounded text-sm {{ request('filter') == 'ok' ? 'bg-green-500 text-white' : 'bg-gray-300 dark:bg-gray-700' }}">
            OK
        </a>
    
    </div>
    
    <div class="overflow-x-auto">

        <table class="min-w-full text-sm">

            <thead class="bg-gray-200 dark:bg-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('ui.rawkey') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('ui.label') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('ui.zone') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('ui.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('ui.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300 dark:divide-gray-700">

                @forelse($items as $item)

                    <tr class="hover:bg-gray-300 dark:hover:bg-gray-700/50 transition">

                        {{-- RAW KEY --}}
                        <td class="px-4 py-4 text-xs font-mono text-gray-600 dark:text-gray-300">
                            {{ $item->raw_key }}
                        </td>

                        {{-- LABEL --}}
                        <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                            {{ $item->label ?? '—' }}
                        </td>

                        {{-- ZONA --}}
                        <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                            @if($item->zone)
                                <span class="bg-indigo-400 dark:bg-indigo-500/20 text-indigo-100 dark:text-indigo-300 px-2 py-1 rounded">
                                    {{ $item->zone }}
                                </span>
                            @else
                                —
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td class="px-4 py-4">

                            @if($item->status === 'pending')
                                <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs">
                                    {{ __('ui.pending') }}
                                </span>
                            @else
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">
                                    OK
                                </span>
                            @endif

                        </td>

                        {{-- ACTION --}}
                        <td class="px-4 py-4">
                            <a href="{{ route('admin.setup-items.edit', $item->id) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                Edit
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5"
                            class="px-6 py-8 text-center text-gray-400">
                            No hay setup items aún.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

{{-- PAGINACIÓN --}}
<div>
    {{ $items->links() }}
</div>

</div>

@endsection