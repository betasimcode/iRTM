{{-- <div class="mb-2 grid grid-cols-[minmax(0,1fr)_44px] gap-2">

    <select
        id="lap"
        name="lap"
        class="min-w-0 w-full rounded-lg border border-[var(--border)]
               bg-[var(--bg)] p-2 text-sm text-[var(--text)]"
        @disabled(!$stint || count($laps) === 0)
    >
        @forelse ($laps as $lap)

            @php
                $lapNumber = (int) (
                    $lap['candidate_lap'] ?? $lap['lap'] ?? 0
                );
            @endphp

            <option
                value="{{ $lapNumber }}"
                @selected(
                    (int) ($selectedLapNumber ?? 0) === $lapNumber
                )
            >
                Vuelta {{ $lapNumber }}
                —
                {{ isset($lap['lap_time'])
                    ? gmdate('i:s.', (int) $lap['lap_time'])
                        . str_pad(
                            (string) (int) (
                                ($lap['lap_time'] - floor($lap['lap_time']))
                                * 1000
                            ),
                            3,
                            '0',
                            STR_PAD_LEFT
                        )
                    : '--:--.---' }}
            </option>

        @empty
            <option value="">{{ __('ui.no_laps_available') }}</option>
        @endforelse
    </select>

    <button
        type="button"
        id="lap-color-trigger"
        aria-label="{{ __('ui.change_track_color') }}"
        title="{{ __('ui.track_color') }}"
        class="flex h-10 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--bg)]"
    >
        <span
            id="lap-color-preview"
            class="h-5 w-5 rounded-full border border-white/20"
            style="background-color: #00E5FF"
        ></span>
    </button>

</div> --}}

