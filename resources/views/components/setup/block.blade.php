<div class="setup-block {{ $class ?? '' }}">

    <div class="setup-title">
        {{ $title }}
    </div>

    <div class="setup-content">

        @forelse($items as $item)

            @php
                $label = last(explode('.', $item->key));
            @endphp

            <div class="setup-item">

                <span class="label">
                    {{ ucwords(str_replace('_',' ', $label)) }}
                </span>

                <span class="value">
                    {{ $item->value }}
                </span>

            </div>

        @empty
            <div class="text-muted small">—</div>
        @endforelse

    </div>

</div>
