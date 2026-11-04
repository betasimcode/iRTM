@extends('layouts.app')

@section('content')

@section('title', __('ui.set_items'))

@section('page-title', __('ui.admin_system'))

<div class="max-w-3xl mx-auto py-10">

    <h1 class="text-2xl font-bold text-gray-600 dark:text-white mb-6">
        {{ __('ui.setup_item_normalize') }}
    </h1>

    <form action="{{ route('admin.setup-items.update', $item->id) }}"
          method="POST"
          class="bg-gray-200 dark:bg-gray-800 border border-gray-700 rounded-2xl shadow-lg p-8 space-y-6">

        @csrf
        @method('PUT')

        {{-- RAW KEY --}}
        <div>
            <label class="block text-sm text.gray-500 dark:text-gray-400 mb-2">
                {{ __('ui.rawkey') }}
            </label>

            <div class="w-full bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-700 px-4 py-2 rounded font-mono text-lg">
                {{ $item->raw_key }}
            </div>
        </div>

        {{-- LABEL --}}
        <div>
            <label class="block text-sm text.gray-500 dark:text-gray-400 mb-2">
                {{ __('ui.label') }}
            </label>

            <select name="label"
                    class="w-full bg-gray-100 dark:bg-gray-900 border border-gray-700
                           rounded-lg px-4 py-2 text-gray-600 dark:text-white">

                <option value="">{{ __('ui.select_option') }}</option>

                @foreach($labels as $label)
                    <option value="{{ $label }}" @selected($item->label == $label)>
                        {{ $label }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- ZONA --}}
        <div>
            <label class="block text-sm text.gray-500 dark:text-gray-400 mb-2">
                {{ __('ui.zone') }}
            </label>

            <select name="zone"
            class="w-full bg-gray-100 dark:bg-gray-900 border border-gray-700
            rounded-lg px-4 py-2 text-gray-600 dark:text-white">

                @foreach($zones as $z)
                    <option value="{{ $z }}" @selected($item->zone == $z)>
                        {{ $z }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- STATUS (solo visual) --}}
        <div>
            <label class="block text-sm text.gray-500 dark:text-gray-400 mb-2">
                {{ __('ui.status') }}
            </label>

            <div class="text-sm">
                @if($item->status === 'pending')
                    <span class="bg-yellow-500 text-yellow-900 px-2 py-1 rounded">
                        {{ __('ui.pending') }}
                    </span>
                @else
                    <span class="bg-green-600 text-white px-2 py-1 rounded">
                        {{ __('ui.ok') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex justify-between items-center pt-4">

            {{-- GUARDAR --}}
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded text-white">
                {{ __('ui.save') }}
            </button>

            {{-- CANCELAR --}}
            <a href="{{ route('admin.setup-items.index') }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg">
                {{ __('ui.cancel') }}
            </a>

        </div>

    </form>

</div>

@endsection