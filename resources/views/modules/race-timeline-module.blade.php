<div x-data="{
    selectedEvent: null,
    now: Math.floor(Date.now() / 1000),

    init() {
        setInterval(() => {
            this.now = Math.floor(Date.now() / 1000);
        }, 1000);
    },

    countdownTo(timestamp) {

        let diff = timestamp - this.now;

        if (diff < 0) return '00:00:00';

        let h = String(Math.floor(diff / 3600)).padStart(2,'0');
        let m = String(Math.floor((diff % 3600) / 60)).padStart(2,'0');
        let s = String(diff % 60).padStart(2,'0');

        return `${h}:${m}:${s}`;
    }
}" class="bg-[var(--card)] rounded-lg border border-[var(--b-card)] shadow-lg p-4">



    <h3 class="text-sm font-semibold uppercase mb-4 text-[var(--card-title)]">Race Timeline</h3>

    <div class="flex items-center gap-3 mb-3 pb-2 border-b border-[var(--b-card)]">

        <div class="flex items-center gap-1">

            @foreach(['utc' => 'UTC', 'local' => 'LOCAL'] as $value => $label)

                <a
                    href="?range={{ $hoursVisible }}&time={{ $value }}"
                    class="
                        px-3 py-1 rounded text-sm
                        {{ $timeMode === $value
                            ? 'bg-[var(--value-info)] text-black'
                            : 'bg-[var(--bg)] border border-[var(--b-card-light)]'
                        }}
                    "
                >
                    {{ $label }}
                </a>

            @endforeach

        </div>

        <div class="flex items-center gap-1">

            @foreach([6,12,24,48] as $range)

                <a

                    href="?range={{ $range }}"

                    class="
                        px-3
                        py-1
                        rounded
                        text-sm

                        {{
                            $hoursVisible === $range

                                ? 'bg-[var(--value-info)] text-black'

                                : 'bg-[var(--bg)] border border-[var(--b-card-light)]'
                        }}
                    "

                >

                    {{ $range }}h

                </a>

            @endforeach

        </div>



    </div>

    <div id="timeline-scroll" class="overflow-x-auto">
        <div
            class="grid min-w-max"
            style="grid-template-columns:
                {{ $showSeriesHeader ? '280px ' : '' }}
                repeat({{ count($timeSlots) }}, {{ $slotWidth }}px);"
        >
            {{-- CABECERA VACIA --}}
            @if($showSeriesHeader)
            <div></div>
            @endif
            {{-- HORAS --}}
            @foreach($timeSlots as $slot)
            @php
                $displaySlot = $timeMode === 'local'
                    ? $slot->copy()->timezone($userTimezone)
                    : $slot;
            @endphp

            <div class="text-center p-2 bg-[var(--base)] rounded-t-lg border border-[var(--b-card-light)]
                {{ abs($loop->index - $currentSlot) <= 0 ? 'border-2 border-[var(--value-info)] ' : '' }}
            ">

                    @if($displaySlot->format('i') === '00')

                        <div class="text-xl font-bold text-[var(--value-info)] font-mono-timing">
                            {{ $displaySlot->format('H:i') }}
                        </div>

                    @else

                        <div class="text-sd text-[var(--text-soft)] pt-1 font-mono-timing">
                            {{ $displaySlot->format('H:i') }}
                        </div>

                    @endif

                </div>

            @endforeach

            {{-- SERIES --}}
            @foreach($timeline as $row)
                @if($showSeriesHeader)
                <div class="m-1 rounded-lg border border-[var(--b-card-light)] p-3 flex items-center gap-3 sticky left-0 bg-[var(--bg)] z-10">
                    <img src="{{ asset('storage/'.$row['logo']) }}" class="h-10" alt="">
                    <span>{{ $row['name'] }}</span>
                </div>
                @endif
                {{-- ******* CASILLAS ********** --}}
                @for($slot = 0; $slot < count($timeSlots); $slot++)
                    @php
                        $marker = collect($row['markers'])->firstWhere('slot', $slot);
                    @endphp

                    <div class="border border-[var(--b-card-light)] h-20 flex items-center justify-center">
                        @if($marker)
                            @php
                                $registrationOpen = now()->gte($marker['registration_time']) && now()->lt($marker['event_time']);
                                $raceLive = now()->between($marker['event_time'], \Carbon\Carbon::parse($marker['event_time'])->addMinutes(15));
                            @endphp

                            <div @click='selectedEvent = @json($marker)' class="rounded-lg p-1 @if($raceLive) ring-2 ring-red-500 bg-red-500/10 opacity-20 @elseif($registrationOpen) ring-2 ring-green-500 bg-green-500/10 @endif">

                                <img src="{{ asset('storage/'.$row['logo']) }}" class="h-12 @if($raceLive) opacity-50 @elseif($registrationOpen) opacity-100 @endif" alt="">
                            </div>
                        @endif
                    </div>
                @endfor
            @endforeach
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- EVENT DETAIL PANEL --}}
    {{-- ===================================================== --}}
    <div class="mt-4 rounded-lg border border-[var(--b-card)] bg-[var(--bg)] p-4 min-h-[140px]">
        <template x-if="selectedEvent">
            <div>
                <div class="flex gap-3 mb-4 border-b border-[var(--b-card)] pb-3">

                    <img :src="'/storage/' + selectedEvent.logo"
                         class="h-16 mt-1"
                         alt="">

                    <div class="grid grid-cols-2 ml-8 pt-3 gap-12 flex-1">

                        {{-- SERIES --}}
                        <div>

                            {{-- <div class="text-xs uppercase tracking-wider text-[var(--text-soft)] mb-1">
                                Series
                            </div> --}}

                            <div class="font-semibold text-[var(--card-title)]" x-text="selectedEvent.series_name">
                            </div>

                            <div class="text-sm text-[var(--text-soft)]" x-text="selectedEvent.series_short_name">
                            </div>

                        </div>

                        {{-- TRACK --}}
                        <div>

                            {{-- <div class="text-xs uppercase tracking-wider text-[var(--text-soft)] mb-1">
                                Track
                            </div> --}}

                            <div class="font-semibold uppercase text-[var(--card-title)]"
                                x-text="selectedEvent.track_name">
                            </div>

                            <div class="text-sm uppercase text-[var(--text-soft)]"
                                x-text="selectedEvent.variant">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="grid grid-cols-3 gap-6 mt-4">

                    {{-- REGISTRO --}}
                <div>

                    <div class="text-xs uppercase tracking-wider text-[var(--text-soft)] mb-2">
                        Registration
                    </div>

                    <div class="text-lg font-semibold"
                        :class="{
                            'text-[var(--open)]': selectedEvent.registration_status === 'OPEN',
                            'text-[var(--pending)]': selectedEvent.registration_status === 'PENDING',
                            'text-[var(--closed)]': selectedEvent.registration_status === 'CLOSED'
                        }"
                        x-text="selectedEvent.registration_status">
                    </div>

                    <div class="text-3xl font-mono-timing font-bold text-[var(--card-title)] mt-2"
                        x-show="selectedEvent.registration_status === 'PENDING'"
                        x-text="countdownTo(selectedEvent.registration_timestamp)">
                    </div>

                    <div class="text-3xl font-mono-timing font-bold text-[var(--card-title)] mt-2"
                        x-show="selectedEvent.registration_status === 'OPEN'"
                        x-text="countdownTo(selectedEvent.event_timestamp)">
                    </div>

                </div>

                    {{-- RACE --}}
                <div>

                    <div class="text-xs uppercase tracking-wider text-[var(--text-soft)] mb-1">
                        Race
                    </div>

                    <div class="text-4xl font-mono-timing font-bold text-[var(--card-title)]"
                         x-text="selectedEvent.event_time_local">
                    </div>

                    <div class="text-lg font-semibold"
                        :class="{
                            'text-[var(--waiting)]': selectedEvent.race_status === 'WAITING',
                            'text-[var(--live)]': selectedEvent.race_status === 'LIVE',
                            'text-[var(--ended)]': selectedEvent.race_status === 'FINISH'
                        }"
                        x-text="selectedEvent.race_status">
                    </div>

                    <div class="text-3xl font-mono-timing font-bold text-[var(--card-title)]"
                        x-show="selectedEvent.race_status === 'WAITING'"
                        x-text="countdownTo(selectedEvent.event_timestamp)">
                    </div>

                </div>

                    {{-- NEXT --}}

                        <div>

                            <div class="text-xs uppercase tracking-wider text-[var(--text-soft)] mb-2">
                                Upcoming
                            </div>

                            <template x-for="race in selectedEvent.next_events">

                                <div class="flex justify-between items-center gap-4 mb-2">

                                    <div class="font-mono-timing text-[var(--card-title)] w-16"
                                         x-text="race.time">
                                    </div>

                                    <div class="text-xs text-[var(--text-soft)] uppercase pr-56"
                                         x-text="race.track">
                                    </div>

                                </div>

                            </template>

                        </div>


                </div>
            </div>
        </template>

        <template x-if="!selectedEvent">
            <div class="text-[var(--text-soft)]">Select a race event</div>
        </template>
    </div>
</div>
