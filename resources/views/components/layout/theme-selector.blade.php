

@php
    $teamThemes = $teamThemes ?? [];
@endphp

<div class="block border-t text-sm text-[var(--text)] px-4 py-3">

    <select
        id="themeSelector"

        :value="$store.theme.current"

        @change="

            $store.theme.current = $event.target.value;

            document.body.className = $store.theme.current;

            fetch('/user/theme', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify({
                    theme: $store.theme.current
                })

            }).then(() => {

                window.location.reload();

            });

        "

        class="align-middle rounded-md left-1 w-30 h-9
               border-[var(--border)]
               bg-[var(--base)]
               text-[var(--text)]
               text-xs"
    >

        <option disabled class="text-[var(--text-title)] uppercase" value="">
            {{ __('ui.main_themes') }}
        </option>

        <option value="theme-dark">
            {{ __('ui.dark_theme') }}
        </option>

        <option value="theme-iracing">
            {{ __('ui.iracing_theme') }}
        </option>

        @foreach($teamThemes as $theme)
            <option value="{{ $theme['value'] }}">
                {{ $theme['label'] }}
            </option>
        @endforeach

        <option disabled class="text-[var(--text-title)] border-b-[var(--card)] uppercase mb-3" value="">
            {{ __('ui.custom_themes') }}
        </option>

        <option value="theme-monaco">
            {{ __('ui.theme') }} Monaco
        </option>

        <option value="theme-monza">
            {{ __('ui.theme') }} Monza
        </option>

        <option value="theme-laguna">
            {{ __('ui.theme') }} Laguna Seca
        </option>

        <option value="theme-hock">
            {{ __('ui.theme') }} Hockenheim
        </option>

        <option value="theme-spa">
            {{ __('ui.theme') }} Spa-Francorchamps
        </option>

        <option value="theme-jerez">
            {{ __('ui.theme') }} Jerez
        </option>

        <option value="theme-crtg">
            {{ __('ui.theme') }} CRT Green
        </option>

    </select>

</div>
