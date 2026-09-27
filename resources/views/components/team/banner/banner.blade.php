@props([
    'team',
    'size' => 'medium',
])

@php
    $height = match ($size) {
        'small' => 'h-24',
        'large' => 'h-64',
        default => 'h-40',
    };
@endphp

<section
    {{ $attributes->merge([
        'class' => "{$height} relative overflow-hidden border-b border-[var(--border)] bg-[var(--bg)]"
    ]) }}
>
    @if($team?->banner_path)
        <img
            src="{{ asset('storage/' . themedBanner($team)) }}"
            alt="{{ $team->name }}"
            class="absolute inset-0 w-full h-full object-cover"
        >

        <div class="absolute inset-0 bg-black/30"></div>
    @endif

    {{-- <div class="relative z-10 h-full flex items-center px-8">
        <div>
            <h1 class="text-2xl font-semibold">
                {{ $team->name ?? 'TEAM' }}
            </h1>

            @if($slot)
                <div class="mt-1 text-sm opacity-80">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div> --}}
</section>
