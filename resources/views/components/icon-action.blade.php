@props(['icon', 'label', 'href' => null, 'variant' => 'default', 'type' => 'button'])

@php($classes = 'icon-action' . ($variant === 'danger' ? ' icon-action--danger' : ''))

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'aria-label' => $label, 'title' => $label]) }}>
        <x-ui-icon :name="$icon" />
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'aria-label' => $label, 'title' => $label]) }}>
        <x-ui-icon :name="$icon" />
    </button>
@endif
