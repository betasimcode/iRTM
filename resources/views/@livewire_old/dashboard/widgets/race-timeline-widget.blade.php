<div

    x-data="{

        selectedEvent: null

    }"

    class="
        bg-[var(--card)]
        rounded-lg
        border border-[var(--b-card)]
        shadow-lg
        p-4
    "
>



<div>

    {{ now()->format('H:i:s') }}

</div>
<div class="mb-4">

</div>
    <h3
        class="
            text-sm
            font-semibold
            uppercase
            mb-4
            text-[var(--card-title)]
        "
    >
        Race Timeline
    </h3>

    <div
        class="
            overflow-x-auto
        "
    >

    
    <div

    class="grid"

    style="

        grid-template-columns:

        280px

        repeat(

            {{ count($timeSlots) }},

            {{ $slotWidth }}px
        );

    "

>

    <div
        class="
            {{-- border-r border-[var(--border)] --}}
            p-2
            font-semibold
            text-[var(--text-soft)]
        "
    >
    </div>

    @foreach($timeSlots as $slot)

        <div

            



        class="
            rounded-t-lg
            p-2
            m-1
            text-center
            bg-[var(--base)]

            {{
                $loop->index === $currentSlot

                ? 'ring-2 ring-[var(--value-info)]'

                : 'border-t border-x border-[var(--header)]'
            }}
        "

        >


        @if($slot->format('i') === '00')

        <div
            class="
                font-mono-timing
                font-bold
                text-lg
                text-[var(--value-info)]
            "
        >
            {{ $slot->format('H') }}
        </div>
    
    @else
    
    <div
        class="
            pt-2
            font-mono-timing
            text-xs
            text-[var(--value-data)]
        "
    >
        {{ $slot->format('H:i') }}
    </div>
    
    @endif

        </div>

    @endforeach

    
</div>

<div class="relative">
@foreach($timeline as $row)

<div

    class=" min-w-max grid "

    style="

        grid-template-columns:

        280px

        repeat(

            {{ count($timeSlots) }},

            {{ $slotWidth }}px
        );

    "

>


<div

    class="
        rounded-lg
        p-3
        m-1
        flex
        items-center
        gap-3
        sticky
        left-0
        bg-[var(--bg)]
        text-[var(--card-title)]
        z-10
    "

>

    <img

        src="{{ asset('storage/'.$row['logo']) }}"

        class="h-10"

        alt=""
    >

    <span>

        {{ $row['name'] }}

    </span>

</div>


@for(

    $slot = 0;

    $slot < count($timeSlots);

    $slot++

)

    @php

        $marker = collect(

            $row['markers']

        )->firstWhere(

            'slot',

            $slot
        );

    @endphp

    <div

        class="
            border border-t border-[var(--b-card)]
            h-20
            w-20
            flex
            items-center
            justify-center
            m-1
        "

    >
    
        @if($marker)

        @php

            $registrationOpen =

                now()->gte(
                    $marker['registration_time']
                )

                &&

                now()->lt(
                    $marker['event_time']
                );

            $raceLive =

                now()->between(

                    $marker['event_time'],

                    \Carbon\Carbon::parse(

                        $marker['event_time']

                    )->addMinutes(15)
                );

        @endphp


<div

    @click='selectedEvent = @js($marker)'

    class="
        cursor-pointer
        rounded-lg
        p-1

        @if($raceLive)

            ring-2 ring-red-500

        @elseif($registrationOpen)

            ring-2 ring-green-500

        @endif
    "

>

    <img

        src="{{ asset('storage/'.$row['logo']) }}"

        class="h-14"

        alt=""

    >

</div>

        @endif

    </div>

@endfor


</div>

@endforeach

</div>


</div>

<div

    class="
        mt-4
        rounded-lg
        border border-[var(--b-card)]
        bg-[var(--bg)]
        p-4
        min-h-[140px]
    "

>

<div

class="
    mt-4
    rounded-lg
    border border-[var(--b-card)]
    bg-[var(--bg)]
    p-4
    min-h-[140px]
"

>

<template x-if="selectedEvent">

    <div>

        <div
            class="
                flex
                items-center
                gap-3
                mb-4
            "
        >

            <img

                :src="'/storage/' + selectedEvent.logo"

                class="h-10"

                alt=""

            >

            <div>

                <div

                    class="
                        font-semibold
                        text-[var(--card-title)]
                    "

                    x-text="selectedEvent.series_name"

                ></div>

                <div

                    class="
                        text-sm
                        text-[var(--text-soft)]
                    "

                    x-text="selectedEvent.series_short_name"

                ></div>

            </div>

        </div>

        <div
            class="
                grid
                grid-cols-2
                gap-4
            "
        >

            <div>

                <div
                    class="
                        text-[var(--text-soft)]
                    "
                >
                    Registration
                </div>

                <div
                    class="
                        font-mono-timing
                    "
                    x-text="selectedEvent.registration_time"
                ></div>

            </div>

            <div>

                <div
                    class="
                        text-[var(--text-soft)]
                    "
                >
                    Race
                </div>

                <div
                    class="
                        font-mono-timing
                    "
                    x-text="selectedEvent.event_time"
                ></div>

            </div>

        </div>

    </div>

</template>

<template x-if="!selectedEvent">

    <div
        class="
            text-[var(--text-soft)]
        "
    >
        Select a race event
    </div>

</template>

</div>

</div>
