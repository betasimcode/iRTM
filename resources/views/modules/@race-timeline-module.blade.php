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

    class="grid min-w-max"

    style="

        grid-template-columns:

        280px

        repeat(

            {{ count($timeSlots) }},

            {{ $slotWidth }}px
        );

    "

>

    {{-- CABECERA VACIA --}}

    <div></div>

    {{-- HORAS --}}

    @foreach($timeSlots as $slot)

        <div

            class="
                text-center
                p-2
                border
                border-[var(--b-card-light)]
            "

        >

            {{ $slot->format('H:i') }}

        </div>

    @endforeach

    {{-- SERIES --}}

    @foreach($timeline as $row)

        <div

            class="
                m-1
                rounded-lg
                border
                border-[var(--b-card-light)]
                p-3
                flex
                items-center
                gap-3
                sticky
                left-0
                bg-[var(--bg)]
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

{{--      *******  CASILLAS **********     --}}
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
                    border
                    border-[var(--b-card-light)]
                    h-20
                    flex
                    items-center
                    justify-center
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
                        @click='selectedEvent = @json($marker)'

                        class="

                            rounded-lg
                            p-1

                            @if($raceLive)

                                ring-2 ring-red-500
                                bg-red-500/10
                                opacity-20 

                            @elseif($registrationOpen)

                                ring-2 ring-green-500
                                bg-green-500/10

                            @endif

                        "

                    >

                    <img

                    src="{{ asset('storage/'.$row['logo']) }}"
                
                    class="
                        h-12
                
                        @if($raceLive)
                
                            opacity-50
                
                        @elseif($registrationOpen)
                
                            opacity-100
                
                        @endif
                    "
                
                    alt=""
                
                >

                    </div>

                @endif

            </div>

            @endfor

    @endforeach

</div>

    </div>
       
    

{{-- PANEL INFERIOR --}}
    
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
        grid-cols-3
        gap-6
        mt-4
    "

>

<div>

    <div

        class="
            text-xs
            uppercase
            tracking-wider
            text-[var(--text-soft)]
            mb-2
        "

    >

        Registration

    </div>

    <template

        x-if="

            new Date(selectedEvent.registration_time)

            <= new Date()

            &&

            new Date()

            < new Date(selectedEvent.event_time)

        "

    >

        <div>

            <div

                class="
                    text-lg
                    font-semibold
                    text-green-500
                "

            >

                OPEN

            </div>

            <div

                class="
                    text-sm
                    text-[var(--text-soft)]
                "

            >

                Registration available

            </div>

        </div>

    </template>

    <template

        x-if="

            new Date()

            < new Date(selectedEvent.registration_time)

        "

    >

        <div>

            <div

                class="
                    text-lg
                    font-semibold
                    text-yellow-500
                "

            >

                PENDING

            </div>

            <div

                class="
                    text-sm
                    text-[var(--text-soft)]
                "

            >

                Opens soon

            </div>

        </div>

    </template>

    <template

        x-if="

            new Date()

            >= new Date(selectedEvent.event_time)

        "

    >

        <div>

            <div

                class="
                    text-lg
                    font-semibold
                    text-red-500
                "

            >

                CLOSED

            </div>

            <div

                class="
                    text-sm
                    text-[var(--text-soft)]
                "

            >

                Registration ended

            </div>

        </div>

    </template>

</div>

<div>

    <div

        class="
            text-xs
            uppercase
            tracking-wider
            text-[var(--text-soft)]
            mb-2
        "

    >

        Race

    </div>

    <div

        class="
            text-4xl
            font-mono-timing
            font-bold
            text-[var(--card-title)]
        "

        x-text="

            selectedEvent.event_time
                .substring(11,16)

        "

    ></div>

</div>


<div>

    <div

        class="
            text-xs
            uppercase
            tracking-wider
            text-[var(--text-soft)]
            mb-2
        "

    >

        Upcoming

    </div>

    <div

        class="
            text-sm
            text-[var(--card-title)]
        "

    >

        Next races

    </div>

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

