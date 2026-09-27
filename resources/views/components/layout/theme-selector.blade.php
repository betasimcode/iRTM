

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
            Main themes
        </option>

        <option value="theme-dark">
            Theme Dark
        </option>

        <option value="theme-iracing">
            Theme iRacing
        </option>

        @foreach($teamThemes as $theme)
            <option value="{{ $theme['value'] }}">
                {{ $theme['label'] }}
            </option>
        @endforeach

        <option disabled class="text-[var(--text-title)] border-b-[var(--card)] uppercase mb-3" value="">
            Custom themes
        </option>

        <option value="theme-monaco">
            UI Monaco
        </option>

        <option value="theme-monza">
            UI Monza
        </option>

        <option value="theme-laguna">
            UI Laguna Seca
        </option>

        <option value="theme-hock">
            UI Hockemheim
        </option>

        <option value="theme-spa">
            UI Spa Francorchamps
        </option>

        <option value="theme-jerez">
            UI Jerez
        </option>

        <option value="theme-crtg">
            UI CRT Green
        </option>

    </select>

</div>
