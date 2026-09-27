{{-- resources/views/components/ui/button.blade.php --}}

@php
$base = "inline-flex items-center justify-center font-medium transition focus:outline-none";

$variants = [
    'main' => "rounded-lg px-4 py-2 border border-gray-300 dark:border-gray-800 bg-gray-100 dark:hover:bg-gray-700 hover:bg-gray-200 dark:text-gray-400 text-gray-500 dark:bg-gray-900 rounded text-sm",
    'exit' => "rounded-lg px-4 py-2 border border-gray-300 dark:border-gray-800 bg-gray-100 dark:hover:bg-red-900 hover:bg-red-500 dark:text-gray-400 hover:text-gray-100 text-gray-500 dark:bg-gray-900 rounded text-sm",
    'add' => "rounded-lg px-4 py-2 border border-gray-300 dark:border-gray-800 bg-gray-100 dark:hover:bg-green-700 hover:bg-green-500 dark:text-gray-400 hover:text-white text-gray-500 dark:bg-gray-700 rounded text-sm",

    'tec' => "rounded-lg px-4 py-2 border border-blue-300 dark:border-blue-800 bg-blue-100 hover:bg-blue-700 dark:hover:bg-blue-700 hover:bg-blue-100 dark:text-gray-400 text-gray-500 hover:text-gray-100 dark:hover:text-gray-100 dark:bg-blue-900/30 rounded text-sm",

    'info' => "rounded-lg px-4 py-2 border border-gray-300 dark:border-gray-800 bg-gray-100 dark:hover:bg-blue-700 hover:bg-blue-100 dark:text-gray-400 text-gray-500 dark:bg-gray-900 rounded text-sm",
    'gestion' => "rounded-lg px-4 py-2 border border-blue-300 dark:border-blue-800 bg-blue-100 dark:hover:bg-blue-700 hover:bg-blue-200 dark:text-gray-400 text-gray-500 dark:bg-gray-900 rounded text-sm",

    'test' => "rounded-lg px-4 py-2 border border-gray-300 dark:border-gray-800 bg-gray-100 dark:hover:bg-orange-700 hover:bg-orange-100 dark:text-gray-400 text-gray-500 dark:bg-gray-900 rounded text-sm",

    'secondary' => "rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white",
    'ghost' => "rounded-lg bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300",
    'danger' => "rounded-lg bg-red-500 hover:bg-red-600 text-white",
    'optionbox' => "rounded-lg text-gray-400 hover:text-gray-500 border border-gray-400",
    'optionbox-active' => "rounded-lg text-gray-100 border border-blue-500 bg-blue-400",
    'option' => "rounded-lg text-gray-400 hover:text-gray-500",
    'menu' => "rounded text-[var(--text)] hover:text-[var(--text-h)] bg-[var(--base)] hover:bg-[var(--hover)] border border-[var(--hover)] mx-4 px-4 w-48 h-9",
    'pit_in' => "rounded text-gray-800 bg-red-200 text-red-800 border border-red-800 mt-1 ml-2 w-12 h-4",
    'pit_out' => "rounded text-gray-800 bg-green-200 text-green-800 border border-green-800 px-10 py-5 mt-1 ml-2 w-12 h-4",

];

$sizes = [
    'xs' => "px-0 py-0 text-xxs",
    'sm' => "px-1 py-1 text-xs",
    'md' => "px-4 py-2 text-sm",
    'lg' => "px-6 py-3 text-base",
];
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge([
            'class' => "$base {$variants[$variant]} {$sizes[$size]}"
       ]) }}>
        {{ $slot }}
    </a>
@else
    <button
        {{ $attributes->merge([
            'class' => "$base {$variants[$variant]} {$sizes[$size]}"
       ]) }}>
        {{ $slot }}
    </button>
@endif
