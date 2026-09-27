<div {{ $attributes->merge(['class' => 'flex items-center']) }}>

    @if($team?->banner_path)
        <img
            src="{{ asset('storage/' . ThemedBanner($team)) }}"
            alt="{{ $team->name }}"
            class="max-h-12 max-w-40 object-contain"
        >
    @else
        <span class="font-semibold">
            {{ $team->name ?? 'TEAM' }}
        </span>
    @endif

</div>
