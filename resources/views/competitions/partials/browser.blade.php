@php
    $registerRoute = $registerRoute ?? 'competitions.register';
@endphp
<div class="space-y-6">

    {{-- Cabecera del catálogo --}}
    <div>
        <p class="text-sm text-[var(--text-muted)] mt-1">
            Available competitions.
        </p>
    </div>

    {{-- Grid de competiciones --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

        @forelse($competitions as $competition)

            <x-competition-card :competition="$competition" :href="route($registerRoute, $competition)"/>

        @empty

            <div class="md:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-[var(--border)] p-8 text-center">

                <div class="text-4xl mb-4">
                    🏁
                </div>

                <h4 class="font-semibold text-[var(--text)]">
                    {{ __('ui.no_competitions') }}
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-2">
                    {{ __('ui.no_competitions_available') }}
                </p>

            </div>

        @endforelse

    </div>

</div>
