@if($flagCode)

    <span
        class="fi fi-{{ $flagCode }} inline-block shrink-0"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        role="img"
    ></span>

@else

    <span
        class="inline-flex h-4 w-6 items-center justify-center
               rounded-sm
               bg-white/5
               text-[9px]
               font-medium
               text-[var(--text-muted)]"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        role="img"
    >
        ?
    </span>

@endif
