@props([
    'color' => 'gray'
])

@php
$colors = [
    'gray' => 'bg-gray-200 hover:bg-gray-600',
    'blue' => 'bg-gray-200 hover:bg-blue-600',
    'red' => 'bg-gray-200 hover:bg-red-600',
];

@endphp

<button 
    {{ $attributes->merge([
        'class' => 'p-2 rounded-lg transition group ' . ($colors[$color] ?? $colors['gray'])
    ]) }}
>
    {{ $slot }}
</button>

